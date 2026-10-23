<?php
/**
 * @var Tito10047\BundleMaker\Naming\BundleNaming $naming
 */
?>
<?= "<?php\n" ?>

namespace <?= $naming->namespace ?>\Exception;

/**
 * Implemented by every exception thrown by <?= $naming->className ?>, so they can be caught at once.
 */
interface ExceptionInterface extends \Throwable
{
}
