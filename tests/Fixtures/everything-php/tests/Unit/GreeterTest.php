<?php

namespace Acme\EverythingPhpBundle\Tests\Unit;

use Acme\EverythingPhpBundle\Exception\InvalidArgumentException;
use Acme\EverythingPhpBundle\Greeter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class GreeterTest extends TestCase
{
    public function testGreetsTheGivenName(): void
    {
        $this->assertSame('Hello Jane!', (new Greeter('Hello'))->greet('Jane'));
    }

    public function testTrimsTheName(): void
    {
        $this->assertSame('Hi Jane!', (new Greeter('Hi'))->greet("  Jane\n"));
    }

    #[DataProvider('provideEmptyNames')]
    public function testRejectsAnEmptyName(string $name): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The name to greet must not be empty.');

        (new Greeter('Hello'))->greet($name);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideEmptyNames(): iterable
    {
        yield 'empty' => [''];
        yield 'whitespace only' => [" \t\n"];
    }
}
