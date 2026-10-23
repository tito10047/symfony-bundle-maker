<?php

use Acme\PhpKitchenSinkBundle\CacheWarmer\Formatter;
use Acme\PhpKitchenSinkBundle\Controller\HelloController;
use Acme\PhpKitchenSinkBundle\Formatter\FormatterInterface;
use Acme\PhpKitchenSinkBundle\Greeter;
use Acme\PhpKitchenSinkBundle\GreeterInterface;
use Acme\PhpKitchenSinkBundle\KitchenSink;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

/*
 * Services are defined explicitly: no autowiring, no autoconfiguration, private by default,
 * with ids prefixed by "acme_php_kitchen_sink." and aliases for autowiring.
 *
 * See https://symfony.com/doc/current/bundles/best_practices.html#services
 */
return static function (ContainerConfigurator $container): void {
    $container->parameters()
        ->set('acme_php_kitchen_sink.kitchen_sink.greeting', 'Hello %%name%%')
        ->set('acme_php_kitchen_sink.channels', ['email', '@sms', 'it\'s'])
        ->set('acme_php_kitchen_sink.enabled', true)
    ;

    $container->services()
        ->set('acme_php_kitchen_sink.greeter', Greeter::class)

        ->set('acme_php_kitchen_sink.kitchen_sink', KitchenSink::class)
            ->args([
                param('acme_php_kitchen_sink.kitchen_sink.greeting'),
                service('acme_php_kitchen_sink.formatter'),
            ])

        ->set('acme_php_kitchen_sink.formatter', Acme\PhpKitchenSinkBundle\Formatter\Formatter::class)

        ->set('.acme_php_kitchen_sink.cache_warmer', Formatter::class)
            ->args([
                service('acme_php_kitchen_sink.formatter'),
            ])
            ->tag('kernel.cache_warmer', ['priority' => 10])

        ->set('acme_php_kitchen_sink.controller.hello', HelloController::class)
            ->args([
                service('acme_php_kitchen_sink.greeter'),
            ])
            ->tag('controller.service_arguments')

        ->alias(GreeterInterface::class, 'acme_php_kitchen_sink.greeter')

        ->alias(FormatterInterface::class, 'acme_php_kitchen_sink.formatter')

        ->alias('acme_php_kitchen_sink.default_greeter', 'acme_php_kitchen_sink.greeter')
    ;
};
