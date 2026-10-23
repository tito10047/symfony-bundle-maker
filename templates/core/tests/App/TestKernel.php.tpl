<?php
use Tito10047\BundleMaker\Blueprint\ListSlot;
use Tito10047\BundleMaker\Blueprint\Slot;

use Tito10047\BundleMaker\Feature\SemanticConfigFeature;

/**
 * @var Tito10047\BundleMaker\Blueprint\BundleBlueprint $blueprint
 * @var Tito10047\BundleMaker\Naming\BundleNaming       $naming
 * @var Tito10047\BundleMaker\Template\SlotRenderer     $slots
 * @var Tito10047\BundleMaker\Template\TemplateHelper   $helper
 */
// the configuration tests boot the kernel with several configurations of the bundle
$configurable = $blueprint->hasFeature(SemanticConfigFeature::NAME);
$hasRoutes = $slots->has(Slot::TestKernelConfigureRoutes);
$bundles = $slots->values(ListSlot::TestKernelBundles);
$imports = $helper->imports([
    $naming->fqcn,
    'Symfony\Bundle\FrameworkBundle\FrameworkBundle',
    'Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait',
    'Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator',
    'Symfony\Component\HttpKernel\Bundle\Bundle',
    'Symfony\Component\HttpKernel\Kernel',
    ...($hasRoutes ? ['Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator'] : []),
    ...$bundles,
    ...$slots->values(ListSlot::TestKernelUses),
], $naming->testsNamespace.'\App');
?>
<?= "<?php\n" ?>

namespace <?= $naming->testsNamespace ?>\App;

<?= $imports->render() ?>

/**
 * Minimal application registering <?= $naming->className ?>, booted by the integration and functional tests.
 *
 * The bundle directory is read-only: the cache and the logs are written to the system temporary
 * directory, in a directory specific to the configuration of the kernel. tests/bootstrap.php
 * removes them before each run, so that every run compiles fresh containers.
 */
final class TestKernel extends <?= $imports->name('Symfony\Component\HttpKernel\Kernel') ?>

{
    use <?= $imports->name('Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait') ?>;
<?php if ($configurable): ?>

    /**
     * @param array<string, array<string, mixed>> $extensionConfigs configuration of the extensions, indexed by alias,
     *                                                              applied last, e.g. ['<?= $naming->alias ?>' => ['greeting' => 'Hi']]
     */
    public function __construct(
        string $environment = 'test',
        bool $debug = true,
        private readonly array $extensionConfigs = [],
    ) {
        parent::__construct($environment, $debug);
    }
<?php endif ?>

    /**
     * The bundles are typed as Bundle: the BundleInterface of HttpKernel is deprecated since Symfony 8.1,
     * and the interface replacing it does not exist in Symfony 7.4.
     *
     * @return iterable<<?= $imports->name('Symfony\Component\HttpKernel\Bundle\Bundle') ?>>
     */
    public function registerBundles(): iterable
    {
        yield new <?= $imports->name('Symfony\Bundle\FrameworkBundle\FrameworkBundle') ?>();
<?php foreach ($bundles as $bundle): ?>
        yield new <?= $imports->name($bundle) ?>();
<?php endforeach ?>
        yield new <?= $imports->name($naming->fqcn) ?>();
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

    protected function configureContainer(<?= $imports->name('Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator') ?> $container): void
    {
        $container->extension('framework', [
            'test' => true,
        ]);
<?php if ($slots->has(Slot::TestKernelConfigureContainer)): ?>

<?= $slots->render(Slot::TestKernelConfigureContainer, 8) ?>
<?php endif ?>
<?php if ($configurable): ?>

        foreach ($this->extensionConfigs as $alias => $config) {
            $container->extension($alias, $config);
        }
<?php endif ?>
    }
<?php if ($hasRoutes): ?>

    protected function configureRoutes(<?= $imports->name('Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator') ?> $routes): void
    {
<?= $slots->render(Slot::TestKernelConfigureRoutes, 8) ?>
    }
<?php endif ?>

    /**
     * The directory of the caches and the logs of the test kernels of this copy of the bundle.
     */
    public static function getTemporaryDir(): string
    {
        return sys_get_temp_dir().'/<?= $naming->slug ?>-bundle/'.hash('xxh128', __DIR__);
    }

    private function getVarDir(): string
    {
<?php if ($configurable): ?>
        return self::getTemporaryDir().'/'.hash('xxh128', serialize($this->extensionConfigs));
<?php else: ?>
        return self::getTemporaryDir();
<?php endif ?>
    }
}
