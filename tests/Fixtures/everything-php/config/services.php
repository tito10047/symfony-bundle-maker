<?php

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Zeta\EverythingPhpBundle\Command\HelloCommand;
use Zeta\EverythingPhpBundle\Controller\HelloController;
use Zeta\EverythingPhpBundle\EventListener\GreetingHeaderListener;
use Zeta\EverythingPhpBundle\Greeter;
use Zeta\EverythingPhpBundle\GreeterInterface;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

/*
 * Services are defined explicitly: no autowiring, no autoconfiguration, private by default,
 * with ids prefixed by "zeta_everything_php." and aliases for autowiring.
 *
 * See https://symfony.com/doc/current/bundles/best_practices.html#services
 */
return static function (ContainerConfigurator $container): void {
    $container->services()
        ->set('zeta_everything_php.greeter', Greeter::class)

        ->set('zeta_everything_php.controller.hello', HelloController::class)
            ->args([
                service('zeta_everything_php.greeter'),
                service('twig'),
            ])
            ->tag('controller.service_arguments')

        ->set('zeta_everything_php.command.hello', HelloCommand::class)
            ->args([
                service('zeta_everything_php.greeter'),
            ])
            ->tag('console.command')

        ->set('zeta_everything_php.event_listener.greeting_header', GreetingHeaderListener::class)
            ->args([
                service('zeta_everything_php.greeter'),
            ])
            ->tag('kernel.event_listener', ['event' => 'kernel.response'])

        ->alias(GreeterInterface::class, 'zeta_everything_php.greeter')
    ;
};
