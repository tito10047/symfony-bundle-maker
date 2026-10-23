<?php

declare(strict_types=1);

/*
 * This file is part of the tito10047/symfony-bundle-maker package.
 *
 * (c) Jozef Môstka <jozef@mostka.sk>
 * (c) Yonel Ceruto <yonelceruto@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tito10047\BundleMaker\Tests\Snapshot;

use Composer\IO\NullIO;
use Composer\Util\ConfigValidator;
use Composer\Util\ProcessExecutor;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Symfony\Component\Routing\Loader\PhpFileLoader;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Yaml\Yaml;
use Tito10047\BundleMaker\Blueprint\ArgumentType;
use Tito10047\BundleMaker\Blueprint\BundleBlueprint;
use Tito10047\BundleMaker\Blueprint\ServiceArgument;
use Tito10047\BundleMaker\Options\ConfigFormat;
use Tito10047\BundleMaker\Tests\Support\Filesystem;

/**
 * Checks that the snapshots (tests/Fixtures/<scenario>/) are a working bundle:
 * valid PHP, YAML and composer.json, and a bundle class whose extension loads
 * exactly the services and routes described by the blueprint, in every configuration format.
 */
final class GeneratedBundleTest extends TestCase
{
    /**
     * @return iterable<string, array{Scenario}>
     */
    public static function provideScenarios(): iterable
    {
        yield from SnapshotTest::provideScenarios();
    }

    #[DataProvider('provideScenarios')]
    public function testPhpFilesHaveAValidSyntax(Scenario $scenario): void
    {
        $process = new ProcessExecutor();
        foreach (array_keys(Filesystem::readTree($scenario->getFixtureDir())) as $path) {
            if (!str_ends_with($path, '.php')) {
                continue;
            }

            $status = $process->execute([\PHP_BINARY, '-l', $scenario->getFixtureDir().'/'.$path], $output);
            self::assertSame(0, $status, $path.': '.$process->getErrorOutput());
        }
    }

    #[DataProvider('provideScenarios')]
    public function testYamlFilesAreValid(Scenario $scenario): void
    {
        foreach (array_keys(Filesystem::readTree($scenario->getFixtureDir())) as $path) {
            if (str_ends_with($path, '.yaml') || str_ends_with($path, '.yml')) {
                self::assertIsArray(Yaml::parseFile($scenario->getFixtureDir().'/'.$path) ?? [], $path);
            }
        }

        $this->addToAssertionCount(1);
    }

    #[DataProvider('provideScenarios')]
    public function testComposerJsonIsValid(Scenario $scenario): void
    {
        [$errors, $publishErrors, $warnings] = (new ConfigValidator(new NullIO()))->validate($scenario->getFixtureDir().'/composer.json');

        self::assertSame([], $errors);
        self::assertSame([], $publishErrors);
        self::assertSame([], $warnings);
    }

