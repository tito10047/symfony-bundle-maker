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

final readonly class ComposerScript
{
    /**
     * @param non-empty-list<string> $commands
     * @param bool                   $qa       whether "composer qa" runs this script
     */
    public function __construct(
        public string $name,
        public array $commands,
        public ?string $description = null,
        public bool $qa = false,
    ) {
        if (1 !== preg_match('/^[a-z][a-z0-9-]*(?::[a-z][a-z0-9-]*)*$/', $name)) {
            throw new \InvalidArgumentException(\sprintf('The Composer script name "%s" is invalid: use lowercase words separated by "-" or ":".', $name));
        }
    }
}
