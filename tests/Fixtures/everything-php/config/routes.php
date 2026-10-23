<?php

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

/*
 * Import these routes in the application with:
 *
 *     $routes->import('@ZetaEverythingPhpBundle/config/routes.php');
 *
 * See https://symfony.com/doc/current/bundles/best_practices.html#routing
 */
return static function (RoutingConfigurator $routes): void {
    $routes->add('zeta_everything_php_hello', '/hello/{name}')
        ->controller('zeta_everything_php.controller.hello')
        ->methods(['GET'])
        ->defaults(['name' => 'World'])
    ;
};
