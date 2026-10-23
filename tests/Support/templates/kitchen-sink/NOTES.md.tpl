<?php
/**
 * @var string                                  $note
 * @var Tito10047\BundleMaker\Template\SlotRenderer $slots
 */
use Tito10047\BundleMaker\Blueprint\ListSlot;
?>
# Notes

<?= $note ?>


Uses: <?= implode(', ', $slots->values(ListSlot::BundleUses)) ?>

