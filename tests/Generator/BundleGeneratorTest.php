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

namespace Tito10047\BundleMaker\Tests\Generator;

use PHPUnit\Framework\TestCase;
use Tito10047\BundleMaker\Blueprint\BundleBlueprint;
use Tito10047\BundleMaker\Blueprint\Constraint;
use Tito10047\BundleMaker\Blueprint\RouteDefinition;
use Tito10047\BundleMaker\Blueprint\ServiceAlias;
use Tito10047\BundleMaker\Contributor\ContributorInterface;
use Tito10047\BundleMaker\Contributor\CoreContributors;
use Tito10047\BundleMaker\Feature\FeatureRegistry;
use Tito10047\BundleMaker\Generator\BundleGenerator;
use Tito10047\BundleMaker\Options\ConfigFormat;
use Tito10047\BundleMaker\Template\TemplateRenderer;
use Tito10047\BundleMaker\Tests\Support\Filesystem;
use Tito10047\BundleMaker\Tests\Support\OptionsFactory;
use Tito10047\BundleMaker\Tests\Support\TestFeature;

final class BundleGeneratorTest extends TestCase
{
    public function testRunsCoreContributorsThenEnabledFeaturesInOrder(): void
    {
        $calls = [];
        $core = new class(static function () use (&$calls): void {
            $calls[] = 'core';
        }) implements ContributorInterface {
            public function __construct(private readonly \Closure $callback)
            {
            }

            public function contribute(BundleBlueprint $blueprint): void
            {
                ($this->callback)();
            }
        };
        $features = new FeatureRegistry(
            new TestFeature('a', contribution: static function () use (&$calls): void { $calls[] = 'a'; }),
            new TestFeature('b', contribution: static function () use (&$calls): void { $calls[] = 'b'; }),
            new TestFeature('c', contribution: static function () use (&$calls): void { $calls[] = 'c'; }),
        );

        (new BundleGenerator(new CoreContributors($core), $features))->createBlueprint(OptionsFactory::create(features: ['a' => true, 'b' => false, 'c' => true]));

        self::assertSame(['core', 'a', 'c'], $calls);
    }

    public function testValidatesServices(): void
    {
        $features = new FeatureRegistry(new TestFeature('broken', contribution: static function (BundleBlueprint $blueprint): void {
            $blueprint->services->addAlias(new ServiceAlias('Acme\\FooInterface', 'acme_blog.missing'));
        }));

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('targets the undefined service "acme_blog.missing"');

        (new BundleGenerator(new CoreContributors(), $features))->createBlueprint(OptionsFactory::create(features: ['broken' => true]));
    }

    public function testRoutesRequireTheRoutingComponentAndGenerateTheRoutingFile(): void
    {
        $features = new FeatureRegistry(new TestFeature('routes', contribution: static function (BundleBlueprint $blueprint): void {
            $blueprint->routes->add(new RouteDefinition('acme_blog_home', '/', 'acme_blog.controller'));
        }));
        $generator = new BundleGenerator(new CoreContributors(), $features);

        $withRoutes = $generator->createBlueprint(OptionsFactory::create(configFormat: ConfigFormat::Yaml, features: ['routes' => true]));
        $withoutRoutes = $generator->createBlueprint(OptionsFactory::create(configFormat: ConfigFormat::Yaml, features: ['routes' => false]));

        self::assertSame(['symfony/routing' => Constraint::SYMFONY], $withRoutes->composer->getRequire());
        self::assertSame(['config/routes.yaml', 'config/services.yaml'], array_keys($generator->render($withRoutes)));
        self::assertSame([], $withoutRoutes->composer->getRequire());
        self::assertSame(['config/services.yaml'], array_keys($generator->render($withoutRoutes)));
    }

    public function testFileParametersCannotOverrideStandardVariables(): void
    {
        $features = new FeatureRegistry(new TestFeature('override', contribution: static function (BundleBlueprint $blueprint): void {
            $blueprint->addFile('core/CHANGELOG.md.tpl', 'a/CHANGELOG.md', ['naming' => 'x']);
        }));
        $generator = new BundleGenerator(new CoreContributors(), $features);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('The parameters of "a/CHANGELOG.md" cannot override the standard template variables "naming".');

        $generator->render($generator->createBlueprint(OptionsFactory::create(features: ['override' => true])));
    }

    public function testGeneratesFilesAndComposerJson(): void
    {
        $dir = Filesystem::createTempDir('generator');
        file_put_contents($dir.'/composer.json', '{"name": "tito10047/symfony-bundle-skeleton", "minimum-stability": "stable"}');

        try {
            $features = ['config' => true, 'controllers' => false, 'twig' => false, 'translations' => false, 'command' => false, 'event-listener' => false];
            $blueprint = (new BundleGenerator(CoreContributors::createDefault(), FeatureRegistry::createDefault()))->generate(OptionsFactory::create(features: $features), $dir);

            self::assertSame('acme/blog-bundle', $blueprint->naming->packageName);
            self::assertSame([
                '.editorconfig',
                '.gitattributes',
                '.gitignore',
                'CHANGELOG.md',
                'LICENSE',
                'README.md',
                'composer.json',
                'config/definition.php',
                'config/services.php',
                'docs/index.md',
                'phpunit.dist.xml',
                'src/AcmeBlogBundle.php',
                'src/Exception/ExceptionInterface.php',
                'src/Exception/InvalidArgumentException.php',
                'src/Greeter.php',
                'src/GreeterInterface.php',
                'tests/App/TestKernel.php',
                'tests/Integration/AcmeBlogBundleTest.php',
                'tests/Integration/ConfigurationTest.php',
                'tests/Unit/GreeterTest.php',
                'tests/bootstrap.php',
            ], array_keys(Filesystem::readTree($dir)));

            /** @var array{name: string, minimum-stability: string} $composer */
            $composer = json_decode((string) file_get_contents($dir.'/composer.json'), true, flags: \JSON_THROW_ON_ERROR);
            self::assertSame('acme/blog-bundle', $composer['name']);
            self::assertSame('stable', $composer['minimum-stability']);
        } finally {
            Filesystem::remove($dir);
        }
    }

    public function testFailsWhenAFileCannotBeWritten(): void
    {
        $dir = Filesystem::createTempDir('generator');
        mkdir($dir.'/src/AcmeBlogBundle.php', 0o777, true);

        try {
            $this->expectException(\RuntimeException::class);
            $this->expectExceptionMessage('Unable to write the file "'.$dir.'/src/AcmeBlogBundle.php".');

            (new BundleGenerator(CoreContributors::createDefault(), new FeatureRegistry(), new TemplateRenderer()))->generate(OptionsFactory::create(), $dir);
        } finally {
            Filesystem::remove($dir);
        }
    }
}
