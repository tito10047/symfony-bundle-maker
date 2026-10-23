<?php
use Tito10047\BundleMaker\Contributor\Core\GreeterContributor;
use Tito10047\BundleMaker\Feature\ControllersFeature;

/**
 * @var Tito10047\BundleMaker\Naming\BundleNaming $naming
 */
$route = ControllersFeature::routeName($naming);
$prefix = '/'.$naming->slug;
$greeting = GreeterContributor::DEFAULT_GREETING;
?>
## Routes

The bundle provides a page greeting people, once its routes are imported
(see the [installation instructions](../README.md#installation)):

| Route | Path | Controller |
|---|---|---|
| `<?= $route ?>` | `/hello/{name}` | `<?= ControllersFeature::className($naming) ?>` |

With the `<?= $prefix ?>` prefix, `<?= $prefix ?>/hello/Jane` greets "<?= $greeting ?> Jane!", and
`<?= $prefix ?>/hello` greets "<?= $greeting ?> <?= ControllersFeature::DEFAULT_NAME ?>!".
The page is plain text.

Link to the page by the name of its route, never by its path, which depends
on the prefix chosen by the application:

```twig
<a href="{{ path('<?= $route ?>', {name: 'Jane'}) }}">Greet Jane</a>
```

Run `php bin/console debug:router <?= $route ?>` to display the route.
