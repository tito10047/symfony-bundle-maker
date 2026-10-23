<?php

namespace MyVendor\EverythingYamlBundle\Tests\App;

use MyVendor\EverythingYamlBundle\GreeterInterface;
use MyVendor\EverythingYamlBundle\MyVendorEverythingYamlBundle;
use Psr\Log\NullLogger;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

/**
 * Minimal application registering MyVendorEverythingYamlBundle, booted by the integration and functional tests.
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
     *                                                              applied last, e.g. ['my_vendor_everything_yaml' => ['greeting' => 'Hi']]
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
        yield new MyVendorEverythingYamlBundle();
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

        foreach ($this->extensionConfigs as $alias => $config) {
            $container->extension($alias, $config);
        }
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        // imported like an application does, see README.md
        $routes->import('@MyVendorEverythingYamlBundle/config/routes.yaml');
    }

    /**
     * The directory of the caches and the logs of the test kernels of this copy of the bundle.
     */
    public static function getTemporaryDir(): string
    {
        return sys_get_temp_dir().'/my-vendor-everything-yaml-bundle/'.hash('xxh128', __DIR__);
    }

    private function getVarDir(): string
    {
        return self::getTemporaryDir().'/'.hash('xxh128', serialize($this->extensionConfigs));
    }
}
