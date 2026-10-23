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

namespace Tito10047\BundleMaker\Tests\Options;

use PHPUnit\Framework\TestCase;
use Tito10047\BundleMaker\Options\ConfigFormat;
use Tito10047\BundleMaker\Options\License;
use Tito10047\BundleMaker\Options\OptionNormalizer;

final class OptionNormalizerTest extends TestCase
{
    private OptionNormalizer $normalizer;

    protected function setUp(): void
    {
        $this->normalizer = new OptionNormalizer();
    }

    public function testNormalizesValidValues(): void
    {
        self::assertSame('acme/blog-bundle', $this->normalizer->packageName(' acme/blog-bundle ')->packageName);
        self::assertSame('A bundle', $this->normalizer->description(' A bundle '));
        self::assertSame('Jane Doe', $this->normalizer->authorName(' Jane Doe '));
        self::assertSame('jane@example.com', $this->normalizer->authorEmail(' jane@example.com '));
        self::assertNull($this->normalizer->authorEmail(''));
        self::assertNull($this->normalizer->authorEmail(null));
        self::assertNull($this->normalizer->authorEmail('None'));
        self::assertSame(License::Mpl2, $this->normalizer->license('mpl-2.0'));
        self::assertSame(ConfigFormat::PhpArray, $this->normalizer->configFormat(' PHP-Array '));
    }

    public function testRejectsInvalidPackageNames(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The package name "" is invalid');

        $this->normalizer->packageName(null);
    }

    public function testRejectsEmptyDescriptions(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The description must not be empty.');

        $this->normalizer->description('  ');
    }

    public function testRejectsEmptyAuthorNames(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The author name must not be empty.');

        $this->normalizer->authorName(null);
    }

    public function testRejectsInvalidEmails(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('"jane@" is not a valid email address.');

        $this->normalizer->authorEmail('jane@');
    }

    public function testRejectsUnsupportedLicenses(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('"WTFPL" is not a supported license. Supported licenses: MIT, Apache-2.0');

        $this->normalizer->license('WTFPL');
    }

    public function testRejectsUnsupportedConfigFormats(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('"xml" is not a supported configuration format. Supported formats: php, php-array, yaml.');

        $this->normalizer->configFormat('xml');
    }
}
