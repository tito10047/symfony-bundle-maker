<?php
use Tito10047\BundleMaker\Feature\ControllersFeature;
use Tito10047\BundleMaker\Feature\TranslationsFeature;
use Tito10047\BundleMaker\Feature\TwigFeature;

/**
 * @var Tito10047\BundleMaker\Blueprint\BundleBlueprint $blueprint
 * @var Tito10047\BundleMaker\Naming\BundleNaming       $naming
 */
$translations = $blueprint->hasFeature(TranslationsFeature::NAME);
$domain = $naming->translationDomain;
$message = static fn (string $message): string => $translations ? "{{ '".$message."'|trans({}, '".$domain."') }}" : $message;
$css = $naming->slug.'-hello';
?>
{#
<?php if ($blueprint->hasFeature(ControllersFeature::NAME)): ?>
 # Greets people, rendered by <?= ControllersFeature::className($naming) ?>.
<?php else: ?>
 # Greets people: render it in your templates with {{ include('<?= TwigFeature::templateName($naming) ?>', {greeting: 'Hello Jane!'}) }}.
<?php endif ?>
 #
 # Variables:
 #   greeting  string  the greeting, e.g. "Hello Jane!"
 #
 # It is a fragment, without layout, so applications can display it anywhere. Override it in the
 # <?= TwigFeature::overrideDirectory($naming) ?>hello.html.twig file of your application,
 # or only its "title" block by extending '@!<?= $naming->twigNamespace ?>/hello.html.twig'.
 #}
<section class="<?= $css ?>">
    <h1 class="<?= $css ?>__title">{% block title %}<?= $message(TranslationsFeature::TITLE_MESSAGE) ?>{% endblock %}</h1>
    <p class="<?= $css ?>__greeting">{{ greeting }}</p>
</section>
