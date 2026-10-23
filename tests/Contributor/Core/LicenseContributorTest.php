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

namespace Tito10047\BundleMaker\Tests\Contributor\Core;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Tito10047\BundleMaker\Blueprint\BundleBlueprint;
use Tito10047\BundleMaker\Blueprint\FileTemplate;
use Tito10047\BundleMaker\Contributor\Core\LicenseContributor;
use Tito10047\BundleMaker\Contributor\CoreContributors;
use Tito10047\BundleMaker\Feature\FeatureRegistry;
use Tito10047\BundleMaker\Generator\BundleGenerator;
use Tito10047\BundleMaker\Options\License;
use Tito10047\BundleMaker\Tests\Support\OptionsFactory;

final class LicenseContributorTest extends TestCase
{
    public function testContributesTheLicenseFile(): void
    {
        $blueprint = new BundleBlueprint(OptionsFactory::create(license: License::Apache2));

        (new LicenseContributor(2030))->contribute($blueprint);

        self::assertEquals(['LICENSE' => new FileTemplate('core/licenses/Apache-2.0.tpl', 'LICENSE', ['year' => 2030])], $blueprint->getFiles());
    }

    /**
     * @return iterable<string, array{License, non-empty-string, string|null}>
     */
    public static function provideLicenses(): iterable
    {
        yield 'MIT' => [License::Mit, 'MIT License', 'Copyright (c) 2030 Jane Doe'];
        yield 'Apache-2.0' => [License::Apache2, 'Apache License', null];
        yield 'BSD-2-Clause' => [License::Bsd2Clause, 'BSD 2-Clause License', 'Copyright (c) 2030, Jane Doe'];
        yield 'BSD-3-Clause' => [License::Bsd3Clause, 'BSD 3-Clause License', 'Copyright (c) 2030, Jane Doe'];
        yield 'GPL-3.0-or-later' => [License::Gpl3OrLater, 'GNU GENERAL PUBLIC LICENSE', null];
        yield 'LGPL-3.0-or-later' => [License::Lgpl3OrLater, 'GNU LESSER GENERAL PUBLIC LICENSE', null];
        yield 'MPL-2.0' => [License::Mpl2, 'Mozilla Public License Version 2.0', null];
        yield 'proprietary' => [License::Proprietary, 'Copyright (c) 2030 Jane Doe', 'All rights reserved.'];
    }

    /**
     * @param non-empty-string $title
     */
    #[DataProvider('provideLicenses')]
    public function testRendersTheFullLicenseText(License $license, string $title, ?string $copyright): void
    {
        $text = self::render($license);

        self::assertStringStartsWith($title, ltrim($text));
        self::assertStringEndsWith("\n", $text);
        self::assertStringEndsNotWith("\n\n", $text);
        self::assertStringNotContainsString('<?', $text);
        if (null !== $copyright) {
            self::assertStringContainsString("\n\n".$copyright."\n\n", $text);
        }
    }

    public function testEveryLicenseHasATemplate(): void
    {
        foreach (License::cases() as $license) {
            self::assertFileExists(\dirname(__DIR__, 3).'/templates/'.LicenseContributor::getTemplate($license));
        }
    }

    public function testTheLgplIsFollowedByTheGpl(): void
    {
        $text = self::render(License::Lgpl3OrLater);

        self::assertStringContainsString('This version of the GNU Lesser General Public License incorporates', $text);
        self::assertStringContainsString(self::render(License::Gpl3OrLater), $text);
    }

    private static function render(License $license): string
    {
        $generator = new BundleGenerator(new CoreContributors(new LicenseContributor(2030)), new FeatureRegistry());

        return $generator->render($generator->createBlueprint(OptionsFactory::create(license: $license)))['LICENSE'];
    }
}
