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
 * Continuous integration with GitHub Actions (.github/workflows/ci.yml) and Dependabot (.github/dependabot.yml).
 *
 * The workflow follows the best practices: tests on every supported PHP version and Symfony
 * version (Symfony Flex with SYMFONY_REQUIRE), with the lowest dependencies on the lowest PHP
 * version of each Symfony version, with code coverage on the latest versions, and on the next
 * minor version of Symfony (allowed to fail); the quality checks ("qa" scripts) on the latest versions.
 *
 * Features add the PHP extensions their tests need (ListSlot::CiPhpExtensions); quality checks
 * are Composer scripts flagged with "qa: true", the workflow runs each of them.
 *
 * @see https://symfony.com/doc/current/bundles/best_practices.html#continuous-integration
 */
final class GitHubActionsFeature extends AbstractFeature
{
    public const NAME = 'ci';
    protected const QUESTION = 'Add a GitHub Actions workflow (every supported PHP and Symfony version) and Dependabot?';
    protected const GROUP = FeatureGroup::QualityAssurance;
    protected const ENABLED_BY_DEFAULT = true;

    public function contribute(BundleBlueprint $blueprint): void
    {
        $blueprint->addFile('features/ci/.github/workflows/ci.yml.tpl', '.github/workflows/ci.yml');
        $blueprint->addFile('features/ci/.github/dependabot.yml.tpl', '.github/dependabot.yml');

        $url = 'https://github.com/'.$blueprint->naming->packageName.'/actions/workflows/ci.yml';
        $blueprint->slots->addValue(ListSlot::ReadmeBadges, \sprintf('[![CI](%s/badge.svg)](%s)', $url, $url));
        $blueprint->slots->addValue(ListSlot::GitAttributesExportIgnore, '/.github');
    }
}
