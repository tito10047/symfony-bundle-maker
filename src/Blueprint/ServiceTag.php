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

final readonly class ServiceTag
{
    /**
     * @param array<string, string|int|float|bool> $attributes
     */
    public function __construct(
        public string $name,
        public array $attributes = [],
    ) {
        if ('' === $name) {
            throw new \InvalidArgumentException('The tag name must not be empty.');
        }

        if (\array_key_exists('name', $attributes)) {
            throw new \InvalidArgumentException(\sprintf('The tag "%s" must not have a "name" attribute: it is reserved for the tag name.', $name));
        }
    }
}
