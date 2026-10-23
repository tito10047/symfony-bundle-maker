<?php

namespace Acme\EverythingPhpArrayBundle\Controller;

use Acme\EverythingPhpArrayBundle\Exception\InvalidArgumentException;
use Acme\EverythingPhpArrayBundle\GreeterInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Greets people, in plain text.
 *
 * Routed by the "acme_everything_php_array_hello" route.
 */
final readonly class HelloController
{
    public function __construct(
        private GreeterInterface $greeter,
    ) {
    }

    public function __invoke(string $name): Response
    {
        try {
            $greeting = $this->greeter->greet($name);
        } catch (InvalidArgumentException $exception) {
            throw new BadRequestHttpException($exception->getMessage(), $exception);
        }

        return new Response($greeting, Response::HTTP_OK, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }
}
