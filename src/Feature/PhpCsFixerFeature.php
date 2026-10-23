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

namespace Tito10047\BundleMaker\Feature;

use Tito10047\BundleMaker\Blueprint\BundleBlueprint;
use Tito10047\BundleMaker\Blueprint\ListSlot;

/**
 * The coding standards of Symfony with PHP-CS-Fixer (.php-cs-fixer.dist.php, "composer cs" and
 * "composer cs:fix"): the "@Symfony" and "@Symfony:risky" rule sets, as Symfony itself.
 *
 * @see https://symfony.com/doc/current/contributing/code/standards.html
 */
final class PhpCsFixerFeature extends AbstractFeature
{
    public const NAME = 'cs-fixer';
    protected const QUESTION = 'Add PHP-CS-Fixer (Symfony coding standards)?';
    protected const GROUP = FeatureGroup::QualityAssurance;
    protected const ENABLED_BY_DEFAULT = true;

    public function contribute(BundleBlueprint $blueprint): void
    {
        $blueprint->composer->requireDev('friendsofphp/php-cs-fixer', '^3.92');
        $blueprint->composer->addScript('cs', 'php-cs-fixer check --diff', 'Checks the coding standards (PHP-CS-Fixer)', qa: true);
        $blueprint->composer->addScript('cs:fix', 'php-cs-fixer fix', 'Fixes the coding standards (PHP-CS-Fixer)');

        $blueprint->addFile('features/cs-fixer/.php-cs-fixer.dist.php.tpl', '.php-cs-fixer.dist.php');
        $blueprint->slots->addValue(ListSlot::GitAttributesExportIgnore, '/.php-cs-fixer.dist.php');
        // local configuration and cache
        $blueprint->slots->addValue(ListSlot::GitIgnore, '/.php-cs-fixer.php', '/.php-cs-fixer.cache');
    }
}
