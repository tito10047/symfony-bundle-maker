<?php
use Tito10047\BundleMaker\Blueprint\ListSlot;

/**
 * @var Tito10047\BundleMaker\Template\SlotRenderer $slots
 */
$paths = $slots->values(ListSlot::GitAttributesExportIgnore);
sort($paths);
?>
# Files and directories excluded from the archive installed by Composer
<?php foreach ($paths as $path): ?>
<?= $path ?> export-ignore
<?php endforeach ?>
