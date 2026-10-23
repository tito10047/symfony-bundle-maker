<?php

use Acme\BlogBundle\Greeter;
use Acme\BlogBundle\GreeterInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

/*
 * Services are defined explicitly: no autowiring, no autoconfiguration, private by default,
 * with ids prefixed by "acme_blog." and aliases for autowiring.
 *
 * See https://symfony.com/doc/current/bundles/best_practices.html#services
 */
return static function (ContainerConfigurator $container): void {
    $container->services()
        ->set('acme_blog.greeter', Greeter::class)

        ->alias(GreeterInterface::class, 'acme_blog.greeter')
    ;
};
