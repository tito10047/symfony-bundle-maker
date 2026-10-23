<?php

/*
 * Import these routes in the application with:
 *
 *     $routes->import('@AcmeEverythingPhpArrayBundle/config/routes.php');
 *
 * This file returns the same structure as a YAML file, which requires Symfony 7.4 or newer.
 *
 * See https://symfony.com/doc/current/bundles/best_practices.html#routing
 */
return [
    'acme_everything_php_array_hello' => [
        'path' => '/hello/{name}',
        'controller' => 'acme_everything_php_array.controller.hello',
        'methods' => ['GET'],
        'defaults' => ['name' => 'World'],
    ],
];
