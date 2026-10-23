<?php

namespace MyVendor\EverythingYamlBundle\Controller;

use MyVendor\EverythingYamlBundle\Exception\InvalidArgumentException;
use MyVendor\EverythingYamlBundle\GreeterInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Greets people, in plain text.
 *
 * Routed by the "my_vendor_everything_yaml_hello" route.
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
