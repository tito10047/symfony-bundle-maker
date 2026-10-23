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
 * Automated refactoring and upgrades with Rector (rector.php, "composer rector" and "composer rector:fix").
 *
 * A local tool, not a quality check: its suggestions are applied on purpose, and are not run by
 * "composer qa" nor by the CI. They target the lowest supported versions: PHP 8.2, and for the
 * Composer-based sets the lowest versions allowed by composer.json, which Rector 2.6.4+ uses for
 * libraries (any package type but "project"), so the changes never break a supported version.
 *
 * @see https://getrector.com/documentation
 */
final class RectorFeature extends AbstractFeature
{
    public const NAME = 'rector';
    protected const QUESTION = 'Add Rector (automated refactoring and upgrades, run locally)?';
    protected const GROUP = FeatureGroup::QualityAssurance;
    protected const ENABLED_BY_DEFAULT = true;

    public function contribute(BundleBlueprint $blueprint): void
    {
        $blueprint->composer->requireDev('rector/rector', '^2.6.4');
        $blueprint->composer->addScript('rector', 'rector process --dry-run', 'Lists the automated refactorings (Rector)');
        $blueprint->composer->addScript('rector:fix', 'rector process', 'Applies the automated refactorings (Rector)');

        $blueprint->addFile('features/rector/rector.php.tpl', 'rector.php');
        $blueprint->slots->addValue(ListSlot::GitAttributesExportIgnore, '/rector.php');
    }
}
