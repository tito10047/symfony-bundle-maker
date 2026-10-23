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

namespace Tito10047\BundleMaker\Options;

/**
 * Author of the bundle, as written in the "authors" section of composer.json and in the LICENSE file.
 */
final readonly class Author
{
    /**
     * @throws \InvalidArgumentException
     */
    public function __construct(
        public string $name,
        public ?string $email = null,
    ) {
        if ('' === trim($name)) {
            throw new \InvalidArgumentException('The author name must not be empty.');
        }

        if (null !== $email && false === filter_var($email, \FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException(\sprintf('The author email "%s" is invalid.', $email));
        }
    }
}
