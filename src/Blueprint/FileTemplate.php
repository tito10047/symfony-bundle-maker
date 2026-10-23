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
 * A file of the generated bundle, rendered from a template.
 */
final readonly class FileTemplate
{
    /**
     * @param string               $template    path relative to the templates directory, ending with ".tpl"
     * @param string               $destination path relative to the bundle root, e.g. "src/AcmeBlogBundle.php"
     * @param array<string, mixed> $parameters  extra variables for the template (on top of the standard ones)
     */
    public function __construct(
        public string $template,
        public string $destination,
        public array $parameters = [],
    ) {
        if ('' === $destination || str_starts_with($destination, '/') || str_ends_with($destination, '/') || \in_array('..', explode('/', $destination), true) || str_contains($destination, '\\')) {
            throw new \InvalidArgumentException(\sprintf('The destination "%s" must be a file path relative to the bundle root, without "..".', $destination));
        }
    }
}
