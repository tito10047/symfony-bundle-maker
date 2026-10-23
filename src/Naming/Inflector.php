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

namespace Tito10047\BundleMaker\Naming;

/**
 * String transformations used to derive bundle names.
 *
 * @internal
 */
final class Inflector
{
    private function __construct()
    {
    }

    /**
     * Converts a Composer package name segment into StudlyCaps: "foo-bar.baz_qux" becomes "FooBarBazQux".
     */
    public static function classify(string $word): string
    {
        return str_replace(' ', '', ucwords(strtolower(strtr($word, '-_.', '   '))));
    }

    /**
     * Mirrors Symfony\Component\DependencyInjection\Container::underscore().
     *
     * The bundle extension alias is computed at runtime by AbstractBundle with that very
     * algorithm, so it is duplicated here (this plugin must not depend on symfony/dependency-injection)
     * and kept in sync through a dedicated test.
     */
    public static function underscore(string $id): string
    {
        return strtolower((string) preg_replace(
            ['/([A-Z]+)([A-Z][a-z])/', '/([a-z\d])([A-Z])/'],
            ['\\1_\\2', '\\1_\\2'],
            str_replace('_', '.', $id),
        ));
    }
}
