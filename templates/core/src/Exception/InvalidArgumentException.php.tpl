<?php
/**
 * @var Tito10047\BundleMaker\Naming\BundleNaming $naming
 */
?>
<?= "<?php\n" ?>

namespace <?= $naming->namespace ?>\Exception;

/**
 * Thrown when an argument has an invalid value.
 */
class InvalidArgumentException extends \InvalidArgumentException implements ExceptionInterface
{
}
