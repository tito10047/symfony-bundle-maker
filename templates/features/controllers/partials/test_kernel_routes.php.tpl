<?php
/**
 * @var Tito10047\BundleMaker\Naming\BundleNaming   $naming
 * @var Tito10047\BundleMaker\Options\BundleOptions $options
 */
?>
// imported like an application does, see README.md
$routes->import('@<?= $naming->className ?>/config/<?= $options->configFormat->getFileName('routes') ?>');
