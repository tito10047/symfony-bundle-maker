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
 * @internal
 */
final class Identifiers
{
    private function __construct()
    {
    }

    public static function isClassName(string $class): bool
    {
        return 1 === preg_match('/^[a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*(?:\\\\[a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*)*$/', $class);
    }

    /**
     * Whether the class name has a namespace, e.g. "Acme\\BlogBundle\\GreeterInterface".
     */
    public static function isNamespacedClassName(string $class): bool
    {
        return str_contains($class, '\\') && self::isClassName($class);
    }

    /**
     * Whether the id is "<alias>.<name>" or, for internal services, ".<alias>.<name>".
     */
    public static function isPrefixedServiceId(string $id, string $alias): bool
    {
        return 1 === preg_match('/^\.?'.preg_quote($alias, '/').'\.[a-z0-9_]+(?:\.[a-z0-9_]+)*$/', $id);
    }
}
