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

final class TranslationsFeatureTest extends TestCase
{
    public function testTranslatesTheMessagesOfTheTemplate(): void
    {
        $files = (new Scenario('translations', features: ['twig', 'translations', 'ux']))->render();

        self::assertArrayHasKey('translations/AcmeBlog.en.xlf', $files);
        self::assertStringContainsString("{{ 'Welcome!'|trans({}, 'AcmeBlog') }}", $files['templates/hello.html.twig']);
        self::assertStringContainsString("{{ 'Close'|trans({}, 'AcmeBlog') }}", $files['templates/hello.html.twig']);
    }

    public function testDocumentsTheTranslatorWithoutTwig(): void
    {
        $files = (new Scenario('translations', features: ['translations']))->render();

        self::assertStringNotContainsString('<source>Close</source>', $files['translations/AcmeBlog.en.xlf']);
        self::assertStringContainsString("\$translator->trans('Welcome!', [], 'AcmeBlog');", $files['docs/index.md']);
    }
}
