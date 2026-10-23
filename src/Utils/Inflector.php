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

namespace Tito10047\BundleMaker\Utils;

class Inflector
{
    /**
     * Converts a word into the format for a PHP class name. Converts 'acme-bundle' to 'AcmeBundle'.
     */
    public static function classify(string $word): string
    {
        return str_replace([' ', '_', '-'], '', ucwords($word, ' _-/'));
    }

    /**
     * Converts a composer package name into the format for a PHP namespace. Converts 'acme/acme-bundle' to 'Acme\AcmeBundle'.
     */
    public static function namespacefy(string $name): string
    {
        return str_replace('/', '\\', self::classify($name));
    }

    /**
     * Converts a composer package name into the format for a PHP class name. Converts 'acme/acme-bundle' to 'AcmeBundle'.
     */
    public static function className(string $name): string
    {
        return self::classify(self::shortName($name));
    }

    /**
     * Converts 'acme/foo-bundle' to 'acme_foo'.
     */
    public static function vendory(string $name): string
    {
        if (!str_contains($name, '/')) {
            return '';
        }

        $dash = strpos($name, '-');

        return strtolower(str_replace('/', '_', false === $dash ? $name : substr($name, 0, $dash)));
    }

    public static function fileName(string $name): string
    {
        return strtolower(self::shortName($name));
    }

    /**
     * Converts 'acme/foo-bundle' to 'foo-bundle'.
     */
    private static function shortName(string $name): string
    {
        $slash = strrpos($name, '/');

        return false === $slash ? $name : substr($name, $slash + 1);
    }
}
