<?php
/**
 * @var Tito10047\BundleMaker\Naming\BundleNaming $naming
 */
?>
## Configuration

The bundle works without configuration. To change its defaults, configure the
`<?= $naming->alias ?>` extension in your application:

```yaml
# config/packages/<?= $naming->alias ?>.yaml
<?= $naming->alias ?>:
    greeting: 'Hi'
```

Or in PHP:

```php
// config/packages/<?= $naming->alias ?>.php
namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return App::config([
    '<?= $naming->alias ?>' => [
        'greeting' => 'Hi',
    ],
]);
```

Run `php bin/console config:dump-reference <?= $naming->alias ?>` to list every option
and its default value, or read the [configuration reference](docs/index.md#configuration).
