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

namespace Tito10047\BundleMaker\Tests\Blueprint;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Tito10047\BundleMaker\Blueprint\ArgumentType;
use Tito10047\BundleMaker\Blueprint\FileTemplate;
use Tito10047\BundleMaker\Blueprint\Identifiers;
use Tito10047\BundleMaker\Blueprint\RouteDefinition;
use Tito10047\BundleMaker\Blueprint\ServiceArgument;
use Tito10047\BundleMaker\Blueprint\ServiceDefinition;
use Tito10047\BundleMaker\Blueprint\ServiceTag;

final class ValueObjectsTest extends TestCase
{
    public function testServiceArguments(): void
    {
        $service = ServiceArgument::service('acme.foo');
        $parameter = ServiceArgument::parameter('acme.bar');

        self::assertSame(ArgumentType::Service, $service->type);
        self::assertSame('acme.foo', $service->getReference());
        self::assertSame(ArgumentType::Parameter, $parameter->type);
        self::assertSame('acme.bar', $parameter->getReference());
    }

    public function testServiceTagRejectsAnEmptyName(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new ServiceTag('');
    }

    public function testServiceTagRejectsANameAttribute(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The tag "foo" must not have a "name" attribute');

        new ServiceTag('foo', ['name' => 'bar']);
    }

    public function testServiceDefinitionRejectsInvalidClasses(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The class "\\Acme\\Foo" of service "acme.foo" is not a valid fully-qualified class name');

        new ServiceDefinition('acme.foo', '\\Acme\\Foo');
    }

    public function testRouteDefinition(): void
    {
        $route = new RouteDefinition('acme_hello', '/hello/{name}', 'acme.controller', ['GET'], ['name' => 'World'], ['name' => '\\w+']);

        self::assertSame(['GET'], $route->methods);
        self::assertSame(['name' => 'World'], $route->defaults);
        self::assertSame(['name' => '\\w+'], $route->requirements);
    }

    public function testRouteDefaultsMustBePlaceholders(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The route "acme_hello" has a default value or a requirement for "id", which is not a placeholder of its path "/hello/{name}".');

        new RouteDefinition('acme_hello', '/hello/{name}', 'acme.controller', defaults: ['id' => 1]);
    }

    public function testRouteRequirementsMustBePlaceholders(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The route "acme_hello" has a default value or a requirement for "id", which is not a placeholder of its path "/hello".');

        new RouteDefinition('acme_hello', '/hello', 'acme.controller', requirements: ['id' => '\\d+']);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideInvalidRequirements(): iterable
    {
        yield 'empty' => [''];
        yield 'invalid' => ['[a-z'];
    }

    #[DataProvider('provideInvalidRequirements')]
    public function testRouteRequirementsMustBeValidRegularExpressions(string $requirement): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(\sprintf('The requirement "%s" of the "name" placeholder of route "acme_hello" is not a valid regular expression.', $requirement));

        new RouteDefinition('acme_hello', '/hello/{name}', 'acme.controller', requirements: ['name' => $requirement]);
    }

    public function testRoutePathMustBeAbsolute(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The path "hello" of route "acme_hello" must start with "/".');

        new RouteDefinition('acme_hello', 'hello', 'acme.controller');
    }

    public function testRouteMethodsMustBeUppercase(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The HTTP method "get" of route "acme_hello" must be uppercase.');

        new RouteDefinition('acme_hello', '/', 'acme.controller', ['get']);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideInvalidDestinations(): iterable
    {
        yield 'empty' => [''];
        yield 'absolute' => ['/etc/passwd'];
        yield 'directory' => ['src/'];
        yield 'parent' => ['src/../../x'];
        yield 'backslash' => ['src\\Foo.php'];
    }

    #[DataProvider('provideInvalidDestinations')]
    public function testFileTemplateRejectsInvalidDestinations(string $destination): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('must be a file path relative to the bundle root');

        new FileTemplate('a.tpl', $destination);
    }

    /**
     * @return iterable<array{string, bool}>
     */
    public static function provideClassNames(): iterable
    {
        yield ['Acme\\Foo', true];
        yield ['Foo', true];
        yield ['Acme\\Foo_Bar\\Baz2', true];
        yield ['\\Acme\\Foo', false];
        yield ['Acme\\', false];
        yield ['Acme\\2Foo', false];
        yield ['acme.foo', false];
        yield ['', false];
    }

    #[DataProvider('provideClassNames')]
    public function testIsClassName(string $class, bool $expected): void
    {
        self::assertSame($expected, Identifiers::isClassName($class));
    }

    public function testIsNamespacedClassName(): void
    {
        self::assertTrue(Identifiers::isNamespacedClassName('Acme\\Foo'));
        self::assertFalse(Identifiers::isNamespacedClassName('Foo'));
        self::assertFalse(Identifiers::isNamespacedClassName('acme.foo\\bar'));
    }

    /**
     * @return iterable<array{string, bool}>
     */
    public static function provideServiceIds(): iterable
    {
        yield ['acme_blog.greeter', true];
        yield ['.acme_blog.greeter', true];
        yield ['acme_blog.controller.hello', true];
        yield ['acme_blog', false];
        yield ['acme_blog.', false];
        yield ['acme_blogger.greeter', false];
        yield ['other.greeter', false];
        yield ['acme_blog.Greeter', false];
        yield ['..acme_blog.greeter', false];
    }

    #[DataProvider('provideServiceIds')]
    public function testIsPrefixedServiceId(string $id, bool $expected): void
    {
        self::assertSame($expected, Identifiers::isPrefixedServiceId($id, 'acme_blog'));
    }
}
