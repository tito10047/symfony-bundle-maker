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
 * Supported licenses, identified by their SPDX identifier.
 *
 * The list is closed on purpose: a LICENSE file is generated for each of them.
 *
 * @see https://spdx.org/licenses/
 */
enum License: string
{
    case Mit = 'MIT';
    case Apache2 = 'Apache-2.0';
    case Bsd2Clause = 'BSD-2-Clause';
    case Bsd3Clause = 'BSD-3-Clause';
    case Gpl3OrLater = 'GPL-3.0-or-later';
    case Lgpl3OrLater = 'LGPL-3.0-or-later';
    case Mpl2 = 'MPL-2.0';
    case Proprietary = 'proprietary';

    /**
     * Human-readable name, e.g. "MIT License".
     */
    public function getName(): string
    {
        return match ($this) {
            self::Mit => 'MIT License',
            self::Apache2 => 'Apache License 2.0',
            self::Bsd2Clause => 'BSD 2-Clause License',
            self::Bsd3Clause => 'BSD 3-Clause License',
            self::Gpl3OrLater => 'GNU General Public License v3.0 or later',
            self::Lgpl3OrLater => 'GNU Lesser General Public License v3.0 or later',
            self::Mpl2 => 'Mozilla Public License 2.0',
            self::Proprietary => 'Proprietary',
        };
    }

    /**
     * Case-insensitive lookup by SPDX identifier.
     */
    public static function fromIdentifier(string $identifier): ?self
    {
        foreach (self::cases() as $license) {
            if (0 === strcasecmp($license->value, $identifier)) {
                return $license;
            }
        }

        return null;
    }

    /**
     * @return list<string>
     */
    public static function identifiers(): array
    {
        return array_map(static fn (self $license): string => $license->value, self::cases());
    }
}