    #[DataProvider('provideScenarios')]
    public function testBundleExtensionLoadsTheBlueprintServices(Scenario $scenario): void
    {
        $blueprint = $this->createBlueprint($scenario);
        $bundle = $this->createBundle($scenario, $blueprint);
        $extension = $bundle->getContainerExtension();
        self::assertNotNull($extension);
        self::assertSame($blueprint->naming->alias, $extension->getAlias());

        $container = new ContainerBuilder();
        $container->setParameter('kernel.environment', 'test');
        $container->setParameter('kernel.debug', false);
        $container->setParameter('kernel.build_dir', sys_get_temp_dir());
        $container->registerExtension($extension);
        $bundle->build($container);
        $extension->load([], $container);

        // literal values are escaped ("%%") in every format
        $unescape = $container->getParameterBag()->unescapeValue(...);

        foreach ($blueprint->services->getParameters() as $name => $value) {
            self::assertSame($value, $unescape($container->getParameter($name)), $name);
        }

        foreach ($blueprint->services->getServices() as $id => $service) {
            self::assertTrue($container->hasDefinition($id), $id);
            $definition = $container->getDefinition($id);

            self::assertSame($service->class, $definition->getClass(), $id);
            self::assertFalse($definition->isPublic(), $id);
            self::assertFalse($definition->isAutowired(), $id);
            self::assertFalse($definition->isAutoconfigured(), $id);

            $expectedTags = [];
            foreach ($service->tags as $tag) {
                $expectedTags[$tag->name][] = $tag->attributes;
            }
            self::assertSame($expectedTags, $definition->getTags(), $id);

            // loadExtension() may set the next arguments from the configuration, e.g. the greeting of the greeter
            self::assertSame(
                array_map(static fn (ServiceArgument $argument): mixed => self::normalizeExpectedArgument($argument), $service->arguments),
                array_map(static fn (mixed $argument): mixed => $argument instanceof Reference ? ['service' => (string) $argument] : $unescape($argument), \array_slice($definition->getArguments(), 0, \count($service->arguments))),
                $id,
            );
        }

        foreach ($blueprint->services->getAliases() as $id => $alias) {
            self::assertTrue($container->hasAlias($id), $id);
            self::assertSame($alias->target, (string) $container->getAlias($id), $id);
            self::assertFalse($container->getAlias($id)->isPublic(), $id);
        }

        $serviceIds = array_keys($blueprint->services->getServices());
        self::assertSame($serviceIds, array_values(array_intersect(array_keys($container->getDefinitions()), $serviceIds)));

        if ($scenario->hasKitchenSink()) {
            // contributed to the loadExtension() and build() slots
            self::assertTrue($container->getParameter($blueprint->naming->alias.'.loaded'));
            self::assertSame('beforeOptimization', $container->getParameter('kitchen_sink.priority'));

            // contributed to the prependExtension() slot
            $prepended = new ContainerBuilder();
            $prepended->setParameter('kernel.environment', 'test');
            $prepended->setParameter('kernel.build_dir', sys_get_temp_dir());
            $prepended->registerExtension($extension);
            self::assertInstanceOf(\Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface::class, $extension);
            $extension->prepend($prepended);
            self::assertSame([['enabled' => true]], $prepended->getExtensionConfig('framework'));
        }
    }

    #[DataProvider('provideScenarios')]
    public function testRoutesMatchTheBlueprint(Scenario $scenario): void
    {
        $blueprint = $this->createBlueprint($scenario);
        $file = $scenario->getFixtureDir().'/config/'.$scenario->configFormat->getFileName('routes');

        if ($blueprint->routes->isEmpty()) {
            self::assertFileDoesNotExist($file);

            return;
        }

        $locator = new FileLocator(\dirname($file));
        $loader = ConfigFormat::Yaml === $scenario->configFormat ? new YamlFileLoader($locator) : new PhpFileLoader($locator);
        $collection = $loader->load(basename($file));

        self::assertSame(array_keys($blueprint->routes->getRoutes()), array_keys($collection->all()));
        foreach ($blueprint->routes->getRoutes() as $name => $route) {
            $loaded = $collection->get($name);
            self::assertNotNull($loaded);
            self::assertSame($route->path, $loaded->getPath(), $name);
            self::assertSame($route->controller, $loaded->getDefault('_controller'), $name);
            self::assertSame($route->methods, $loaded->getMethods(), $name);
            $defaults = $loaded->getDefaults();
            ksort($defaults);
            $expectedDefaults = ['_controller' => $route->controller] + $route->defaults;
            ksort($expectedDefaults);
            self::assertSame($expectedDefaults, $defaults, $name);
            self::assertSame($route->requirements, $loaded->getRequirements(), $name);
        }
    }

    private function createBlueprint(Scenario $scenario): BundleBlueprint
    {
        $features = Scenarios::createFeatureRegistry();

        return Scenarios::createGenerator($features)->createBlueprint($scenario->createOptions($features));
    }

    private function createBundle(Scenario $scenario, BundleBlueprint $blueprint): AbstractBundle
    {
        require_once $scenario->getFixtureDir().'/src/'.$blueprint->naming->className.'.php';

        $class = $blueprint->naming->fqcn;
        $bundle = new $class();
        self::assertInstanceOf(AbstractBundle::class, $bundle);
        self::assertSame($scenario->getFixtureDir(), $bundle->getPath());

        return $bundle;
    }

    private static function normalizeExpectedArgument(ServiceArgument $argument): mixed
    {
        return match ($argument->type) {
            ArgumentType::Service => ['service' => $argument->getReference()],
            ArgumentType::Parameter => '%'.$argument->getReference().'%',
        };
    }
}
