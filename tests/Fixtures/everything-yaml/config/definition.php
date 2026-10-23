<?php

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;

/*
 * Configuration tree of the "tito10047_everything_yaml" extension.
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
                ->info('Adds the greeting to the responses of the application, in the "X-Tito10047-Everything-Yaml-Greeting" header.')
                ->canBeEnabled()
            ->end()
            ->scalarNode('model_manager_name')
                ->info('The Doctrine entity manager mapping the entities of the bundle, the default one when null.')
                ->defaultNull()
            ->end()
        ->end()
    ;
};
