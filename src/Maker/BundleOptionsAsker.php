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

use Composer\IO\IOInterface;

class BundleOptionsAsker
{
    public function __construct(private readonly IOInterface $io)
    {
    }

    public function ask(): BundleOptions
    {
        $options = new BundleOptions();

        $this->io->write(' ');
        $this->io->write('<info>✨ Bundle Assistant</info>');
        $this->io->write(' ');
        foreach ((new \ReflectionClass($options))->getProperties() as $property) {
            $default = $property->getValue($options);
            $question = \sprintf(
                '%s (%s): ',
                $this->humanizeProperty($property),
                $this->humanizeValue($default),
            );

            if (\is_bool($default)) {
                $property->setValue($options, $this->io->askConfirmation($question, $default));
            } elseif (\is_string($default)) {
                $property->setValue($options, $this->io->ask($question, $default));
            }
        }

        return $options;
    }

    private function humanizeProperty(\ReflectionProperty $property): string
    {
        // the summaries of the doc comments end with a period, the questions do not
        return rtrim(trim((string) preg_replace('/\/\*\*|\*\/|\n\s*\* ?/', '', (string) $property->getDocComment()), " \t\n\r\0\x0B/"), '.');
    }

    private function humanizeValue(mixed $value): string
    {
        return match (true) {
            \is_bool($value) => $value ? 'y' : 'n',
            \is_string($value) => $value,
            default => '',
        };
    }
}
