<?php
use Tito10047\BundleMaker\Contributor\Core\GreeterContributor;

/**
 * @var Tito10047\BundleMaker\Blueprint\BundleBlueprint $blueprint
 * @var Tito10047\BundleMaker\Naming\BundleNaming       $naming
 */
$interface = GreeterContributor::interfaceName($naming);
?>
## Usage

The bundle provides a greeter service. Type-hint `<?= $interface ?>`
in the constructor of your services to get it injected by autowiring:

```php
namespace App\Service;

use <?= $interface ?>;

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
