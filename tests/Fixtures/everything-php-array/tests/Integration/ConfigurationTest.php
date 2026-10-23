<?php

namespace Acme\EverythingPhpArrayBundle\Tests\Integration;

use Acme\EverythingPhpArrayBundle\EventListener\GreetingHeaderListener;
use Acme\EverythingPhpArrayBundle\GreeterInterface;
use Acme\EverythingPhpArrayBundle\Tests\App\TestKernel;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Boots the test application with the given configuration of the bundle.
 */
final class ConfigurationTest extends KernelTestCase
{
    public function testGreetingIsPassedToTheGreeter(): void
    {
        self::bootKernel(['extension_configs' => ['acme_everything_php_array' => ['greeting' => 'Hi']]]);

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

        self::bootKernel(['extension_configs' => ['acme_everything_php_array' => $config]]);
    }

    /**
     * @return iterable<string, array{array<string, mixed>, string}>
     */
    public static function provideInvalidConfigurations(): iterable
    {
        yield 'empty greeting' => [['greeting' => ''], 'The path "acme_everything_php_array.greeting" cannot contain an empty value'];
        yield 'unknown option' => [['unknown' => true], 'Unrecognized option "unknown" under "acme_everything_php_array"'];
    }

    public function testGreetingHeaderIsDisabledByDefault(): void
    {
        $response = $this->dispatchResponse(self::bootKernel());

        $this->assertFalse($response->headers->has(GreetingHeaderListener::HEADER));
    }

    public function testGreetingHeaderCanBeEnabled(): void
    {
        $response = $this->dispatchResponse(self::bootKernel(['extension_configs' => ['acme_everything_php_array' => ['greeting_header' => true]]]));

        $this->assertSame('Hello World!', $response->headers->get(GreetingHeaderListener::HEADER));
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

    private function dispatchResponse(KernelInterface $kernel): Response
    {
        $dispatcher = $kernel->getContainer()->get('event_dispatcher');
        $this->assertInstanceOf(EventDispatcherInterface::class, $dispatcher);

        $response = new Response();
        $dispatcher->dispatch(new ResponseEvent($kernel, Request::create('/'), HttpKernelInterface::MAIN_REQUEST, $response), KernelEvents::RESPONSE);

        return $response;
    }
}
