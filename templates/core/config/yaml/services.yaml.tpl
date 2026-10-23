<?php
/**
 * @var Tito10047\BundleMaker\Blueprint\BundleBlueprint $blueprint
 * @var Tito10047\BundleMaker\Naming\BundleNaming       $naming
 * @var Tito10047\BundleMaker\Options\BundleOptions     $options
 * @var Tito10047\BundleMaker\Template\TemplateHelper   $helper
 */
$format = $options->configFormat;
$config = $blueprint->services;
?>
# Services are defined explicitly: no autowiring, no autoconfiguration, private by default,
# with ids prefixed by "<?= $naming->alias ?>." and aliases for autowiring.
#
# See https://symfony.com/doc/current/bundles/best_practices.html#services
<?php if ([] !== $config->getParameters()): ?>

parameters:
<?php foreach ($config->getParameters() as $name => $value): ?>
    <?= $helper->yamlKey($name) ?>: <?= $helper->configValue($value, $format) ?>

<?php endforeach ?>
<?php endif ?>

services:
<?php if ([] === $config->getServices() && [] === $config->getAliases()): ?>
    # <?= $naming->alias ?>.some_service:
    #     class: <?= $naming->namespace ?>\SomeService
    #     arguments: ['@<?= $naming->alias ?>.other_service']
<?php endif ?>
<?php $first = true ?>
<?php foreach ($config->getServices() as $service): ?>
<?php if (!$first): ?>

<?php endif ?>
<?php $first = false ?>
    <?= $helper->yamlKey($service->id) ?>:
        class: <?= $helper->yamlKey($service->class) ?>

<?php if ([] !== $service->arguments): ?>
        arguments:
<?php foreach ($service->arguments as $argument): ?>
            - <?= $helper->serviceArgument($argument, $format) ?>

<?php endforeach ?>
<?php endif ?>
<?php if ([] !== $service->tags): ?>
        tags:
<?php foreach ($service->tags as $tag): ?>
            - <?= $helper->yaml(['name' => $tag->name] + $tag->attributes) ?>

<?php endforeach ?>
<?php endif ?>
<?php endforeach ?>
<?php foreach ($config->getAliases() as $alias): ?>
<?php if (!$first): ?>

<?php endif ?>
<?php $first = false ?>
    <?= $helper->yamlKey($alias->id) ?>:
        alias: <?= $helper->yamlKey($alias->target) ?>

<?php endforeach ?>
