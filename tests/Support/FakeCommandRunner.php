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

namespace Tito10047\BundleMaker\Tests\Support;

use Tito10047\BundleMaker\Composer\CommandRunnerInterface;

final class FakeCommandRunner implements CommandRunnerInterface
{
    /** @var list<string> */
    public array $calls = [];

    public function __construct(private readonly int|\Exception $updateResult = 0)
    {
    }

    public function update(): int
    {
        $this->calls[] = 'update';
        if ($this->updateResult instanceof \Exception) {
            throw $this->updateResult;
        }

        return $this->updateResult;
    }
}
