<?php
use Tito10047\BundleMaker\Feature\UxFeature;

/**
 * @var Tito10047\BundleMaker\Naming\BundleNaming $naming
 */
$package = UxFeature::packageName($naming);
?>
Enable the Stimulus Controller

The bundle provides a [Stimulus](https://stimulus.hotwired.dev/) controller, so
your application needs [StimulusBundle](https://symfony.com/bundles/StimulusBundle/current/index.html).
Symfony Flex enables the controller for you. Without Flex, enable it in the
`assets/controllers.json` file of your application:

```json
{
    "controllers": {
        "<?= $package ?>": {
            "<?= UxFeature::CONTROLLER ?>": {
                "enabled": true,
                "fetch": "lazy"
            }
        }
    },
    "entrypoints": []
}
```

With AssetMapper, also add Stimulus to the importmap of your application, if it
is not there yet:

```console
php bin/console importmap:require @hotwired/stimulus
```

With Webpack Encore, add the bundle to the dependencies of your `package.json`
(`"<?= $package ?>": "file:vendor/<?= $naming->packageName ?>/assets"`), then
run `npm install --force` and rebuild your assets.
