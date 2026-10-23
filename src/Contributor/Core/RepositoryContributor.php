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

namespace Tito10047\BundleMaker\Contributor\Core;

use Tito10047\BundleMaker\Blueprint\BundleBlueprint;
use Tito10047\BundleMaker\Blueprint\ListSlot;
use Tito10047\BundleMaker\Contributor\ContributorInterface;

/**
 * The repository files every bundle has: .editorconfig, .gitattributes and .gitignore.
 *
 * Their entries come from ListSlot::GitAttributesExportIgnore and ListSlot::GitIgnore,
 * so every contributor declares the files it generates: development files are kept out of
 * the archive Composer installs, and generated files out of Git.
 */
final class RepositoryContributor implements ContributorInterface
{
    public function contribute(BundleBlueprint $blueprint): void
    {
        $blueprint->addFile('core/editorconfig.tpl', '.editorconfig');
        $blueprint->addFile('core/gitattributes.tpl', '.gitattributes');
        $blueprint->addFile('core/gitignore.tpl', '.gitignore');

        $blueprint->slots->addValue(ListSlot::GitAttributesExportIgnore, '/.editorconfig', '/.gitattributes', '/.github', '/.gitignore');
        $blueprint->slots->addValue(ListSlot::GitIgnore, '/composer.lock', '/vendor/');
    }
}
