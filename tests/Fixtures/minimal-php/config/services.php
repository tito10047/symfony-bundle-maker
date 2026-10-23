<?php

use Acme\MinimalPhpBundle\Greeter;
use Acme\MinimalPhpBundle\GreeterInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\param;

/*
 * Services are defined explicitly: no autowiring, no autoconfiguration, private by default,
 * with ids prefixed by "acme_minimal_php." and aliases for autowiring.
 *
 * See https://symfony.com/doc/current/bundles/best_practices.html#services
 */
return static function (ContainerConfigurator $container): void {
    $container->parameters()
        ->set('acme_minimal_php.greeting', 'Hello')
    ;

    $container->services()
        ->set('acme_minimal_php.greeter', Greeter::class)
            ->args([
                param('acme_minimal_php.greeting'),
            ])

        ->alias(GreeterInterface::class, 'acme_minimal_php.greeter')
    ;
};
