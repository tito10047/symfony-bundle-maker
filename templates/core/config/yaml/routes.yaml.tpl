<?php
/**
 * @var Tito10047\BundleMaker\Blueprint\BundleBlueprint $blueprint
 * @var Tito10047\BundleMaker\Naming\BundleNaming       $naming
 * @var Tito10047\BundleMaker\Template\TemplateHelper   $helper
 */
?>
# Import these routes in the application with:
#
#     <?= $naming->alias ?>:
#         resource: '@<?= $naming->className ?>/config/routes.yaml'
#
# See https://symfony.com/doc/current/bundles/best_practices.html#routing
<?php foreach ($blueprint->routes->getRoutes() as $route): ?>

<?= $helper->yamlKey($route->name) ?>:
    path: <?= $helper->yaml($route->path) ?>

    controller: <?= $helper->yaml($route->controller) ?>

<?php if ([] !== $route->methods): ?>
    methods: <?= $helper->yaml($route->methods) ?>

<?php endif ?>
<?php if ([] !== $route->defaults): ?>
    defaults: <?= $helper->yaml($route->defaults) ?>

<?php endif ?>
<?php if ([] !== $route->requirements): ?>
    requirements: <?= $helper->yaml($route->requirements) ?>

<?php endif ?>
<?php endforeach ?>
