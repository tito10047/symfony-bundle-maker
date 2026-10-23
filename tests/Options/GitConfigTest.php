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

namespace Tito10047\BundleMaker\Tests\Options;

use Composer\Util\ProcessExecutor;
use PHPUnit\Framework\TestCase;
use Tito10047\BundleMaker\Options\GitConfig;

final class GitConfigTest extends TestCase
{
    public function testReadsAndCachesValues(): void
    {
        $process = $this->createMock(ProcessExecutor::class);
        $process->expects(self::once())
            ->method('execute')
            ->with(['git', 'config', '--get', 'user.name'])
            ->willReturnCallback(static function (array $command, mixed &$output): int {
                $output = "Jane Doe\n";

                return 0;
            });

        $git = new GitConfig($process);

        self::assertSame('Jane Doe', $git->get('user.name'));
        self::assertSame('Jane Doe', $git->get('user.name'));
    }

    public function testReturnsNullOnFailure(): void
    {
        $process = $this->createMock(ProcessExecutor::class);
        $process->method('execute')->willReturn(1);

        self::assertNull((new GitConfig($process))->get('user.email'));
    }

    public function testReturnsNullForEmptyValues(): void
    {
        $process = $this->createMock(ProcessExecutor::class);
        $process->method('execute')->willReturnCallback(static function (array $command, mixed &$output): int {
            $output = "  \n";

            return 0;
        });

        self::assertNull((new GitConfig($process))->get('user.email'));
    }

    public function testReturnsNullWhenGitIsMissing(): void
    {
        $process = $this->createMock(ProcessExecutor::class);
        $process->method('execute')->willThrowException(new \RuntimeException('git: not found'));

        self::assertNull((new GitConfig($process))->get('user.email'));
    }
}
