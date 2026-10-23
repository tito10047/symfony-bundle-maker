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

final class SemanticConfigFeatureTest extends TestCase
{
    public function testPassesTheGreetingToTheGreeter(): void
    {
        $scenario = new Scenario('config', features: ['config']);
        $files = $scenario->render();

        self::assertStringContainsString("->get('acme_blog.greeter')\n                ->arg(0, \$config['greeting'])", $files['src/AcmeBlogBundle.php']);
        self::assertSame([], $scenario->createBlueprint()->services->getParameters());
        self::assertStringContainsString('private readonly array $extensionConfigs = []', $files['tests/App/TestKernel.php']);
    }

    public function testTheGreetingIsAParameterWithoutSemanticConfiguration(): void
    {
        $scenario = new Scenario('config');
        $files = $scenario->render();

        self::assertSame(['acme_blog.greeting' => 'Hello'], $scenario->createBlueprint()->services->getParameters());
        self::assertStringNotContainsString('extensionConfigs', $files['tests/App/TestKernel.php']);
        self::assertStringContainsString("parameters:\n    acme_blog.greeting: 'Hi'", $files['docs/index.md']);
    }
}
