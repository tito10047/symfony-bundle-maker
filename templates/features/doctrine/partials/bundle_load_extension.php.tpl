<?php
use Tito10047\BundleMaker\Feature\DoctrineFeature;

/**
 * @var Tito10047\BundleMaker\Naming\BundleNaming     $naming
 * @var Tito10047\BundleMaker\Template\TemplateHelper $helper
 */
?>
$container->parameters()
    ->set(<?= $helper->php(DoctrineFeature::managerParameter($naming)) ?>, $config['model_manager_name'])
;
