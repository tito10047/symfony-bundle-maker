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

class TemplateFileCreator
{
    public function __construct(
        private readonly string $bundleDir,
        private readonly TemplateRenderer $renderer = new TemplateRenderer(),
    ) {
    }

    /**
     * @param array<string, string> $parameters
     */
    public function create(string $name, array $parameters = [], ?string $destName = null): void
    {
        $content = $this->renderer->render($name.'.template', $parameters);
        $destination = $this->bundleDir.'/'.($destName ?? $name);

        if (!is_dir(\dirname($destination)) || false === file_put_contents($destination, $content)) {
            throw new \RuntimeException(\sprintf('Unable to create "%s" file.', $destination));
        }
    }
}
