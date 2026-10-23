<?php

use Acme\MinimalPhpArrayBundle\Greeter;
use Acme\MinimalPhpArrayBundle\GreeterInterface;

/*
 * Services are defined explicitly: no autowiring, no autoconfiguration, private by default,
 * with ids prefixed by "acme_minimal_php_array." and aliases for autowiring.
 *
 * This file returns the same structure as a YAML file, which requires Symfony 7.4 or newer.
 *
 * See https://symfony.com/doc/current/bundles/best_practices.html#services
 */
return [
    'parameters' => [
        'acme_minimal_php_array.greeting' => 'Hello',
    ],
    'services' => [
        'acme_minimal_php_array.greeter' => [
            'class' => Greeter::class,
            'arguments' => [
                '%acme_minimal_php_array.greeting%',
            ],
        ],
        GreeterInterface::class => [
            'alias' => 'acme_minimal_php_array.greeter',
        ],
    ],
];
