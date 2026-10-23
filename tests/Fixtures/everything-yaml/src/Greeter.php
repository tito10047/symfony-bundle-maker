<?php

namespace Tito10047\EverythingYamlBundle;

use Tito10047\EverythingYamlBundle\Exception\InvalidArgumentException;

/**
 * Greets people with the configured greeting.
 *
 * Registered as the "tito10047_everything_yaml.greeter" service.
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
