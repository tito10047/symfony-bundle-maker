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
use Tito10047\BundleMaker\Feature\FeatureSelection;
use Tito10047\BundleMaker\Tests\Support\TestFeature;

final class FeatureSelectionTest extends TestCase
{
    public function testIsImmutable(): void
    {
        $empty = new FeatureSelection();
        $selection = $empty->with('twig', true)->with('doctrine', false)->with('ux', true);

        self::assertFalse($empty->has('twig'));
        self::assertTrue($selection->has('doctrine'));
        self::assertTrue($selection->isEnabled('twig'));
        self::assertFalse($selection->isEnabled('doctrine'));
        self::assertEquals(new FeatureSelection(['twig' => true, 'doctrine' => false, 'ux' => true]), $selection);
    }

    public function testOverridingAnAnswerKeepsItsPosition(): void
    {
        $selection = (new FeatureSelection(['a' => true, 'b' => true]))->with('a', false);

        self::assertEquals(new FeatureSelection(['a' => false, 'b' => true]), $selection);
    }

    public function testUnansweredFeaturesAreErrors(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('The feature "twig" has not been answered (yet). Answered features: "controllers".');

        (new FeatureSelection(['controllers' => true]))->isEnabled('twig');
    }

    public function testAllowsAFeatureWhenTheFeaturesItRequiresAreEnabled(): void
    {
        $feature = new TestFeature('c', requiredFeatures: ['a', 'b']);

        self::assertTrue((new FeatureSelection(['a' => true, 'b' => true]))->allowsFeature($feature));
        self::assertFalse((new FeatureSelection(['a' => true, 'b' => false]))->allowsFeature($feature));
        self::assertTrue((new FeatureSelection())->allowsFeature(new TestFeature('d')));
    }
}
