<?php
use Tito10047\BundleMaker\Feature\ControllersFeature;
use Tito10047\BundleMaker\Feature\TwigFeature;

/**
 * @var Tito10047\BundleMaker\Blueprint\BundleBlueprint $blueprint
 * @var Tito10047\BundleMaker\Naming\BundleNaming       $naming
 */
$template = TwigFeature::templateName($naming);
$override = TwigFeature::overrideDirectory($naming).'hello.html.twig';
?>
## Templates

The templates of the bundle are in the `@<?= $naming->twigNamespace ?>` Twig namespace.
<?php if ($blueprint->hasFeature(ControllersFeature::NAME)): ?>
`<?= $template ?>` renders the greeting page. Its only variable is
`greeting`, the greeting to display, e.g. "Hello Jane!". You can also include it
in your own templates:
<?php else: ?>
`<?= $template ?>` displays a greeting. Its only variable is `greeting`,
the greeting to display, e.g. "Hello Jane!". Include it in your templates:
<?php endif ?>

```twig
{{ include('<?= $template ?>', {greeting: 'Hello Jane!'}) }}
```

The templates extend no layout: they display well anywhere in your pages.

### Overriding the templates

To change a template, create a template with the same name in the
`<?= TwigFeature::overrideDirectory($naming) ?>` directory of your application. For example,
`<?= $override ?>` replaces `<?= $template ?>`:

```twig
{# <?= $override ?> #}
<p class="greeting">{{ greeting }}</p>
```

To only change its `title` block, extend the original template: the `!` prefix
always refers to the template of the bundle.

```twig
{# <?= $override ?> #}
{% extends '@!<?= $naming->twigNamespace ?>/hello.html.twig' %}

{% block title %}Nice to meet you!{% endblock %}
```

Clear the cache after creating an overriding template for the first time:
`php bin/console cache:clear`.
See [How to Override any Part of a Bundle](https://symfony.com/doc/current/bundles/override.html#templates).
