<?php
/**
 * @var Tito10047\BundleMaker\Blueprint\BundleBlueprint $blueprint
 * @var Tito10047\BundleMaker\Naming\BundleNaming       $naming
 * @var Tito10047\BundleMaker\Template\TemplateHelper   $helper
 */
?>
<?= "<?php\n" ?>

/*
 * Import these routes in the application with:
 *
 *     $routes->import('@<?= $naming->className ?>/config/routes.php');
 *
 * This file returns the same structure as a YAML file, which requires Symfony 7.4 or newer.
 *
 * See https://symfony.com/doc/current/bundles/best_practices.html#routing
 */
return [
<?php foreach ($blueprint->routes->getRoutes() as $route): ?>
    <?= $helper->php($route->name) ?> => [
        'path' => <?= $helper->php($route->path) ?>,
        'controller' => <?= $helper->php($route->controller) ?>,
<?php if ([] !== $route->methods): ?>
        'methods' => <?= $helper->php($route->methods) ?>,
<?php endif ?>
<?php if ([] !== $route->defaults): ?>
        'defaults' => <?= $helper->php($route->defaults) ?>,
<?php endif ?>
<?php if ([] !== $route->requirements): ?>
        'requirements' => <?= $helper->php($route->requirements) ?>,
<?php endif ?>
    ],
<?php endforeach ?>
];
