<?php

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;

/*
 * Configuration tree of the "acme_blog" extension.
 *
 * Keep it static (no environment variables, no container parameters): Symfony
 * dumps it as the configuration reference of the bundle.
 *
 * See https://symfony.com/doc/current/bundles/configuration.html
 */
return static function (DefinitionConfigurator $definition): void {
    $definition->rootNode()
        ->children()
            ->stringNode('greeting')
                ->info('The word people are greeted with, e.g. "Hello" in "Hello Jane!".')
                ->example('Hi')
                ->defaultValue('Hello')
                ->cannotBeEmpty()
            ->end()
        ->end()
    ;
};
