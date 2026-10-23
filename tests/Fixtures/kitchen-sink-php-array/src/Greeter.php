<?php

namespace Acme\PhpArrayKitchenSinkBundle;

use Acme\PhpArrayKitchenSinkBundle\Exception\InvalidArgumentException;

/**
 * Greets people with the configured greeting.
 *
 * Registered as the "acme_php_array_kitchen_sink.greeter" service.
 */
final readonly class Greeter implements GreeterInterface
{
    /**
     * @param string $greeting the word people are greeted with, e.g. "Hello"
     */
    public function __construct(
        private string $greeting,
    ) {
    }

    public function greet(string $name): string
    {
        $name = trim($name);
        if ('' === $name) {
            throw new InvalidArgumentException('The name to greet must not be empty.');
        }

        return \sprintf('%s %s!', $this->greeting, $name);
    }
}
