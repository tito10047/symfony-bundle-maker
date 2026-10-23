<?php
use Tito10047\BundleMaker\Contributor\Core\GreeterContributor;

/**
 * @var Tito10047\BundleMaker\Naming\BundleNaming     $naming
 * @var Tito10047\BundleMaker\Template\TemplateHelper $helper
 */
?>
$container->services()
    ->get(<?= $helper->php(GreeterContributor::serviceId($naming)) ?>)
        ->arg(0, $config['greeting'])
;
