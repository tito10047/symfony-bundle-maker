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
use Tito10047\BundleMaker\Contributor\ContributorInterface;

/**
 * The documentation every bundle has:
 *
 *  - README.md: description, requirements, the standard installation instructions
 *    (see the Readme* slots), development commands and license;
 *  - docs/index.md: the mandatory entry point of the documentation (see Slot::DocsSections),
 *    shipped with the package: README.md links to it;
 *  - CHANGELOG.md, following the "Keep a Changelog" format.
 *
 * @see https://symfony.com/doc/current/bundles/best_practices.html#documentation
 */
final class DocumentationContributor implements ContributorInterface
{
    public function contribute(BundleBlueprint $blueprint): void
    {
        $blueprint->addFile('core/README.md.tpl', 'README.md');
        $blueprint->addFile('core/CHANGELOG.md.tpl', 'CHANGELOG.md');
        $blueprint->addFile('core/docs/index.md.tpl', 'docs/index.md');
    }
}
