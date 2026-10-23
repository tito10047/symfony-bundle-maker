<?php

namespace Zeta\EverythingPhpBundle;

use Zeta\EverythingPhpBundle\Exception\InvalidArgumentException;

/**
 * Greets people.
 *
 * Type-hint this interface in your services to get the "zeta_everything_php.greeter" service autowired.
 */
interface GreeterInterface
{
    /**
     * Returns the greeting of the given name, e.g. "Hello Jane!".
     *
     * @throws InvalidArgumentException when the name is empty
     */
    public function greet(string $name): string;
}
