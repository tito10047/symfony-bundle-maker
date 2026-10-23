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

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;
use Tito10047\BundleMaker\Blueprint\ServiceArgument;
use Tito10047\BundleMaker\Options\ConfigFormat;
use Tito10047\BundleMaker\Template\TemplateHelper;

final class TemplateHelperTest extends TestCase
{
    private TemplateHelper $helper;

    protected function setUp(): void
    {
        $this->helper = new TemplateHelper();
    }

    /**
     * @return iterable<string, array{string|int|float|bool|array<array-key, string|int|float|bool|null>|null, string, string}>
     */
    public static function provideValues(): iterable
    {
        yield 'null' => [null, 'null', 'null'];
        yield 'true' => [true, 'true', 'true'];
        yield 'false' => [false, 'false', 'false'];
        yield 'int' => [42, '42', '42'];
        yield 'negative int' => [-1, '-1', '-1'];
        yield 'float' => [1.5, '1.5', '1.5'];
        yield 'string' => ['hello', "'hello'", "'hello'"];
        yield 'quote' => ["it's", "'it\\'s'", "'it''s'"];
        yield 'backslash' => ['Acme\\Foo', "'Acme\\\\Foo'", "'Acme\\Foo'"];
        yield 'empty string' => ['', "''", "''"];
        yield 'list' => [['a', 1, true, null], "['a', 1, true, null]", "['a', 1, true, null]"];
        yield 'empty list' => [[], '[]', '[]'];
        yield 'map' => [['name' => 'x', 'priority' => 10], "['name' => 'x', 'priority' => 10]", "{ name: 'x', priority: 10 }"];
    }

    /**
     * @param string|int|float|bool|array<array-key, string|int|float|bool|null>|null $value
     */
    #[DataProvider('provideValues')]
    public function testEncodesValues(string|int|float|bool|array|null $value, string $php, string $yaml): void
    {
        self::assertSame($php, $this->helper->php($value));
        self::assertSame($yaml, $this->helper->yaml($value));

        self::assertSame($value, eval('return '.$php.';'));
        self::assertSame(['key' => $value], Yaml::parse('key: '.$yaml));
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function provideYamlKeys(): iterable
    {
        yield 'service id' => ['acme_blog.greeter', 'acme_blog.greeter'];
        yield 'internal service id' => ['.acme_blog.greeter', '.acme_blog.greeter'];
        yield 'class' => ['Acme\\BlogBundle\\Greeter', 'Acme\\BlogBundle\\Greeter'];
        yield 'dash' => ['foo-bar', 'foo-bar'];
        yield 'space' => ['foo bar', "'foo bar'"];
        yield 'leading digit' => ['1foo', "'1foo'"];
        yield 'null keyword' => ['null', "'null'"];
        yield 'true keyword' => ['True', "'True'"];
        yield 'colon' => ['a:b', "'a:b'"];
    }

    #[DataProvider('provideYamlKeys')]
    public function testEncodesYamlKeys(string $key, string $expected): void
    {
        self::assertSame($expected, $this->helper->yamlKey($key));
        self::assertSame([$key => 1], Yaml::parse($expected.': 1'));
    }

    public function testEscapesXml(): void
    {
        self::assertSame('&lt;a href=&quot;x&quot;&gt;Tom &amp; Jerry&apos;s&lt;/a&gt;', $this->helper->xml('<a href="x">Tom & Jerry\'s</a>'));
    }

    /**
     * @return iterable<string, array{ConfigFormat, string|int|bool|list<string>|null, string}>
     */
    public static function provideConfigValues(): iterable
    {
        yield 'php percent' => [ConfigFormat::Php, '100%', "'100%%'"];
        yield 'php at sign is literal' => [ConfigFormat::Php, '@foo', "'@foo'"];
        yield 'php-array at sign' => [ConfigFormat::PhpArray, '@foo', "'@@foo'"];
        yield 'yaml at sign' => [ConfigFormat::Yaml, '@foo', "'@@foo'"];
        yield 'yaml percent' => [ConfigFormat::Yaml, '%foo%', "'%%foo%%'"];
        yield 'yaml list' => [ConfigFormat::Yaml, ['@a', 'b%'], "['@@a', 'b%%']"];
        yield 'php list' => [ConfigFormat::Php, ['@a', 'b%'], "['@a', 'b%%']"];
        yield 'non string' => [ConfigFormat::Yaml, 42, '42'];
        yield 'null' => [ConfigFormat::PhpArray, null, 'null'];
        yield 'at sign not leading' => [ConfigFormat::Yaml, 'a@b', "'a@b'"];
    }

    /**
     * @param string|int|bool|list<string>|null $value
     */
    #[DataProvider('provideConfigValues')]
    public function testEncodesConfigValues(ConfigFormat $format, string|int|bool|array|null $value, string $expected): void
    {
        self::assertSame($expected, $this->helper->configValue($value, $format));
    }

    /**
     * @return iterable<string, array{ServiceArgument, ConfigFormat, string}>
     */
    public static function provideServiceArguments(): iterable
    {
        yield 'php service' => [ServiceArgument::service('acme.foo'), ConfigFormat::Php, "service('acme.foo')"];
        yield 'php-array service' => [ServiceArgument::service('acme.foo'), ConfigFormat::PhpArray, "'@acme.foo'"];
        yield 'yaml service' => [ServiceArgument::service('acme.foo'), ConfigFormat::Yaml, "'@acme.foo'"];
        yield 'php parameter' => [ServiceArgument::parameter('acme.bar'), ConfigFormat::Php, "param('acme.bar')"];
        yield 'php-array parameter' => [ServiceArgument::parameter('acme.bar'), ConfigFormat::PhpArray, "'%acme.bar%'"];
        yield 'yaml parameter' => [ServiceArgument::parameter('acme.bar'), ConfigFormat::Yaml, "'%acme.bar%'"];
    }

    #[DataProvider('provideServiceArguments')]
    public function testEncodesServiceArguments(ServiceArgument $argument, ConfigFormat $format, string $expected): void
    {
        self::assertSame($expected, $this->helper->serviceArgument($argument, $format));
    }

    public function testIndentsNonEmptyLines(): void
    {
        self::assertSame("    a\n\n      b\n", $this->helper->indent("a\n\n  b\n", 4));
        self::assertSame("a\n", $this->helper->indent("a\n", 0));
        self::assertSame('', $this->helper->indent('', 4));
    }

    public function testCreatesImports(): void
    {
        $imports = $this->helper->imports(['Acme\\Foo'], 'App', ['Acme\\bar']);

        self::assertSame("use Acme\\Foo;\n\nuse function Acme\\bar;\n", $imports->render());
    }
}
