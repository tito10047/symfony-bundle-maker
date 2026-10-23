<?php

namespace Zeta\EverythingPhpBundle\Tests\Functional;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Requests the pages of the bundle like a browser does, and only checks the responses.
 */
final class HelloControllerTest extends WebTestCase
{
    #[DataProvider('provideGreetings')]
    public function testGreetsTheNameOfTheUrl(string $url, string $greeting): void
    {
        $client = self::createClient();

        $client->request('GET', $url);

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'text/html; charset=UTF-8');
        $this->assertStringContainsString($greeting, (string) $client->getResponse()->getContent());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function provideGreetings(): iterable
    {
        yield 'name' => ['/hello/Jane', 'Hello Jane!'];
        yield 'several words' => ['/hello/Jane%20Doe', 'Hello Jane Doe!'];
        yield 'no name' => ['/hello', 'Hello World!'];
    }

    public function testRejectsABlankName(): void
    {
        $client = self::createClient();

        $client->request('GET', '/hello/%20');

        $this->assertResponseStatusCodeSame(400);
    }

    public function testOnlyAcceptsGetRequests(): void
    {
        $client = self::createClient();

        $client->request('POST', '/hello/Jane');

        $this->assertResponseStatusCodeSame(405);
    }
}
