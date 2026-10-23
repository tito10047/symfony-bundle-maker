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
use Tito10047\BundleMaker\Tests\Snapshot\Scenario;

final class RectorFeatureTest extends TestCase
{
    public function testIsALocalToolTargetingTheLowestVersions(): void
    {
        $scenario = new Scenario('rector', features: ['rector']);
        $composer = $scenario->createBlueprint()->composer;

        self::assertTrue($composer->hasScript('rector'));
        self::assertNotContains('rector', $composer->getQaScriptNames());
        self::assertStringContainsString("->withPhpSets(php82: true)\n    ->withComposerBased(phpunit: true, symfony: true)", $scenario->render()['rector.php']);
    }

    public function testAppliesTheSetsOfTheRequiredPackages(): void
    {
        $rector = (new Scenario('rector', features: ['controllers', 'twig', 'doctrine', 'rector']))->render()['rector.php'];

        self::assertStringContainsString('->withComposerBased(twig: true, doctrine: true, phpunit: true, symfony: true)', $rector);
    }
}
