<?php
use Tito10047\BundleMaker\Contributor\Core\GreeterContributor;

/**
 * @var Tito10047\BundleMaker\Blueprint\BundleBlueprint $blueprint
 * @var Tito10047\BundleMaker\Naming\BundleNaming       $naming
 * @var Tito10047\BundleMaker\Template\TemplateHelper   $helper
 */
$interface = GreeterContributor::interfaceName($naming);
$imports = $helper->imports([$interface], 'App\Service');
?>
## Usage

The bundle provides a greeter service. Type-hint `<?= $interface ?>`
in the constructor of your services to get it injected by autowiring:

```php
namespace App\Service;

<?= $imports->render() ?>

final class Welcome
{
    public function __construct(
        private readonly GreeterInterface $greeter,
    ) {
    }

    public function welcome(string $name): string
    {
        return $this->greeter->greet($name); // "<?= GreeterContributor::DEFAULT_GREETING ?> Jane!" for "Jane"
    }
}
```
<?php if (!GreeterContributor::isConfigurable($blueprint)): ?>

The greeting is the `<?= GreeterContributor::greetingParameter($naming) ?>` parameter (`<?= GreeterContributor::DEFAULT_GREETING ?>` by default).
Change it in the configuration of your application:

```yaml
# config/services.yaml
parameters:
    <?= GreeterContributor::greetingParameter($naming) ?>: 'Hi'
```
<?php endif ?>

Every exception thrown by the bundle implements `<?= $naming->namespace ?>\Exception\ExceptionInterface`.
