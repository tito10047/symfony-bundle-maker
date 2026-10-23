<?php
/**
 * @var Tito10047\BundleMaker\Naming\BundleNaming     $naming
 * @var Tito10047\BundleMaker\Template\TemplateHelper $helper
 */
$imports = $helper->imports([
    $naming->testsNamespace.'\App\TestKernel',
    'Doctrine\Persistence\ManagerRegistry',
]);
?>
<?= "<?php\n" ?>

/*
 * Gives the entity manager of the test application to PHPStan: phpstan/phpstan-doctrine reads
 * the mapping of the entities from it (doctrine.objectManagerLoader in phpstan.dist.neon).
 */

<?= $imports->render() ?>

// in debug mode, the container and the mapping are refreshed when the mapping files change
$kernel = new <?= $imports->name($naming->testsNamespace.'\App\TestKernel') ?>('test', true);
$kernel->boot();

$doctrine = $kernel->getContainer()->get('doctrine');
if (!$doctrine instanceof <?= $imports->name('Doctrine\Persistence\ManagerRegistry') ?>) {
    throw new LogicException('The "doctrine" service must be a ManagerRegistry.');
}

return $doctrine->getManager();
