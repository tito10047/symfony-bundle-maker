<?php
/**
 * @var Tito10047\BundleMaker\Naming\BundleNaming   $naming
 * @var Tito10047\BundleMaker\Options\BundleOptions $options
 */
?>
$routes->import(\dirname(__DIR__, 2).'/config/<?= $options->configFormat->getFileName('routes') ?>');
