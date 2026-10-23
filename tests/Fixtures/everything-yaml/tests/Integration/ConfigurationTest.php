<?php

namespace MyVendor\EverythingYamlBundle\Tests\Integration;

use MyVendor\EverythingYamlBundle\GreeterInterface;
use MyVendor\EverythingYamlBundle\Tests\App\TestKernel;
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
        self::bootKernel(['extension_configs' => ['my_vendor_everything_yaml' => ['greeting' => 'Hi']]]);

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

        self::bootKernel(['extension_configs' => ['my_vendor_everything_yaml' => $config]]);
    }

    /**
     * @return iterable<string, array{array<string, mixed>, string}>
     */
    public static function provideInvalidConfigurations(): iterable
    {
        yield 'empty greeting' => [['greeting' => ''], 'The path "my_vendor_everything_yaml.greeting" cannot contain an empty value'];
        yield 'unknown option' => [['unknown' => true], 'Unrecognized option "unknown" under "my_vendor_everything_yaml"'];
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
