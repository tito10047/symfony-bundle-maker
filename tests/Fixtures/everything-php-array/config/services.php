<?php

use Acme\EverythingPhpArrayBundle\Controller\HelloController;
use Acme\EverythingPhpArrayBundle\Greeter;
use Acme\EverythingPhpArrayBundle\GreeterInterface;

/*
 * Services are defined explicitly: no autowiring, no autoconfiguration, private by default,
 * with ids prefixed by "acme_everything_php_array." and aliases for autowiring.
 *
 * This file returns the same structure as a YAML file, which requires Symfony 7.4 or newer.
 *
 * See https://symfony.com/doc/current/bundles/best_practices.html#services
 */
return [
    'services' => [
        'acme_everything_php_array.greeter' => [
            'class' => Greeter::class,
        ],
        'acme_everything_php_array.controller.hello' => [
            'class' => HelloController::class,
            'arguments' => [
                '@acme_everything_php_array.greeter',
            ],
            'tags' => [
                ['name' => 'controller.service_arguments'],
            ],
        ],
        GreeterInterface::class => [
            'alias' => 'acme_everything_php_array.greeter',
        ],
    ],
];
