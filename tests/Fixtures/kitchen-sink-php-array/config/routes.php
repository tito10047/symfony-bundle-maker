<?php

/*
 * Import these routes in the application with:
 *
 *     $routes->import('@AcmePhpArrayKitchenSinkBundle/config/routes.php');
 *
 * This file returns the same structure as a YAML file, which requires Symfony 7.4 or newer.
 *
 * See https://symfony.com/doc/current/bundles/best_practices.html#routing
 */
return [
    'acme_php_array_kitchen_sink_hello' => [
        'path' => '/hello/{name}',
        'controller' => 'acme_php_array_kitchen_sink.controller.hello',
        'methods' => ['GET', 'HEAD'],
        'defaults' => ['name' => 'World'],
        'requirements' => ['name' => '\\w+'],
    ],
    'acme_php_array_kitchen_sink_page' => [
        'path' => '/page/{page}.{_format}',
        'controller' => 'acme_php_array_kitchen_sink.controller.hello::page',
        'defaults' => ['page' => 1, '_format' => 'html'],
        'requirements' => ['page' => '\\d+', '_format' => 'html|json'],
    ],
    'acme_php_array_kitchen_sink_home' => [
        'path' => '/',
        'controller' => 'acme_php_array_kitchen_sink.controller.hello',
    ],
];
