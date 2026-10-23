<?php
use Tito10047\BundleMaker\Feature\PublicAssetsFeature;
use Tito10047\BundleMaker\Feature\TwigFeature;

/**
 * @var Tito10047\BundleMaker\Blueprint\BundleBlueprint $blueprint
 * @var Tito10047\BundleMaker\Naming\BundleNaming       $naming
 */
?>
## Web Assets

The bundle provides a stylesheet, `public/<?= PublicAssetsFeature::stylesheetPath($naming) ?>`. The
`assets:install` command installs it in the `public/<?= $naming->publicAssetsDirectory ?>/` directory
of your application (see the [installation instructions](../README.md#installation)).
When your application uses AssetMapper, AssetMapper serves it too, with a versioned URL.
<?php if ($blueprint->hasFeature(TwigFeature::NAME)): ?>
It styles the [templates](#templates) of the bundle.
<?php endif ?>

Link it in the `stylesheets` block of your layout, with the `asset()` function of the
[Asset component](https://symfony.com/doc/current/components/asset.html), which gives
the right URL in both cases:

```twig
{# templates/base.html.twig #}
{% block stylesheets %}
    <link rel="stylesheet" href="{{ asset('<?= PublicAssetsFeature::stylesheetPublicPath($naming) ?>') }}">
{% endblock %}
```
