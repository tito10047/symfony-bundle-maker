<?php

use Acme\PhpArrayKitchenSinkBundle\CacheWarmer\Formatter;
use Acme\PhpArrayKitchenSinkBundle\Controller\HelloController;
use Acme\PhpArrayKitchenSinkBundle\Formatter\FormatterInterface;
use Acme\PhpArrayKitchenSinkBundle\Greeter;
use Acme\PhpArrayKitchenSinkBundle\GreeterInterface;
use Acme\PhpArrayKitchenSinkBundle\KitchenSink;

/*
 * Services are defined explicitly: no autowiring, no autoconfiguration, private by default,
 * with ids prefixed by "acme_php_array_kitchen_sink." and aliases for autowiring.
 *
 * This file returns the same structure as a YAML file, which requires Symfony 7.4 or newer.
 *
 * See https://symfony.com/doc/current/bundles/best_practices.html#services
 */
return [
    'parameters' => [
        'acme_php_array_kitchen_sink.kitchen_sink.greeting' => 'Hello %%name%%',
        'acme_php_array_kitchen_sink.channels' => ['email', '@@sms', 'it\'s'],
        'acme_php_array_kitchen_sink.enabled' => true,
    ],
    'services' => [
        'acme_php_array_kitchen_sink.greeter' => [
            'class' => Greeter::class,
        ],
        'acme_php_array_kitchen_sink.kitchen_sink' => [
            'class' => KitchenSink::class,
            'arguments' => [
                '%acme_php_array_kitchen_sink.kitchen_sink.greeting%',
                '@acme_php_array_kitchen_sink.formatter',
            ],
        ],
        'acme_php_array_kitchen_sink.formatter' => [
            'class' => Acme\PhpArrayKitchenSinkBundle\Formatter\Formatter::class,
        ],
        '.acme_php_array_kitchen_sink.cache_warmer' => [
            'class' => Formatter::class,
            'arguments' => [
                '@acme_php_array_kitchen_sink.formatter',
            ],
            'tags' => [
                ['name' => 'kernel.cache_warmer', 'priority' => 10],
            ],
        ],
        'acme_php_array_kitchen_sink.controller.hello' => [
            'class' => HelloController::class,
            'arguments' => [
                '@acme_php_array_kitchen_sink.greeter',
            ],
            'tags' => [
                ['name' => 'controller.service_arguments'],
            ],
        ],
        GreeterInterface::class => [
            'alias' => 'acme_php_array_kitchen_sink.greeter',
        ],
        FormatterInterface::class => [
            'alias' => 'acme_php_array_kitchen_sink.formatter',
        ],
        'acme_php_array_kitchen_sink.default_greeter' => [
            'alias' => 'acme_php_array_kitchen_sink.greeter',
        ],
    ],
];
