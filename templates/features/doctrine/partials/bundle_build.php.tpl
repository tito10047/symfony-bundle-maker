<?php
use Tito10047\BundleMaker\Feature\DoctrineFeature;

/**
 * @var Tito10047\BundleMaker\Naming\BundleNaming     $naming
 * @var Tito10047\BundleMaker\Template\TemplateHelper $helper
 */
?>
// maps the entities with the XML files of config/doctrine/ when the application uses Doctrine ORM,
// in the entity manager named by the "<?= DoctrineFeature::managerParameter($naming) ?>" parameter, or the default one
if (class_exists(DoctrineOrmMappingsPass::class) && class_exists(XmlDriver::class)) {
    $container->addCompilerPass(DoctrineOrmMappingsPass::createXmlMappingDriver(
        [$this->getPath().'/config/doctrine' => <?= $helper->php($naming->namespace.'\Entity') ?>],
        [<?= $helper->php(DoctrineFeature::managerParameter($naming)) ?>],
        'doctrine.default_entity_manager',
        enableXsdValidation: true,
    ));
}
