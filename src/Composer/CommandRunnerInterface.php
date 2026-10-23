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

namespace Tito10047\BundleMaker\Composer;

/**
 * Installs the dependencies of the generated bundle.
 */
interface CommandRunnerInterface
{
    /**
     * Runs "composer update"; returns the exit code.
     */
    public function update(): int;
}
