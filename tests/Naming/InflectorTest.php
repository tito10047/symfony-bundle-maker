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

namespace Tito10047\BundleMaker\Tests\Naming;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;
use Tito10047\BundleMaker\Naming\Inflector;

final class InflectorTest extends TestCase
{
    /**
     * @return iterable<string, array{string, string}>
     */
    public static function provideClassifyCases(): iterable
    {
        yield 'single word' => ['blog', 'Blog'];
        yield 'dashes' => ['foo-bar-bundle', 'FooBarBundle'];
        yield 'double dashes' => ['foo--bar', 'FooBar'];
        yield 'underscores' => ['foo_bar', 'FooBar'];
        yield 'dots' => ['foo.bar', 'FooBar'];
        yield 'mixed separators' => ['a-b_c.d', 'ABCD'];
        yield 'digits' => ['blog2-bundle', 'Blog2Bundle'];
        yield 'digit segment' => ['foo-2fa', 'Foo2fa'];
        yield 'uppercase input' => ['FOO-bar', 'FooBar'];
    }

    #[DataProvider('provideClassifyCases')]
    public function testClassify(string $word, string $expected): void
    {
        self::assertSame($expected, Inflector::classify($word));
    }

    /**
     * @return iterable<array{string}>
     */
    public static function provideUnderscoreCases(): iterable
    {
        yield ['AcmeBlog'];
        yield ['MyVendorFooBar'];
        yield ['AcmeBlog2'];
        yield ['Acme2fa'];
        yield ['AcmeFoo2Bar'];
        yield ['HTTPClient'];
        yield ['AcmeHTTPClient'];
        yield ['Acme'];
        yield ['already_underscored'];
        yield ['A'];
        yield ['ABC'];
    }

    #[DataProvider('provideUnderscoreCases')]
    public function testUnderscoreMirrorsSymfonyContainer(string $id): void
    {
        self::assertSame(Container::underscore($id), Inflector::underscore($id));
    }
}
