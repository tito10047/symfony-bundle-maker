<?php

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;

/*
 * Configuration tree of the "acme_everything_php_array" extension.
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
            ->arrayNode('greeting_header')
                ->info('Adds the greeting to the responses of the application, in the "X-Acme-Everything-Php-Array-Greeting" header.')
                ->canBeEnabled()
            ->end()
        ->end()
    ;
};
