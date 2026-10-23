<?php
/**
 * @var Tito10047\BundleMaker\Naming\BundleNaming     $naming
 * @var Tito10047\BundleMaker\Template\TemplateHelper $helper
 */
$imports = $helper->imports([
    $naming->testsNamespace.'\App\TestKernel',
    'Symfony\Component\Filesystem\Filesystem',
]);
?>
<?= "<?php\n" ?>

<?= $imports->render() ?>

require __DIR__.'/../vendor/autoload.php';

// a kernel reuses the container compiled by a previous run: the code of the bundle building it
// (the extension, the compiler passes) would not run, and would be missing from the code coverage
(new Filesystem())->remove(TestKernel::getTemporaryDir());
