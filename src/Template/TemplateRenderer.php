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

namespace Tito10047\BundleMaker\Template;

class TemplateRenderer
{
    public function __construct(private readonly string $baseDir = __DIR__.'/../../templates')
    {
    }

    /**
     * @param array<string, string> $parameters
     */
    public function render(string $template, array $parameters = []): string
    {
        $filename = $this->baseDir.'/'.$template;

        if (!file_exists($filename)) {
            throw new \InvalidArgumentException(\sprintf('Template "%s" not found.', $template));
        }

        if (false === $content = file_get_contents($filename)) {
            throw new \RuntimeException(\sprintf('Unable to read "%s" template.', $template));
        }

        if ([] !== $parameters) {
            $placeholders = array_map(static fn (string $name) => '<'.$name.'>', array_keys($parameters));
            $content = str_replace($placeholders, array_values($parameters), $content);
        }

        return $content;
    }
}
