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

use Tito10047\BundleMaker\Naming\BundleNaming;

/**
 * Validates and normalizes raw answers, from the user or from environment presets.
 *
 * Every method throws an \InvalidArgumentException with a user-facing message on invalid input,
 * as expected by IOInterface::askAndValidate().
 */
final class OptionNormalizer
{
    /**
     * @throws \InvalidArgumentException
     */
    public function packageName(?string $value): BundleNaming
    {
        return new BundleNaming(trim((string) $value));
    }

    /**
     * @return non-empty-string
     *
     * @throws \InvalidArgumentException
     */
    public function description(?string $value): string
    {
        $value = trim((string) $value);
        if ('' === $value) {
            throw new \InvalidArgumentException('The description must not be empty.');
        }

        return $value;
    }

    /**
     * @return non-empty-string
     *
     * @throws \InvalidArgumentException
     */
    public function authorName(?string $value): string
    {
        $value = trim((string) $value);
        if ('' === $value) {
            throw new \InvalidArgumentException('The author name must not be empty.');
        }

        return $value;
    }

    /** Answer omitting the (optional) author email. */
    public const NO_EMAIL = 'none';

    /**
     * The email is optional (Composer authors only require a name): an empty value or "none" omits it.
     *
     * @throws \InvalidArgumentException
     */
    public function authorEmail(?string $value): ?string
    {
        $value = trim((string) $value);
        if ('' === $value || 0 === strcasecmp(self::NO_EMAIL, $value)) {
            return null;
        }

        if (false === filter_var($value, \FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException(\sprintf('"%s" is not a valid email address.', $value));
        }

        return $value;
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function license(?string $value): License
    {
        return License::fromIdentifier(trim((string) $value))
            ?? throw new \InvalidArgumentException(\sprintf('"%s" is not a supported license. Supported licenses: %s.', $value, implode(', ', License::identifiers())));
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function configFormat(?string $value): ConfigFormat
    {
        return ConfigFormat::tryFrom(strtolower(trim((string) $value)))
            ?? throw new \InvalidArgumentException(\sprintf('"%s" is not a supported configuration format. Supported formats: %s.', $value, implode(', ', array_map(static fn (ConfigFormat $format): string => $format->value, ConfigFormat::cases()))));
    }
}
