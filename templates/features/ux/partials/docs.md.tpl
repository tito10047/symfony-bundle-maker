<?php
use Tito10047\BundleMaker\Feature\TwigFeature;
use Tito10047\BundleMaker\Feature\UxFeature;

/**
 * @var Tito10047\BundleMaker\Blueprint\BundleBlueprint $blueprint
 * @var Tito10047\BundleMaker\Naming\BundleNaming       $naming
 */
$name = UxFeature::controllerName($naming);
$identifier = UxFeature::controllerIdentifier($naming);
?>
## Stimulus Controller

The bundle is a [Symfony UX](https://ux.symfony.com/) package: it provides the
`<?= $name ?>` [Stimulus](https://stimulus.hotwired.dev/) controller
(`assets/src/<?= UxFeature::CONTROLLER ?>_controller.js`), which closes the element it is attached
to. It is loaded lazily: only when a page uses it.
<?php if ($blueprint->hasFeature(TwigFeature::NAME)): ?>
The [templates](#templates) use it to close the greeting.
<?php endif ?>

Use it with the `stimulus_*()` Twig functions of StimulusBundle:

```twig
<section {{ stimulus_controller('<?= $name ?>') }}>
    <p>Hello Jane!</p>
    <button type="button" {{ stimulus_action('<?= $name ?>', 'close') }}>Close</button>
</section>
```

Or with the HTML attributes they render:

```html
<section data-controller="<?= $identifier ?>">
    <p>Hello Jane!</p>
    <button type="button" data-action="<?= $identifier ?>#close">Close</button>
</section>
```

Before closing the element, the controller dispatches the `<?= $identifier ?>:close`
event. Cancel it to keep the element open:

```js
document.addEventListener('<?= $identifier ?>:close', (event) => {
    if (!confirm('Close the greeting?')) {
        event.preventDefault();
    }
});
```

When your application uses AssetMapper, the bundle adds its `assets/src/` directory to
the AssetMapper paths, under the `<?= UxFeature::packageName($naming) ?>` namespace: run
`php bin/console debug:asset-map` to list its files. Disable the controller in the
`assets/controllers.json` file of your application to stop loading it.
See [Create a UX bundle](https://symfony.com/doc/current/frontend/create_ux_bundle.html).
