<?php

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

/*
 * Import these routes in the application with:
 *
 *     $routes->import('@AcmePhpKitchenSinkBundle/config/routes.php');
 *
 * See https://symfony.com/doc/current/bundles/best_practices.html#routing
 */
return static function (RoutingConfigurator $routes): void {
    $routes->add('acme_php_kitchen_sink_hello', '/hello/{name}')
        ->controller('acme_php_kitchen_sink.controller.hello')
        ->methods(['GET', 'HEAD'])
        ->defaults(['name' => 'World'])
        ->requirements(['name' => '\\w+'])
    ;

    $routes->add('acme_php_kitchen_sink_page', '/page/{page}.{_format}')
        ->controller('acme_php_kitchen_sink.controller.hello::page')
        ->defaults(['page' => 1, '_format' => 'html'])
        ->requirements(['page' => '\\d+', '_format' => 'html|json'])
    ;

    $routes->add('acme_php_kitchen_sink_home', '/')
        ->controller('acme_php_kitchen_sink.controller.hello')
    ;
};
