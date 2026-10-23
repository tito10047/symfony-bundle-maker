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

namespace Tito10047\BundleMaker\Tests\Maker;

use PHPUnit\Framework\TestCase;
use Tito10047\BundleMaker\Maker\BundleDirectoryMaker;

abstract class MakerTestCase extends TestCase
{
    protected BundleDirectoryMaker $maker;
    protected string $bundleDir;
    protected string $expectedDir;

    protected function setUp(): void
    {
        $this->expectedDir = __DIR__.'/../Expected/acme-bundle';
        $this->bundleDir = sys_get_temp_dir().'/bundle-maker/bundles/acme-bundle';
        if (!is_dir($this->bundleDir)) {
            mkdir($this->bundleDir, 0o777, true);
        }
    }

    protected function tearDown(): void
    {
        if (is_dir($this->bundleDir)) {
            $this->rmdir($this->bundleDir);
        }
    }

    protected function assertGenFile(string $relativePath, bool $save = false): void
    {
        if ($save) {
            file_put_contents($this->expectedDir.'/'.$relativePath.'.expected', file_get_contents($this->bundleDir.'/'.$relativePath));
        }

        self::assertFileEquals($this->expectedDir.'/'.$relativePath.'.expected', $this->bundleDir.'/'.$relativePath);
    }

    private function rmdir(string $dir): bool
    {
        if (!file_exists($dir)) {
            return true;
        }

        if (!is_dir($dir)) {
            return unlink($dir);
        }

        if (false === $items = scandir($dir)) {
            return false;
        }

        foreach ($items as $item) {
            if ('.' === $item || '..' === $item) {
                continue;
            }

            if (!$this->rmdir($dir.\DIRECTORY_SEPARATOR.$item)) {
                return false;
            }
        }

        return rmdir($dir);
    }
}
