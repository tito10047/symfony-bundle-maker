<?php
/**
 * @var Tito10047\BundleMaker\Naming\BundleNaming     $naming
 * @var Tito10047\BundleMaker\Template\TemplateHelper $helper
 */
$imports = $helper->imports([$naming->namespace.'\Exception\InvalidArgumentException'], $naming->namespace);
?>
<?= "<?php\n" ?>

namespace <?= $naming->namespace ?>;

<?= $imports->render() ?>

/**
 * Greets people.
 *
 * Type-hint this interface in your services to get the "<?= $naming->alias ?>.greeter" service autowired.
 */
interface GreeterInterface
{
    /**
     * Returns the greeting of the given name, e.g. "Hello Jane!".
     *
     * @throws InvalidArgumentException when the name is empty
     */
    public function greet(string $name): string;
}
