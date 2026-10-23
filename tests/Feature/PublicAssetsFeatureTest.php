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
use Tito10047\BundleMaker\Blueprint\Constraint;
use Tito10047\BundleMaker\Tests\Snapshot\Scenario;

final class PublicAssetsFeatureTest extends TestCase
{
    public function testRequiresFrameworkBundleWhichInstallsTheAssets(): void
    {
        $composer = (new Scenario('assets', features: ['public-assets']))->createBlueprint()->composer;

        self::assertSame(Constraint::SYMFONY, $composer->getRequire()['symfony/framework-bundle'] ?? null);
        self::assertArrayNotHasKey('symfony/asset', $composer->getRequire() + $composer->getRequireDev());
    }

    public function testApplicationsLinkTheStylesheetInTheirLayout(): void
    {
        $files = (new Scenario('assets', features: ['twig', 'public-assets']))->render();

        self::assertStringNotContainsString('<link', $files['templates/hello.html.twig']);
        self::assertStringContainsString("{% block stylesheets %}\n    <link rel=\"stylesheet\" href=\"{{ asset('bundles/acmeblog/css/acme-blog.css') }}\">", $files['docs/index.md']);
    }
}
