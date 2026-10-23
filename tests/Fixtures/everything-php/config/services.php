<?php

use Acme\EverythingPhpBundle\Command\HelloCommand;
use Acme\EverythingPhpBundle\Controller\HelloController;
use Acme\EverythingPhpBundle\EventListener\GreetingHeaderListener;
use Acme\EverythingPhpBundle\Greeter;
use Acme\EverythingPhpBundle\GreeterInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

/*
 * Services are defined explicitly: no autowiring, no autoconfiguration, private by default,
 * with ids prefixed by "acme_everything_php." and aliases for autowiring.
 *
 * See https://symfony.com/doc/current/bundles/best_practices.html#services
 */
return static function (ContainerConfigurator $container): void {
    $container->services()
        ->set('acme_everything_php.greeter', Greeter::class)

        ->set('acme_everything_php.controller.hello', HelloController::class)
            ->args([
                service('acme_everything_php.greeter'),
                service('twig'),
            ])
            ->tag('controller.service_arguments')

        ->set('acme_everything_php.command.hello', HelloCommand::class)
            ->args([
                service('acme_everything_php.greeter'),
            ])
            ->tag('console.command')

        ->set('acme_everything_php.event_listener.greeting_header', GreetingHeaderListener::class)
            ->args([
                service('acme_everything_php.greeter'),
            ])
            ->tag('kernel.event_listener', ['event' => 'kernel.response'])

        ->alias(GreeterInterface::class, 'acme_everything_php.greeter')
    ;
};
