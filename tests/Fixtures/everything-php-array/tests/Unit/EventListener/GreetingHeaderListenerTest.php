<?php

namespace Acme\EverythingPhpArrayBundle\Tests\Unit\EventListener;

use Acme\EverythingPhpArrayBundle\EventListener\GreetingHeaderListener;
use Acme\EverythingPhpArrayBundle\Greeter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

final class GreetingHeaderListenerTest extends TestCase
{
    public function testAddsTheGreetingHeaderToTheMainResponse(): void
    {
        $response = $this->dispatch(HttpKernelInterface::MAIN_REQUEST, new Response());

        $this->assertSame('Hi World!', $response->headers->get(GreetingHeaderListener::HEADER));
    }

    public function testIgnoresTheResponsesOfSubRequests(): void
    {
        $response = $this->dispatch(HttpKernelInterface::SUB_REQUEST, new Response());

        $this->assertFalse($response->headers->has(GreetingHeaderListener::HEADER));
    }

    public function testKeepsTheHeaderSetByTheApplication(): void
    {
        $response = $this->dispatch(HttpKernelInterface::MAIN_REQUEST, new Response(headers: [GreetingHeaderListener::HEADER => 'Welcome!']));

        $this->assertSame('Welcome!', $response->headers->get(GreetingHeaderListener::HEADER));
    }

    /**
     * @param HttpKernelInterface::*_REQUEST $requestType
     */
    private function dispatch(int $requestType, Response $response): Response
    {
        $listener = new GreetingHeaderListener(new Greeter('Hi'));

        $listener->onKernelResponse(new ResponseEvent($this->createStub(HttpKernelInterface::class), Request::create('/'), $requestType, $response));

        return $response;
    }
}
