<?php

namespace MyVendor\EverythingYamlBundle\EventListener;

use MyVendor\EverythingYamlBundle\GreeterInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

/**
 * Adds a greeting header to the responses of the application, e.g. "X-My-Vendor-Everything-Yaml-Greeting: Hello World!".
 *
 * Listens to the "kernel.response" event when the "greeting_header" option is enabled.
 */
final readonly class GreetingHeaderListener
{
    public const HEADER = 'X-My-Vendor-Everything-Yaml-Greeting';

    public function __construct(
        private GreeterInterface $greeter,
    ) {
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        // the responses of sub-requests (fragments, ESI) are part of the response of the main request
        if (!$event->isMainRequest()) {
            return;
        }

        $headers = $event->getResponse()->headers;

        // a header set by the application takes precedence
        if (!$headers->has(self::HEADER)) {
            $headers->set(self::HEADER, $this->greeter->greet('World'));
        }
    }
}
