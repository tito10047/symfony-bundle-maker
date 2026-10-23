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
use Tito10047\BundleMaker\Options\Author;
use Tito10047\BundleMaker\Options\ConfigFormat;
use Tito10047\BundleMaker\Options\License;

final class EnumsAndAuthorTest extends TestCase
{
    public function testConfigFormats(): void
    {
        self::assertSame('services.php', ConfigFormat::Php->getFileName('services'));
        self::assertSame('routes.php', ConfigFormat::PhpArray->getFileName('routes'));
        self::assertSame('services.yaml', ConfigFormat::Yaml->getFileName('services'));
        self::assertSame('yaml', ConfigFormat::Yaml->getFileExtension());

        foreach (ConfigFormat::cases() as $format) {
            self::assertNotSame('', $format->getDescription());
        }
    }

    public function testLicenses(): void
    {
        self::assertSame(License::Mit, License::fromIdentifier('mit'));
        self::assertSame(License::Apache2, License::fromIdentifier('APACHE-2.0'));
        self::assertNull(License::fromIdentifier('WTFPL'));
        self::assertSame('MIT License', License::Mit->getName());
        self::assertSame('GNU Lesser General Public License v3.0 or later', License::Lgpl3OrLater->getName());
        foreach (License::cases() as $license) {
            self::assertNotSame('', $license->getName());
        }
        self::assertSame(['MIT', 'Apache-2.0', 'BSD-2-Clause', 'BSD-3-Clause', 'GPL-3.0-or-later', 'LGPL-3.0-or-later', 'MPL-2.0', 'proprietary'], License::identifiers());
    }

    public function testAuthor(): void
    {
        $author = new Author('Jane', 'jane@example.com');

        self::assertSame('Jane', $author->name);
        self::assertSame('jane@example.com', $author->email);
        self::assertNull((new Author('Jane'))->email);
    }

    public function testAuthorNameIsRequired(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The author name must not be empty.');

        new Author(' ');
    }

    public function testAuthorEmailMustBeValid(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The author email "nope" is invalid.');

        new Author('Jane', 'nope');
    }
}
