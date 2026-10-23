<?php

namespace Acme\EverythingPhpArrayBundle;

use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Doctrine\ORM\Mapping\Driver\XmlDriver;
use Symfony\Component\AssetMapper\AssetMapperInterface;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

/**
 * Entry point of the bundle: loads its configuration and its services into the container of the application.
 *
 * @see https://symfony.com/doc/current/bundles/best_practices.html
 */
final class AcmeEverythingPhpArrayBundle extends AbstractBundle
{
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->import('../config/definition.php');
    }

    public function prependExtension(ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        // exposes the Stimulus controllers to AssetMapper, when the application uses it,
        // see https://symfony.com/doc/current/frontend/create_ux_bundle.html#specifics-for-asset-mapper
        if (interface_exists(AssetMapperInterface::class) && $builder->hasExtension('framework')) {
            $builder->prependExtensionConfig('framework', [
                'asset_mapper' => [
                    'paths' => [
                        $this->getPath().'/assets/src' => '@acme/everything-php-array-bundle',
                    ],
                ],
            ]);
        }
    }

    /**
     * @param array<mixed> $config the processed configuration of the bundle
     */
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('../config/services.php');

        $container->services()
            ->get('acme_everything_php_array.greeter')
                ->arg(0, $config['greeting'])
        ;

        if (!$config['greeting_header']['enabled']) {
            $builder->removeDefinition('acme_everything_php_array.event_listener.greeting_header');
        }

        $container->parameters()
            ->set('acme_everything_php_array.model_manager_name', $config['model_manager_name'])
        ;
    }

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        // maps the entities with the XML files of config/doctrine/ when the application uses Doctrine ORM,
        // in the entity manager named by the "acme_everything_php_array.model_manager_name" parameter, or the default one
        if (class_exists(DoctrineOrmMappingsPass::class) && class_exists(XmlDriver::class)) {
            $container->addCompilerPass(DoctrineOrmMappingsPass::createXmlMappingDriver(
                [$this->getPath().'/config/doctrine' => 'Acme\\EverythingPhpArrayBundle\\Entity'],
                ['acme_everything_php_array.model_manager_name'],
                'doctrine.default_entity_manager',
                enableXsdValidation: true,
            ));
        }
    }
}
