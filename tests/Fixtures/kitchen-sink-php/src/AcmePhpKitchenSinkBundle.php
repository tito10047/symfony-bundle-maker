<?php

namespace Acme\PhpKitchenSinkBundle;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

/**
 * Entry point of the bundle: loads its configuration and its services into the container of the application.
 *
 * @see https://symfony.com/doc/current/bundles/best_practices.html
 */
final class AcmePhpKitchenSinkBundle extends AbstractBundle
{
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->import('../config/definition.php');
    }

    public function prependExtension(ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $builder->prependExtensionConfig('framework', ['enabled' => true]);
    }

    /**
     * @param array<mixed> $config the processed configuration of the bundle
     */
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('../config/services.php');

        $container->services()
            ->get('acme_php_kitchen_sink.greeter')
                ->arg(0, $config['greeting'])
        ;

        $container->parameters()
            ->set('acme_php_kitchen_sink.loaded', true)
        ;
    }

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->getParameterBag()->set('kitchen_sink.priority', PassConfig::TYPE_BEFORE_OPTIMIZATION);

        // compiler passes would be registered here, e.g. PassConfig::TYPE_BEFORE_OPTIMIZATION
    }
}
