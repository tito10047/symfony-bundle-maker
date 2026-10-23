<?php

namespace MyVendor\EverythingYamlBundle;

use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Doctrine\ORM\Mapping\Driver\XmlDriver;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

/**
 * Entry point of the bundle: loads its configuration and its services into the container of the application.
 *
 * @see https://symfony.com/doc/current/bundles/best_practices.html
 */
final class MyVendorEverythingYamlBundle extends AbstractBundle
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
        $container->import('../config/services.yaml');

        $container->services()
            ->get('my_vendor_everything_yaml.greeter')
                ->arg(0, $config['greeting'])
        ;

        if (!$config['greeting_header']['enabled']) {
            $builder->removeDefinition('my_vendor_everything_yaml.event_listener.greeting_header');
        }

        $container->parameters()
            ->set('my_vendor_everything_yaml.model_manager_name', $config['model_manager_name'])
        ;
    }

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        // maps the entities with the XML files of config/doctrine/ when the application uses Doctrine ORM,
        // in the entity manager named by the "my_vendor_everything_yaml.model_manager_name" parameter, or the default one
        if (class_exists(DoctrineOrmMappingsPass::class) && class_exists(XmlDriver::class)) {
            $container->addCompilerPass(DoctrineOrmMappingsPass::createXmlMappingDriver(
                [$this->getPath().'/config/doctrine' => 'MyVendor\\EverythingYamlBundle\\Entity'],
                ['my_vendor_everything_yaml.model_manager_name'],
                'doctrine.default_entity_manager',
                enableXsdValidation: true,
            ));
        }
    }
}
