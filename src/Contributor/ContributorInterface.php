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

namespace Tito10047\BundleMaker\Contributor;

use Tito10047\BundleMaker\Blueprint\BundleBlueprint;

/**
 * Contributes files, dependencies, services, routes and slot partials to the generated bundle.
 *
 * Contributors only describe the bundle: they must not write files nor perform I/O.
 */
interface ContributorInterface
{
    public function contribute(BundleBlueprint $blueprint): void;
}
