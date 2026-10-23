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

namespace Tito10047\BundleMaker\Maker;

use Tito10047\BundleMaker\Composer\CommandRunner;

class BundleDirectoryMaker
{
    public function __construct(
        private readonly CommandRunner $commandRunner,
        private readonly string $bundleDir,
    ) {
    }

    public function make(BundleOptions $options): void
    {
        $this->makeDirectory('config');
        $this->makeDirectory('docs');
        $this->makeDirectory('src');
        $this->makeDirectory('tests');

        if ($options->hasWebAssets) {
            $this->makeDirectory('assets');
            $this->makeDirectory('public');
        }

        if ($options->hasTwigTemplates) {
            $this->makeDirectory('templates');
            $this->commandRunner->require('symfony/twig-bundle');
        }

        if ($options->hasTranslations) {
            $this->makeDirectory('translations');
            $this->commandRunner->require('symfony/translation');
        }

        if ($options->hasControllers) {
            $this->makeDirectory('src/Controller');
        }
    }

    private function makeDirectory(string $name): void
    {
        $dir = $this->bundleDir.'/'.$name;

        if (!is_dir($dir) && !mkdir($dir, 0o777, true) && !is_dir($dir)) {
            throw new \RuntimeException(\sprintf('Directory "%s" was not created.', $dir));
        }
    }
}
