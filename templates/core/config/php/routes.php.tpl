<?php
/**
 * @var Tito10047\BundleMaker\Blueprint\BundleBlueprint $blueprint
 * @var Tito10047\BundleMaker\Naming\BundleNaming       $naming
 * @var Tito10047\BundleMaker\Template\TemplateHelper   $helper
 */
$first = true;
?>
<?= "<?php\n" ?>

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

/*
 * Import these routes in the application with:
 *
 *     $routes->import('@<?= $naming->className ?>/config/routes.php');
 *
 * See https://symfony.com/doc/current/bundles/best_practices.html#routing
 */
return static function (RoutingConfigurator $routes): void {
<?php foreach ($blueprint->routes->getRoutes() as $route): ?>
<?php if (!$first): ?>

<?php endif ?>
<?php $first = false ?>
    $routes->add(<?= $helper->php($route->name) ?>, <?= $helper->php($route->path) ?>)
        ->controller(<?= $helper->php($route->controller) ?>)
<?php if ([] !== $route->methods): ?>
        ->methods(<?= $helper->php($route->methods) ?>)
<?php endif ?>
<?php if ([] !== $route->defaults): ?>
        ->defaults(<?= $helper->php($route->defaults) ?>)
<?php endif ?>
<?php if ([] !== $route->requirements): ?>
        ->requirements(<?= $helper->php($route->requirements) ?>)
<?php endif ?>
    ;
<?php endforeach ?>
};
