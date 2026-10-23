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
use Tito10047\BundleMaker\Feature\TwigFeature;
use Tito10047\BundleMaker\Tests\Snapshot\Scenario;

final class TwigFeatureTest extends TestCase
{
    public function testIsEnabledByDefaultWithControllers(): void
    {
        self::assertTrue((new TwigFeature())->isEnabledByDefault(new FeatureSelection(['controllers' => true])));
        self::assertFalse((new TwigFeature())->isEnabledByDefault(new FeatureSelection(['controllers' => false])));
    }

    public function testTheApplicationRendersTheTemplatesWithoutControllers(): void
    {
        $scenario = new Scenario('twig', features: ['twig', 'ux']);
        $composer = $scenario->createBlueprint()->composer;

        self::assertArrayHasKey('symfony/twig-bundle', $composer->getRequireDev());
        self::assertArrayHasKey('symfony/stimulus-bundle', $composer->getRequireDev());
        self::assertArrayNotHasKey('twig/twig', $composer->getRequire() + $composer->getRequireDev());
        self::assertStringContainsString("render it in your templates with {{ include('@AcmeBlog/hello.html.twig'", $scenario->render()['templates/hello.html.twig']);
    }

    public function testTheBundleRendersTheTemplatesWithControllers(): void
    {
        $composer = (new Scenario('twig', features: ['controllers', 'twig', 'ux']))->createBlueprint()->composer;

        self::assertArrayHasKey('symfony/twig-bundle', $composer->getRequire());
        self::assertArrayHasKey('symfony/stimulus-bundle', $composer->getRequire());
        self::assertSame(TwigFeature::TWIG_CONSTRAINT, $composer->getRequire()['twig/twig'] ?? null);
    }
}
