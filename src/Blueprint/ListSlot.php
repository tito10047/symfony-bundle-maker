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
 * Value slots of the shared ("master") templates: sets of unique strings,
 * kept in registration order. Master templates decide how to sort and render them.
 *
 * Adding a case requires rendering it in the master template that owns it.
 */
enum ListSlot: string
{
    /** Fully-qualified class names imported ("use") by the bundle class, for code added to the Bundle* slots. */
    case BundleUses = 'bundle.uses';

    /** Fully-qualified class names of the bundles registered by the test kernel, besides FrameworkBundle and the bundle itself. */
    case TestKernelBundles = 'test_kernel.bundles';

    /** Fully-qualified class names imported ("use") by the test kernel, for code added to the TestKernel* slots. */
    case TestKernelUses = 'test_kernel.uses';

    /** Paths excluded from the Composer archive in .gitattributes, e.g. "/phpstan.dist.neon". */
    case GitAttributesExportIgnore = 'gitattributes.export_ignore';

    /** Patterns of .gitignore, e.g. "/.php-cs-fixer.cache". */
    case GitIgnore = 'gitignore';

    /** Markdown badges shown under the title of README.md, e.g. the CI status. */
    case ReadmeBadges = 'readme.badges';

    /**
     * PHP extensions installed by every job of .github/workflows/ci.yml (shivammathur/setup-php "extensions" input),
     * e.g. "pdo_sqlite". Rendered only when the "ci" feature is enabled.
     */
    case CiPhpExtensions = 'ci.php_extensions';
}
