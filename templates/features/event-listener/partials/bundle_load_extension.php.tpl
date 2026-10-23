<?php
use Tito10047\BundleMaker\Feature\EventListenerFeature;

/**
 * @var Tito10047\BundleMaker\Naming\BundleNaming     $naming
 * @var Tito10047\BundleMaker\Template\TemplateHelper $helper
 */
?>
if (!$config['greeting_header']['enabled']) {
    $builder->removeDefinition(<?= $helper->php(EventListenerFeature::serviceId($naming)) ?>);
}
