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

namespace Tito10047\BundleMaker\Tests\Template;

use PHPUnit\Framework\TestCase;
use Tito10047\BundleMaker\Template\PhpImports;

final class PhpImportsTest extends TestCase
{
    public function testImportsAndSortsClasses(): void
    {
        $imports = new PhpImports(['Symfony\\Component\\HttpKernel\\Bundle\\AbstractBundle', 'Acme\\Foo', 'acme\\bar\\Baz', 'Acme2\\Bar', '\\Acme\\Foo']);

        self::assertSame("use acme\\bar\\Baz;\nuse Acme\\Foo;\nuse Acme2\\Bar;\nuse Symfony\\Component\\HttpKernel\\Bundle\\AbstractBundle;\n", $imports->render());
        self::assertSame('AbstractBundle', $imports->name('Symfony\\Component\\HttpKernel\\Bundle\\AbstractBundle'));
        self::assertSame('Foo', $imports->name('\\Acme\\Foo'));
        self::assertFalse($imports->isEmpty());
    }

    public function testDoesNotImportClassesOfTheSameNamespace(): void
    {
        $imports = new PhpImports(['Acme\\BlogBundle\\Greeter', 'Acme\\BlogBundle\\Sub\\Thing'], 'Acme\\BlogBundle');

        self::assertSame("use Acme\\BlogBundle\\Sub\\Thing;\n", $imports->render());
        self::assertSame('Greeter', $imports->name('Acme\\BlogBundle\\Greeter'));
        self::assertSame('Thing', $imports->name('Acme\\BlogBundle\\Sub\\Thing'));
    }

    public function testReferencesGlobalClassesByTheirFullyQualifiedName(): void
    {
        $global = new PhpImports(['ArrayObject']);
        $namespaced = new PhpImports(['ArrayObject'], 'App');

        self::assertSame('', $global->render());
        self::assertTrue($global->isEmpty());
        self::assertSame('ArrayObject', $global->name('ArrayObject'));
        self::assertSame('\\ArrayObject', $namespaced->name('ArrayObject'));
    }

    public function testFullyQualifiesConflictingShortNames(): void
    {
        $imports = new PhpImports(['B\\Formatter', 'A\\Formatter', 'C\\formatter']);

        self::assertSame("use A\\Formatter;\n", $imports->render());
        self::assertSame('Formatter', $imports->name('A\\Formatter'));
        self::assertSame('B\\Formatter', $imports->name('B\\Formatter'));
        self::assertSame('C\\formatter', $imports->name('C\\formatter'));
        self::assertSame('\\B\\Formatter', (new PhpImports(['B\\Formatter', 'A\\Formatter'], 'App'))->name('B\\Formatter'));
    }

    public function testSameNamespaceClassesReserveTheirShortName(): void
    {
        $imports = new PhpImports(['Acme\\Formatter', 'Other\\Formatter'], 'Acme');

        self::assertSame('', $imports->render());
        self::assertSame('Formatter', $imports->name('Acme\\Formatter'));
        self::assertSame('\\Other\\Formatter', $imports->name('Other\\Formatter'));
    }

    public function testImportsFunctions(): void
    {
        $imports = new PhpImports([], null, ['Foo\\service', 'Foo\\param', 'Foo\\param']);

        self::assertSame("use function Foo\\param;\nuse function Foo\\service;\n", $imports->render());
        self::assertFalse($imports->isEmpty());
    }

    public function testRejectsUnknownClasses(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The class "Acme\\Unknown" has not been registered in the imports.');

        (new PhpImports([]))->name('Acme\\Unknown');
    }
}
