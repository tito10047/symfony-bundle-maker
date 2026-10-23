<?php

namespace Acme\MinimalPhpBundle\Tests\App;

use Acme\MinimalPhpBundle\AcmeMinimalPhpBundle;
use Acme\MinimalPhpBundle\GreeterInterface;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\HttpKernel\Kernel;

/**
 * Minimal application registering AcmeMinimalPhpBundle, booted by the integration and functional tests.
 *
 * The bundle directory is read-only: the cache and the logs are written to the system temporary
 * directory, in a directory specific to the configuration of the kernel. tests/bootstrap.php
 * removes them before each run, so that every run compiles fresh containers.
 */
final class TestKernel extends Kernel
{
    use MicroKernelTrait;

    /**
     * The bundles are typed as Bundle: the BundleInterface of HttpKernel is deprecated since Symfony 8.1,
     * and the interface replacing it does not exist in Symfony 7.4.
     *
     * @return iterable<Bundle>
     */
    public function registerBundles(): iterable
    {
        yield new FrameworkBundle();
        yield new AcmeMinimalPhpBundle();
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
    }

    /**
     * The directory of the caches and the logs of the test kernels of this copy of the bundle.
     */
    public static function getTemporaryDir(): string
    {
        return sys_get_temp_dir().'/acme-minimal-php-bundle/'.hash('xxh128', __DIR__);
    }

    private function getVarDir(): string
    {
        return self::getTemporaryDir();
    }
}
