<?php
use Tito10047\BundleMaker\Contributor\Core\GreeterContributor;
use Tito10047\BundleMaker\Feature\DoctrineFeature;
use Tito10047\BundleMaker\Feature\EventListenerFeature;

/**
 * @var Tito10047\BundleMaker\Blueprint\BundleBlueprint $blueprint
 * @var Tito10047\BundleMaker\Naming\BundleNaming       $naming
 * @var Tito10047\BundleMaker\Template\TemplateHelper   $helper
 */
?>
<?= "<?php\n" ?>

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;

/*
 * Configuration tree of the "<?= $naming->alias ?>" extension.
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
                ->defaultValue(<?= $helper->php(GreeterContributor::DEFAULT_GREETING) ?>)
                ->cannotBeEmpty()
            ->end()
<?php if ($blueprint->hasFeature(EventListenerFeature::NAME)): ?>
            ->arrayNode('greeting_header')
                ->info(<?= $helper->php('Adds the greeting to the responses of the application, in the "'.EventListenerFeature::headerName($naming).'" header.') ?>)
                ->canBeEnabled()
            ->end()
<?php endif ?>
<?php if ($blueprint->hasFeature(DoctrineFeature::NAME)): ?>
            ->scalarNode('model_manager_name')
                ->info('The Doctrine entity manager mapping the entities of the bundle, the default one when null.')
                ->defaultNull()
            ->end()
<?php endif ?>
        ->end()
    ;
};
