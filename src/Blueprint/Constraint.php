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
 * Version constraints shared by every contributor, so the supported versions are defined once.
 */
final class Constraint
{
    /** Symfony 7.4 LTS requires PHP 8.2; Symfony 8.1 requires PHP 8.4 (enforced by Symfony itself). */
    public const PHP = '>=8.2';

    /**
     * Every Symfony component and bundle: 7.4 LTS and 8.1+ (8.0 is no longer maintained).
     *
     * 7.4.17 and 8.1.5 are the first versions whose ErrorHandler no longer leaks its exception
     * handler when a kernel boots, which PHPUnit reports as risky (symfony/symfony#65534).
     */
    public const SYMFONY = '^7.4.17 || ^8.1.5';

    private function __construct()
    {
    }
}
