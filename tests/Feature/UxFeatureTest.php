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
use Tito10047\BundleMaker\Feature\UxFeature;
use Tito10047\BundleMaker\Naming\BundleNaming;
use Tito10047\BundleMaker\Tests\Snapshot\Scenario;

final class UxFeatureTest extends TestCase
{
    public function testExposesTheControllersToAssetMapperWhenTheApplicationUsesIt(): void
    {
        $files = (new Scenario('ux', features: ['ux']))->render();

        self::assertStringContainsString("if (interface_exists(AssetMapperInterface::class) && \$builder->hasExtension('framework')) {", $files['src/AcmeBlogBundle.php']);
        self::assertStringNotContainsString('"version"', $files['assets/package.json']);
    }

    public function testNamesTheControllerAfterThePackage(): void
    {
        $naming = new BundleNaming('my-vendor/foo_bar-bundle');

        self::assertSame('@my-vendor/foo_bar-bundle', UxFeature::packageName($naming));
        self::assertSame('my-vendor/foo_bar-bundle/hello', UxFeature::controllerName($naming));
        self::assertSame('my-vendor--foo-bar-bundle--hello', UxFeature::controllerIdentifier($naming));
    }
}
