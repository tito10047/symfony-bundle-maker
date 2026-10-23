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

final class PhpStanFeatureTest extends TestCase
{
    public function testAnalysesAtLevel8WithTheSymfonyAndPhpUnitExtensions(): void
    {
        $scenario = new Scenario('phpstan', features: ['phpstan']);

        self::assertStringContainsString("    level: 8\n", $scenario->render()['phpstan.dist.neon']);
        self::assertSame([
            'phpstan/extension-installer',
            'phpstan/phpstan',
            'phpstan/phpstan-phpunit',
            'phpstan/phpstan-symfony',
        ], array_values(array_filter(array_keys($scenario->createBlueprint()->composer->getRequireDev()), static fn (string $package): bool => str_starts_with($package, 'phpstan/'))));
    }
}
