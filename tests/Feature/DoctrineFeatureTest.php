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

namespace Tito10047\BundleMaker\Tests\Feature;

use PHPUnit\Framework\TestCase;
use Tito10047\BundleMaker\Feature\DoctrineFeature;
use Tito10047\BundleMaker\Tests\Snapshot\Scenario;

final class DoctrineFeatureTest extends TestCase
{
    public function testDoctrineIsAnOptionalDependency(): void
    {
        $composer = (new Scenario('doctrine', features: ['doctrine']))->createBlueprint()->composer;

        self::assertArrayNotHasKey('doctrine/orm', $composer->getRequire());
        self::assertSame(DoctrineFeature::ORM_CONSTRAINT, $composer->getRequireDev()['doctrine/orm'] ?? null);
        self::assertSame(DoctrineFeature::DOCTRINE_BUNDLE_CONSTRAINT, $composer->getRequireDev()['doctrine/doctrine-bundle'] ?? null);
        self::assertSame(['doctrine/orm' => '<3.4'], $composer->getConflict());
        self::assertArrayHasKey('doctrine/doctrine-bundle', $composer->getSuggest());
    }

    public function testRegistersTheMappingWithACompilerPass(): void
    {
        $bundle = (new Scenario('doctrine', features: ['doctrine']))->render()['src/AcmeBlogBundle.php'];

        self::assertStringContainsString('public function build(ContainerBuilder $container): void', $bundle);
        self::assertStringContainsString("DoctrineOrmMappingsPass::createXmlMappingDriver(\n", $bundle);
        self::assertStringContainsString("['acme_blog.model_manager_name'],\n                'doctrine.default_entity_manager',", $bundle);
        self::assertStringNotContainsString('prependExtension', $bundle);
    }

    public function testTheEntityManagerIsAParameterWithoutSemanticConfiguration(): void
    {
        $files = (new Scenario('doctrine', features: ['doctrine']))->render();

        self::assertArrayNotHasKey('config/definition.php', $files);
        self::assertStringContainsString("parameters:\n    acme_blog.model_manager_name: customer", $files['docs/index.md']);
    }

    public function testTheEntityManagerIsAnOptionOfTheSemanticConfiguration(): void
    {
        $files = (new Scenario('doctrine', features: ['config', 'doctrine']))->render();

        self::assertStringContainsString("->scalarNode('model_manager_name')", $files['config/definition.php']);
        self::assertStringContainsString("->set('acme_blog.model_manager_name', \$config['model_manager_name'])", $files['src/AcmeBlogBundle.php']);
        self::assertStringContainsString("acme_blog:\n    model_manager_name: customer", $files['docs/index.md']);
    }
}
