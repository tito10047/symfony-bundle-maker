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
use Tito10047\BundleMaker\Feature\CommandFeature;
use Tito10047\BundleMaker\Feature\ControllersFeature;
use Tito10047\BundleMaker\Feature\EventListenerFeature;
use Tito10047\BundleMaker\Feature\FeatureGroup;
use Tito10047\BundleMaker\Feature\FeatureInterface;
use Tito10047\BundleMaker\Feature\FeatureRegistry;
use Tito10047\BundleMaker\Feature\FeatureSelection;
use Tito10047\BundleMaker\Feature\SemanticConfigFeature;
use Tito10047\BundleMaker\Feature\TranslationsFeature;
use Tito10047\BundleMaker\Feature\TwigFeature;
use Tito10047\BundleMaker\Tests\Support\TestFeature;

final class FeatureRegistryTest extends TestCase
{
    public function testIndexesFeaturesInRegistrationOrder(): void
    {
        $a = new TestFeature('a');
        $b = new TestFeature('b-c');
        $qa = new TestFeature('qa', FeatureGroup::QualityAssurance);
        $registry = new FeatureRegistry($a, $b, $qa);

        self::assertCount(3, $registry);
        self::assertSame(['a' => $a, 'b-c' => $b, 'qa' => $qa], iterator_to_array($registry));
        self::assertTrue($registry->has('b-c'));
        self::assertFalse($registry->has('d'));
        self::assertSame($b, $registry->get('b-c'));
        self::assertSame([$a, $b], $registry->getByGroup(FeatureGroup::Features));
        self::assertSame([$qa], $registry->getByGroup(FeatureGroup::QualityAssurance));
        self::assertSame([$a, $qa], $registry->getEnabled(new FeatureSelection(['a' => true, 'b-c' => false, 'qa' => true])));
    }

    public function testUnknownFeatures(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown feature "d". Available features: "a".');

        (new FeatureRegistry(new TestFeature('a')))->get('d');
    }

    public function testNamesAreKebabCase(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The feature name "Event_Listener"');

        new FeatureRegistry(new TestFeature('Event_Listener'));
    }

    public function testRequiredFeaturesAreRegisteredBefore(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('The feature "a" requires the feature "b", which must be registered before it.');

        new FeatureRegistry(new TestFeature('a', requiredFeatures: ['b']), new TestFeature('b'));
    }

    public function testNamesAreUnique(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('The feature "a" is registered twice');

        new FeatureRegistry(new TestFeature('a'), new TestFeature('a'));
    }

    public function testNamesMustNotCollideWithReservedVariables(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The feature name "config-format" (Tito10047\BundleMaker\Tests\Support\TestFeature) is reserved: its environment variable "BUNDLE_MAKER_CONFIG_FORMAT" is already used.');

        new FeatureRegistry(new TestFeature('config-format'));
    }

    public function testFeaturesAreRegisteredGroupByGroup(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('The feature "b" must be registered before the features of the "Quality assurance" group');

        new FeatureRegistry(new TestFeature('a', FeatureGroup::QualityAssurance), new TestFeature('b'));
    }

    public function testDefaultRegistry(): void
    {
        $registry = FeatureRegistry::createDefault();

        self::assertInstanceOf(SemanticConfigFeature::class, $registry->get(SemanticConfigFeature::NAME));
        self::assertInstanceOf(ControllersFeature::class, $registry->get(ControllersFeature::NAME));
        self::assertInstanceOf(TwigFeature::class, $registry->get(TwigFeature::NAME));
        self::assertInstanceOf(TranslationsFeature::class, $registry->get(TranslationsFeature::NAME));
        self::assertInstanceOf(CommandFeature::class, $registry->get(CommandFeature::NAME));
        self::assertInstanceOf(EventListenerFeature::class, $registry->get(EventListenerFeature::NAME));
        // the order of the questions and of the contributions; a feature reading the answers of other features
        // comes after them (twig defaults to the answer of controllers)
        self::assertSame(
            [
                SemanticConfigFeature::NAME,
                ControllersFeature::NAME,
                TwigFeature::NAME,
                TranslationsFeature::NAME,
                CommandFeature::NAME,
                EventListenerFeature::NAME,
            ],
            array_map(static fn (FeatureInterface $feature): string => $feature->getName(), $registry->getByGroup(FeatureGroup::Features)),
        );
    }
}
