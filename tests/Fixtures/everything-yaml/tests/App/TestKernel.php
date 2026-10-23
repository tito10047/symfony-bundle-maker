<?php

namespace Tito10047\EverythingYamlBundle\Tests\App;

use Composer\InstalledVersions;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Psr\Log\NullLogger;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Symfony\UX\StimulusBundle\StimulusBundle;
use Tito10047\EverythingYamlBundle\GreeterInterface;
use Tito10047\EverythingYamlBundle\Tito10047EverythingYamlBundle;

/**
 * Minimal application registering Tito10047EverythingYamlBundle, booted by the integration and functional tests.
 *
 * The bundle directory is read-only: the cache and the logs are written to the system temporary
 * directory, in a directory specific to the configuration of the kernel. tests/bootstrap.php
 * removes them before each run, so that every run compiles fresh containers.
 */
final class TestKernel extends Kernel
{
    use MicroKernelTrait;

    /**
     * @param array<string, array<string, mixed>> $extensionConfigs configuration of the extensions, indexed by alias,
     *                                                              applied last, e.g. ['tito10047_everything_yaml' => ['greeting' => 'Hi']]
     */
    public function __construct(
        string $environment = 'test',
        bool $debug = true,
        private readonly array $extensionConfigs = [],
    ) {
        parent::__construct($environment, $debug);
    }

    /**
     * The bundles are typed as Bundle: the BundleInterface of HttpKernel is deprecated since Symfony 8.1,
     * and the interface replacing it does not exist in Symfony 7.4.
     *
     * @return iterable<Bundle>
     */
    public function registerBundles(): iterable
    {
        yield new FrameworkBundle();
        yield new TwigBundle();
        yield new DoctrineBundle();
        yield new StimulusBundle();
        yield new Tito10047EverythingYamlBundle();
    }

    /**
     * The application has no files of its own: using this directory as the project directory
     * keeps the files of the bundle (config, templates, translations) from being loaded as
     * application files.
     */
    public function getProjectDir(): string
    {
        return __DIR__;
    }

    public function getCacheDir(): string
    {
        return $this->getVarDir().'/cache/'.$this->environment;
    }

    public function getLogDir(): string
    {
        return $this->getVarDir().'/log';
    }

    protected function configureContainer(ContainerConfigurator $container): void
    {
        $container->extension('framework', [
            'test' => true,
        ]);

        // unused private services are removed from the container: this public alias keeps the
        // greeter available to the tests, the way an application autowiring it does
        $container->services()
            ->alias('test.'.GreeterInterface::class, GreeterInterface::class)
            ->public()
        ;

        // the default logger prints the errors, e.g. the "404 Not Found" responses of the functional tests,
        // which PHPUnit reports as risky: the test application logs nothing
        $container->services()
            ->set('logger', NullLogger::class)
        ;

        // undefined variables of the templates are errors
        $container->extension('twig', [
            'strict_variables' => true,
        ]);

        $container->extension('framework', [
            'default_locale' => 'en',
            'translator' => [
                'fallbacks' => ['en'],
            ],
        ]);

        $container->extension('doctrine', [
            'dbal' => [
                'url' => 'sqlite:///:memory:',
            ],
            'orm' => [],
        ]);

        // DoctrineBundle 2, the only version supporting PHP 8.2 and 8.3, deprecates the defaults that DoctrineBundle 3 no longer has
        if (version_compare(InstalledVersions::getVersion('doctrine/doctrine-bundle') ?? '', '3.0.0', '<')) {
            $container->extension('doctrine', [
                'orm' => [
                    'enable_lazy_ghost_objects' => true,
                    'enable_native_lazy_objects' => \PHP_VERSION_ID >= 80400,
                    'controller_resolver' => [
                        'auto_mapping' => false,
                    ],
                ],
            ]);
        }

        foreach ($this->extensionConfigs as $alias => $config) {
            $container->extension($alias, $config);
        }
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        // imported like an application does, see README.md
        $routes->import('@Tito10047EverythingYamlBundle/config/routes.yaml');
    }

    /**
     * The directory of the caches and the logs of the test kernels of this copy of the bundle.
     */
    public static function getTemporaryDir(): string
    {
        return sys_get_temp_dir().'/tito10047-everything-yaml-bundle/'.hash('xxh128', __DIR__);
    }

    private function getVarDir(): string
    {
        return self::getTemporaryDir().'/'.hash('xxh128', serialize($this->extensionConfigs));
    }
}
