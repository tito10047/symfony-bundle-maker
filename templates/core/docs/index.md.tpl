<?php
use Tito10047\BundleMaker\Blueprint\Slot;

/**
 * @var Tito10047\BundleMaker\Naming\BundleNaming   $naming
 * @var Tito10047\BundleMaker\Options\BundleOptions $options
 * @var Tito10047\BundleMaker\Template\SlotRenderer $slots
 */
?>
# <?= $naming->className ?>


<?= $options->description ?>


## Installation

Follow the [installation instructions](../README.md#installation) of the README.
<?php if ($slots->has(Slot::DocsSections)): ?>

<?= $slots->render(Slot::DocsSections) ?>
<?php endif ?>
