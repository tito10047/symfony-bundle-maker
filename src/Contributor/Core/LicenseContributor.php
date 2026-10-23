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
use Tito10047\BundleMaker\Options\License;

/**
 * The LICENSE file: the full text of the chosen license, with the copyright line
 * (year and author) when the license has one.
 *
 * The LGPL only lists additional permissions on top of the GPL: its LICENSE file
 * contains both texts, as the LGPL requires.
 */
final class LicenseContributor implements ContributorInterface
{
    /**
     * @param int $year year of the copyright notice
     */
    public function __construct(
        private readonly int $year,
    ) {
    }

    public function contribute(BundleBlueprint $blueprint): void
    {
        $blueprint->addFile(self::getTemplate($blueprint->options->license), 'LICENSE', ['year' => $this->year]);
    }

    /**
     * Template of the license text, e.g. "core/licenses/MIT.tpl".
     */
    public static function getTemplate(License $license): string
    {
        return 'core/licenses/'.$license->value.'.tpl';
    }
}
