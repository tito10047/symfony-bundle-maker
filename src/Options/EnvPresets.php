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
 * Answers preset through BUNDLE_MAKER_* environment variables.
 *
 * Presets become the default answers: with "--no-interaction" they are used as is,
 * otherwise they are proposed to the user. Empty variables are ignored.
 */
final readonly class EnvPresets
{
    public const PREFIX = 'BUNDLE_MAKER_';
    public const NAME = 'BUNDLE_MAKER_NAME';
    public const DESCRIPTION = 'BUNDLE_MAKER_DESCRIPTION';
    public const AUTHOR_NAME = 'BUNDLE_MAKER_AUTHOR_NAME';
    public const AUTHOR_EMAIL = 'BUNDLE_MAKER_AUTHOR_EMAIL';
    public const LICENSE = 'BUNDLE_MAKER_LICENSE';
    public const CONFIG_FORMAT = 'BUNDLE_MAKER_CONFIG_FORMAT';

    /** Enables (1) or disables (0) every feature at once; per-feature variables take precedence. */
    public const ALL = 'BUNDLE_MAKER_ALL';

    /** Variables that feature names must not collide with. */
    public const RESERVED = [self::NAME, self::DESCRIPTION, self::AUTHOR_NAME, self::AUTHOR_EMAIL, self::LICENSE, self::CONFIG_FORMAT, self::ALL];

    private const TRUE_VALUES = ['1', 'true', 'yes', 'y', 'on'];
    private const FALSE_VALUES = ['0', 'false', 'no', 'n', 'off'];

    /**
     * @param array<string, string> $variables
     */
    public function __construct(private array $variables = [])
    {
    }

    public static function fromEnvironment(): self
    {
        return new self(array_filter(getenv(), static fn (string $name): bool => str_starts_with($name, self::PREFIX), \ARRAY_FILTER_USE_KEY));
    }

    /**
     * The variable presetting the answer of the given feature, e.g. "BUNDLE_MAKER_EVENT_LISTENER" for "event-listener".
     */
    public static function featureVariable(string $feature): string
    {
        return self::PREFIX.strtoupper(str_replace('-', '_', $feature));
    }

    /**
     * Names of the variables that are set (not empty).
     *
     * @return list<string>
     */
    public function getNames(): array
    {
        return array_values(array_filter(array_keys($this->variables), fn (string $name): bool => null !== $this->get($name)));
    }

    public function get(string $variable): ?string
    {
        $value = trim($this->variables[$variable] ?? '');

        return '' === $value ? null : $value;
    }

    /**
     * @throws InvalidPresetException when the value is not a boolean ("1", "true", "yes", "y", "on" or "0", "false", "no", "n", "off")
     */
    public function getBool(string $variable): ?bool
    {
        if (null === $value = $this->get($variable)) {
            return null;
        }

        $normalized = strtolower($value);

        return match (true) {
            \in_array($normalized, self::TRUE_VALUES, true) => true,
            \in_array($normalized, self::FALSE_VALUES, true) => false,
            default => throw InvalidPresetException::fromInvalidValue($variable, new \InvalidArgumentException(\sprintf('"%s" is not a boolean, use 1 or 0.', $value))),
        };
    }

    /**
     * Returns the normalized preset, or null when the variable is not set.
     *
     * @template T
     *
     * @param callable(string): T $normalizer throws \InvalidArgumentException on invalid values
     *
     * @return T|null
     *
     * @throws InvalidPresetException
     */
    public function getValid(string $variable, callable $normalizer): mixed
    {
        if (null === $value = $this->get($variable)) {
            return null;
        }

        try {
            return $normalizer($value);
        } catch (\InvalidArgumentException $e) {
            throw InvalidPresetException::fromInvalidValue($variable, $e);
        }
    }
}
