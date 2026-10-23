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

final class PhpCsFixerFeatureTest extends TestCase
{
    public function testFollowsTheCodingStandardsOfSymfony(): void
    {
        $blueprint = (new Scenario('cs', features: ['cs-fixer']))->createBlueprint();
        $config = (new Scenario('cs', features: ['cs-fixer']))->render()['.php-cs-fixer.dist.php'];

        self::assertStringContainsString("->setRules([\n        '@Symfony' => true,\n        '@Symfony:risky' => true,\n    ])", $config);
        self::assertSame(['test', 'cs'], $blueprint->composer->getQaScriptNames());
    }
}
