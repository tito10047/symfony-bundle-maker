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
 * A partial template contributed to a text slot.
 */
final readonly class Partial
{
    /**
     * @param string               $template   path relative to the templates directory, ending with ".tpl"
     * @param array<string, mixed> $parameters extra variables for the partial (on top of the standard ones)
     */
    public function __construct(
        public string $template,
        public array $parameters = [],
        public int $priority = 0,
    ) {
    }
}
