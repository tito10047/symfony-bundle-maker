<?php
use Tito10047\BundleMaker\Blueprint\ListSlot;

/**
 * @var Tito10047\BundleMaker\Template\SlotRenderer $slots
 */
$patterns = $slots->values(ListSlot::GitIgnore);
sort($patterns);
?>
<?php foreach ($patterns as $pattern): ?>
<?= $pattern ?>

<?php endforeach ?>
