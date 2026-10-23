<?php

declare(strict_types=1);

/*
 * This file is part of the tito10047/symfony-bundle-maker package.
 *
 * (c) Jozef Môstka <jozef@mostka.sk>
 * (c) Yonel Ceruto <yonelceruto@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tito10047\BundleMaker\Feature;

use Tito10047\BundleMaker\Blueprint\BundleBlueprint;
use Tito10047\BundleMaker\Blueprint\ListSlot;
use Tito10047\BundleMaker\Blueprint\Slot;
use Tito10047\BundleMaker\Naming\BundleNaming;

/**
 * A Doctrine ORM entity (src/Entity/Greeting.php) mapped with XML (config/doctrine/Greeting.orm.xml).
 *
 * As the best practices recommend, the entity is a plain PHP class and its mapping is an XML
 * file of config/doctrine/. The entity is concrete, so the bundle works out of the box.
 *
 * AcmeBlogBundle::build() registers the mapping with the DoctrineOrmMappingsPass of DoctrineBundle,
 * like reusable bundles do (FOSUserBundle, Sylius): in the entity manager named by the
 * "<alias>.model_manager_name" parameter (the "model_manager_name" option of the semantic
 * configuration), the default entity manager otherwise, and only when the application uses
 * Doctrine ORM. Nothing is prepended to the configuration of DoctrineBundle, which would create
 * a "default" entity manager in applications defining others, and enable the ORM in applications
 * only using DBAL. Doctrine stays optional: the bundle works without it.
 *
 * @see https://symfony.com/doc/current/bundles/best_practices.html#doctrine-entities-documents
 * @see https://symfony.com/doc/current/doctrine/mapping_model_classes.html
 */
final class DoctrineFeature extends AbstractFeature
{
    public const NAME = 'doctrine';
    protected const QUESTION = 'Add a Doctrine ORM entity?';

    /**
     * The lowest version tested, and the first one supporting the native lazy objects of PHP 8.4,
     * which DoctrineBundle 3 always uses.
     */
    public const ORM_CONSTRAINT = '^3.4';

    /**
     * DoctrineBundle 2.x (PHP 8.1+) supports Symfony 7.4 and 3.x (PHP 8.4+) Symfony 7.4 and 8.x:
     * Composer installs the one the PHP and Symfony versions support.
     */
    public const DOCTRINE_BUNDLE_CONSTRAINT = '^2.18 || ^3.0';

    public function contribute(BundleBlueprint $blueprint): void
    {
        $composer = $blueprint->composer;

        // optional: the bundle only maps its entity when the application uses Doctrine ORM
        $composer->requireDev('doctrine/doctrine-bundle', self::DOCTRINE_BUNDLE_CONSTRAINT);
        $composer->requireDev('doctrine/orm', self::ORM_CONSTRAINT);
        $composer->conflict('doctrine/orm', '<3.4');
        $composer->suggest('doctrine/doctrine-bundle', 'To store the greetings with Doctrine ORM');
        $composer->addKeyword('doctrine');

        $blueprint->addFile('features/doctrine/src/Entity/Greeting.php.tpl', 'src/Entity/Greeting.php');
        $blueprint->addFile('features/doctrine/config/doctrine/Greeting.orm.xml.tpl', 'config/doctrine/Greeting.orm.xml');
        $blueprint->addFile('features/doctrine/tests/Integration/Doctrine/GreetingMappingTest.php.tpl', 'tests/Integration/Doctrine/GreetingMappingTest.php');

        $blueprint->slots->addValue(ListSlot::BundleUses, 'Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass', 'Doctrine\ORM\Mapping\Driver\XmlDriver');
        $blueprint->slots->addPartial(Slot::BundleBuild, 'features/doctrine/partials/bundle_build.php.tpl');
        if ($blueprint->hasFeature(SemanticConfigFeature::NAME)) {
            $blueprint->slots->addPartial(Slot::BundleLoadExtension, 'features/doctrine/partials/bundle_load_extension.php.tpl');
        }

        // the tests use an in-memory SQLite database
        $blueprint->slots->addValue(ListSlot::TestKernelBundles, 'Doctrine\Bundle\DoctrineBundle\DoctrineBundle');
        $blueprint->slots->addValue(ListSlot::TestKernelUses, 'Composer\InstalledVersions');
        $blueprint->slots->addPartial(Slot::TestKernelConfigureContainer, 'features/doctrine/partials/test_kernel.php.tpl');

        $blueprint->slots->addPartial(Slot::ReadmeInstallationSteps, 'features/doctrine/partials/readme_step.md.tpl');
        $blueprint->slots->addPartial(Slot::DocsSections, 'features/doctrine/partials/docs.md.tpl');
    }

    /**
     * Name of the parameter naming the entity manager of the entities, e.g. "acme_blog.model_manager_name".
     */
    public static function managerParameter(BundleNaming $naming): string
    {
        return $naming->alias.'.model_manager_name';
    }

    /**
     * Name of the table of the entity, e.g. "acme_blog_greeting": prefixed with the bundle alias,
     * so it cannot collide with the tables of the application or of other bundles.
     */
    public static function tableName(BundleNaming $naming): string
    {
        return $naming->alias.'_greeting';
    }
}
