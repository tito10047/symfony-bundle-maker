<?php

use Acme\EverythingPhpBundle\Controller\HelloController;
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

        ->alias(GreeterInterface::class, 'acme_everything_php.greeter')
    ;
};
