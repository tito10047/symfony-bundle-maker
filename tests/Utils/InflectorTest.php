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

namespace Tito10047\BundleMaker\Tests\Utils;

use PHPUnit\Framework\TestCase;
use Tito10047\BundleMaker\Utils\Inflector;

class InflectorTest extends TestCase
{
    public function testClassifyBundleName(): void
    {
        self::assertSame('AcmeBundle', Inflector::classify('acme-bundle'));
    }

    public function testNamespacefyPackageName(): void
    {
        self::assertSame('Acme\\AcmeBundle', Inflector::namespacefy('acme/acme-bundle'));
    }

    public function testClassNamePackageName(): void
    {
        self::assertSame('AcmeBundle', Inflector::className('acme/acme-bundle'));
    }

    public function testVendoryPackageName(): void
    {
        self::assertSame('acme_foo', Inflector::vendory('acme/foo-bundle'));
    }

    public function testFileName(): void
    {
        self::assertSame('acme-bundle', Inflector::fileName('acme/acme-bundle'));
    }
}
