<?php

declare(strict_types=1);

/*
 * This file is part of the tito10047/symfony-bundle-maker package.
 *
 * (c) Jozef Môstka <jozef@mostka.sk>
 * (c) Yonel Ceruto <yonelceruto@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tito10047\BundleMaker\Blueprint;

/**
 * Argument of a service definition: a reference to a service or to a container parameter,
 * rendered in the chosen configuration format.
 */
final readonly class ServiceArgument
{
    private function __construct(
        public ArgumentType $type,
        private string $reference,
    ) {
    }

    public static function service(string $id): self
    {
        return new self(ArgumentType::Service, $id);
    }

    public static function parameter(string $name): self
    {
        return new self(ArgumentType::Parameter, $name);
    }

    /**
     * The referenced service id or parameter name.
     */
    public function getReference(): string
    {
        return $this->reference;
    }
}
