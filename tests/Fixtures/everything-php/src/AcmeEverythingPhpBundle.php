<?php

namespace Acme\EverythingPhpBundle;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

/**
 * Entry point of the bundle: loads its configuration and its services into the container of the application.
 *
 * @see https://symfony.com/doc/current/bundles/best_practices.html
 */
final class AcmeEverythingPhpBundle extends AbstractBundle
{
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->import('../config/definition.php');
    }

    /**
     * @param array<mixed> $config the processed configuration of the bundle
     */
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('../config/services.php');

        $container->services()
            ->get('acme_everything_php.greeter')
                ->arg(0, $config['greeting'])
        ;

        if (!$config['greeting_header']['enabled']) {
            $builder->removeDefinition('acme_everything_php.event_listener.greeting_header');
        }
    }
}
