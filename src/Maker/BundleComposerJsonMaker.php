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

use Composer\Json\JsonManipulator;
use Tito10047\BundleMaker\Utils\Inflector;

class BundleComposerJsonMaker
{
    public function __construct(private readonly string $bundleDir)
    {
    }

    public function make(BundleOptions $options): void
    {
        $file = $this->bundleDir.'/composer.json';

        if (false === $contents = file_get_contents($file)) {
            throw new \RuntimeException(\sprintf('Unable to read "%s" file.', $file));
        }

        $manipulator = new JsonManipulator($contents);
        $manipulator->addProperty('name', $options->name);
        $manipulator->addProperty('description', $options->description);
        $manipulator->addSubNode('autoload', 'psr-4', [Inflector::namespacefy($options->name).'\\' => 'src/']);
        $manipulator->addSubNode('autoload-dev', 'psr-4', [Inflector::namespacefy($options->name).'\\Tests\\' => 'tests/']);

        file_put_contents($file, $manipulator->getContents());
    }
}
