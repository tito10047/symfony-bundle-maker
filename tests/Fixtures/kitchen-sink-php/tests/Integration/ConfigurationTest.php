<?php

namespace Acme\PhpKitchenSinkBundle\Tests\Integration;

use Acme\PhpKitchenSinkBundle\GreeterInterface;
use Acme\PhpKitchenSinkBundle\Tests\App\TestKernel;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * Boots the test application with the given configuration of the bundle.
 */
final class ConfigurationTest extends KernelTestCase
{
    public function testGreetingIsPassedToTheGreeter(): void
    {
        self::bootKernel(['extension_configs' => ['acme_php_kitchen_sink' => ['greeting' => 'Hi']]]);

        $greeter = self::getContainer()->get('test.'.GreeterInterface::class);

        $this->assertInstanceOf(GreeterInterface::class, $greeter);
        $this->assertSame('Hi Jane!', $greeter->greet('Jane'));
    }

    /**
     * @param array<string, mixed> $config
     */
    #[DataProvider('provideInvalidConfigurations')]
    public function testInvalidConfigurationIsRejected(array $config, string $expectedMessage): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage($expectedMessage);

        self::bootKernel(['extension_configs' => ['acme_php_kitchen_sink' => $config]]);
    }

    /**
     * @return iterable<string, array{array<string, mixed>, string}>
     */
    public static function provideInvalidConfigurations(): iterable
    {
        yield 'empty greeting' => [['greeting' => ''], 'The path "acme_php_kitchen_sink.greeting" cannot contain an empty value'];
        yield 'unknown option' => [['unknown' => true], 'Unrecognized option "unknown" under "acme_php_kitchen_sink"'];
    }

    /**
     * Supports the "extension_configs" option, see TestKernel::__construct().
     *
     * @param array<mixed> $options
     */
    protected static function createKernel(array $options = []): TestKernel
    {
        return new TestKernel(extensionConfigs: $options['extension_configs'] ?? []);
    }
}
