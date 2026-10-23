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
 * The bundle class (src/AcmeBlogBundle.php), an AbstractBundle whose methods are
 * assembled from the Bundle* slots.
 */
final class BundleClassContributor implements ContributorInterface
{
    public function contribute(BundleBlueprint $blueprint): void
    {
        $blueprint->addFile('core/src/Bundle.php.tpl', 'src/'.$blueprint->naming->className.'.php');
    }
}
