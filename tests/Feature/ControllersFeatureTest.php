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

final class ControllersFeatureTest extends TestCase
{
    public function testRequiresFrameworkBundleWhichRegistersTheControllers(): void
    {
        $composer = (new Scenario('controllers', features: ['controllers']))->createBlueprint()->composer;

        self::assertSame(Constraint::SYMFONY, $composer->getRequire()['symfony/framework-bundle'] ?? null);
    }

    public function testTheRouteAcceptsAnyNameWithoutSlash(): void
    {
        $route = (new Scenario('controllers', features: ['controllers']))->createBlueprint()->routes->getRoutes()['acme_blog_hello'];

        self::assertSame('/hello/{name}', $route->path);
        self::assertSame([], $route->requirements);
    }

    public function testRendersPlainTextWithoutTwig(): void
    {
        $files = (new Scenario('controllers', features: ['controllers']))->render();

        self::assertStringContainsString("'Content-Type' => 'text/plain; charset=UTF-8'", $files['src/Controller/HelloController.php']);
        self::assertStringNotContainsString('Twig', $files['src/Controller/HelloController.php']);
        self::assertStringContainsString("assertResponseHeaderSame('Content-Type', 'text/plain; charset=UTF-8')", $files['tests/Functional/HelloControllerTest.php']);
        self::assertStringContainsString('The page is plain text.', $files['docs/index.md']);
    }

    public function testRendersTheTemplateWithTwig(): void
    {
        $files = (new Scenario('controllers', features: ['controllers', 'twig']))->render();

        self::assertStringContainsString("\$this->twig->render('@AcmeBlog/hello.html.twig'", $files['src/Controller/HelloController.php']);
    }
}
