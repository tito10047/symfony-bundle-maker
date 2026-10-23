<?php
/**
 * @var Tito10047\BundleMaker\Blueprint\BundleBlueprint $blueprint
 * @var Tito10047\BundleMaker\Naming\BundleNaming       $naming
 * @var Tito10047\BundleMaker\Options\BundleOptions     $options
 * @var Tito10047\BundleMaker\Template\TemplateHelper   $helper
 */
$format = $options->configFormat;
$config = $blueprint->services;
$imports = $helper->imports($config->getReferencedClasses());
?>
<?= "<?php\n" ?>

<?php if (!$imports->isEmpty()): ?>
<?= $imports->render() ?>

<?php endif ?>
/*
 * Services are defined explicitly: no autowiring, no autoconfiguration, private by default,
 * with ids prefixed by "<?= $naming->alias ?>." and aliases for autowiring.
 *
 * This file returns the same structure as a YAML file, which requires Symfony 7.4 or newer.
 *
 * See https://symfony.com/doc/current/bundles/best_practices.html#services
 */
return [
<?php if ([] !== $config->getParameters()): ?>
    'parameters' => [
<?php foreach ($config->getParameters() as $name => $value): ?>
        <?= $helper->php($name) ?> => <?= $helper->configValue($value, $format) ?>,
<?php endforeach ?>
    ],
<?php endif ?>
    'services' => [
<?php if ([] === $config->getServices() && [] === $config->getAliases()): ?>
        // '<?= $naming->alias ?>.some_service' => [
        //     'class' => SomeService::class,
        //     'arguments' => ['@<?= $naming->alias ?>.other_service'],
        // ],
<?php endif ?>
<?php foreach ($config->getServices() as $service): ?>
        <?= $helper->php($service->id) ?> => [
            'class' => <?= $imports->name($service->class) ?>::class,
<?php if ([] !== $service->arguments): ?>
            'arguments' => [
<?php foreach ($service->arguments as $argument): ?>
                <?= $helper->serviceArgument($argument, $format) ?>,
<?php endforeach ?>
            ],
<?php endif ?>
<?php if ([] !== $service->tags): ?>
            'tags' => [
<?php foreach ($service->tags as $tag): ?>
                <?= $helper->php(['name' => $tag->name] + $tag->attributes) ?>,
<?php endforeach ?>
            ],
<?php endif ?>
        ],
<?php endforeach ?>
<?php foreach ($config->getAliases() as $alias): ?>
        <?= str_contains($alias->id, '\\') ? $imports->name($alias->id).'::class' : $helper->php($alias->id) ?> => [
            'alias' => <?= $helper->php($alias->target) ?>,
        ],
<?php endforeach ?>
    ],
];
