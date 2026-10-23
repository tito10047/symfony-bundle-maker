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
 * Thrown when a BUNDLE_MAKER_* environment variable has an invalid value.
 */
final class InvalidPresetException extends \InvalidArgumentException
{
    public static function fromInvalidValue(string $variable, \InvalidArgumentException $previous): self
    {
        return new self(\sprintf('Invalid value for the environment variable "%s": %s', $variable, $previous->getMessage()), 0, $previous);
    }

    public static function fromDisabledRequirement(string $variable, string $feature, string $required, string $disabledBy): self
    {
        return new self(\sprintf('The environment variable "%s" enables the feature "%s", which requires the feature "%s", disabled by "%s".', $variable, $feature, $required, $disabledBy));
    }

    /**
     * @param list<string> $variables
     * @param list<string> $known
     */
    public static function fromUnknownVariables(array $variables, array $known): self
    {
        return new self(\sprintf('Unknown environment variable "%s". Known variables: "%s".', implode('", "', $variables), implode('", "', $known)));
    }
}
