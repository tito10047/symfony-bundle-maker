<?php
use Tito10047\BundleMaker\Contributor\Core\GreeterContributor;
use Tito10047\BundleMaker\Feature\DoctrineFeature;
use Tito10047\BundleMaker\Feature\EventListenerFeature;

/**
 * @var Tito10047\BundleMaker\Blueprint\BundleBlueprint $blueprint
 * @var Tito10047\BundleMaker\Naming\BundleNaming       $naming
 */
$header = $blueprint->hasFeature(EventListenerFeature::NAME);
$doctrine = $blueprint->hasFeature(DoctrineFeature::NAME);
?>
## Configuration

The bundle works without configuration. These are the options of the
`<?= $naming->alias ?>` extension, with their default values:

```yaml
# config/packages/<?= $naming->alias ?>.yaml
<?= $naming->alias ?>:
    # The word people are greeted with, e.g. "Hello" in "Hello Jane!".
    greeting: '<?= GreeterContributor::DEFAULT_GREETING ?>' # Example: 'Hi'
<?php if ($header): ?>

    # Adds the greeting to the responses of the application, in the "<?= EventListenerFeature::headerName($naming) ?>" header.
    greeting_header:
        enabled: false
<?php endif ?>
<?php if ($doctrine): ?>

    # The Doctrine entity manager mapping the entities of the bundle, the default one when null.
    model_manager_name: null
<?php endif ?>
```

The same configuration in PHP:

```php
// config/packages/<?= $naming->alias ?>.php
namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return App::config([
    '<?= $naming->alias ?>' => [
        'greeting' => '<?= GreeterContributor::DEFAULT_GREETING ?>',
<?php if ($header): ?>
        'greeting_header' => [
            'enabled' => false,
        ],
<?php endif ?>
<?php if ($doctrine): ?>
        'model_manager_name' => null,
<?php endif ?>
    ],
]);
```

Run `php bin/console config:dump-reference <?= $naming->alias ?>` to display this
reference, and `php bin/console debug:config <?= $naming->alias ?>` to display the
configuration of your application.
