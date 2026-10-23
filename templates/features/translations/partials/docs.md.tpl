<?php
use Tito10047\BundleMaker\Feature\TranslationsFeature;
use Tito10047\BundleMaker\Feature\TwigFeature;

/**
 * @var Tito10047\BundleMaker\Blueprint\BundleBlueprint $blueprint
 * @var Tito10047\BundleMaker\Naming\BundleNaming       $naming
 */
$domain = $naming->translationDomain;
$file = TranslationsFeature::fileName($naming, 'fr');
$messages = TranslationsFeature::getMessages($blueprint);
?>
## Translations

The messages of the bundle are in the `<?= $domain ?>` translation domain. They
are written in English, and their ids are the English messages: they display
well even when your application has no translator.
<?php if ($blueprint->hasFeature(TwigFeature::NAME)): ?>
The [templates](#templates) translate them with the `trans` filter:

```twig
{{ '<?= TranslationsFeature::TITLE_MESSAGE ?>'|trans({}, '<?= $domain ?>') }}
```
<?php else: ?>
Translate them with the translator:

```php
$translator->trans('<?= TranslationsFeature::TITLE_MESSAGE ?>', [], '<?= $domain ?>');
```
<?php endif ?>

To translate them into another language, or to change them, create a file
for the `<?= $domain ?>` domain in the `translations/` directory of your
application, e.g. `<?= $file ?>`. Its messages take precedence over the
messages of the bundle:

```xml
<?= '<?xml version="1.0" encoding="UTF-8"?>' ?>

<xliff xmlns="urn:oasis:names:tc:xliff:document:1.2" version="1.2">
    <file source-language="en" target-language="fr" datatype="plaintext" original="file.ext">
        <body>
            <trans-unit id="1">
                <source><?= $messages[0] ?></source>
                <target>Bienvenue !</target>
            </trans-unit>
        </body>
    </file>
</xliff>
```

Run `php bin/console debug:translation en --domain=<?= $domain ?>` to list the
messages of the bundle, and `php bin/console lint:xliff translations` to check
your files. See [How to Override any Part of a Bundle](https://symfony.com/doc/current/bundles/override.html#translations).
