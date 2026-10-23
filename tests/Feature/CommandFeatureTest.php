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
use Tito10047\BundleMaker\Blueprint\ServiceTag;
use Tito10047\BundleMaker\Feature\CommandFeature;
use Tito10047\BundleMaker\Naming\BundleNaming;
use Tito10047\BundleMaker\Tests\Snapshot\Scenario;

final class CommandFeatureTest extends TestCase
{
    public function testRegistersTheCommandWithABareTag(): void
    {
        $services = (new Scenario('command', features: ['command']))->createBlueprint()->services->getServices();

        // the console reads the name and the description of #[AsCommand]
        self::assertEquals([new ServiceTag('console.command')], $services['acme_blog.command.hello']->tags);
    }

    public function testNamesTheCommandAfterTheBundle(): void
    {
        self::assertSame('acme-blog:hello', CommandFeature::commandName(new BundleNaming('acme/blog-bundle')));
        self::assertSame('my-vendor-foo-bar:hello', CommandFeature::commandName(new BundleNaming('my-vendor/foo-bar-bundle')));
    }
}
