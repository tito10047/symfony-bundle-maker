<?php

namespace Zeta\EverythingPhpBundle\EventListener;

use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Zeta\EverythingPhpBundle\GreeterInterface;

/**
 * Adds a greeting header to the responses of the application, e.g. "X-Zeta-Everything-Php-Greeting: Hello World!".
 *
 * Listens to the "kernel.response" event when the "greeting_header" option is enabled.
 */
final readonly class GreetingHeaderListener
{
    public const HEADER = 'X-Zeta-Everything-Php-Greeting';

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
