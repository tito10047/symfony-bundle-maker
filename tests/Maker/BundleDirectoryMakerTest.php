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

use Tito10047\BundleMaker\Composer\CommandRunner;
use Tito10047\BundleMaker\Maker\BundleDirectoryMaker;
use Tito10047\BundleMaker\Maker\BundleOptions;

class BundleDirectoryMakerTest extends MakerTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $commandRunner = $this->createMock(CommandRunner::class);
        $this->maker = new BundleDirectoryMaker($commandRunner, $this->bundleDir);
    }

    public function testMakeDefault(): void
    {
        $this->maker->make(new BundleOptions());

        self::assertFileExists($this->bundleDir.'/config');
        self::assertFileExists($this->bundleDir.'/src');
        self::assertFileExists($this->bundleDir.'/tests');
        self::assertFileDoesNotExist($this->bundleDir.'/assets');
        self::assertFileDoesNotExist($this->bundleDir.'/public');
        self::assertFileDoesNotExist($this->bundleDir.'/templates');
        self::assertFileDoesNotExist($this->bundleDir.'/translation');
    }

    public function testMakeWebAssets(): void
    {
        $options = new BundleOptions();
        $options->hasWebAssets = true;

        $this->maker->make($options);

        self::assertDirectoryExists($this->bundleDir.'/assets');
        self::assertDirectoryExists($this->bundleDir.'/public');
    }

    public function testMakeConfig(): void
    {
        $options = new BundleOptions();
        $options->hasConfig = true;

        $this->maker->make($options);

        self::assertDirectoryExists($this->bundleDir.'/config');
    }

    public function testMakeTwigTemplates(): void
    {
        $options = new BundleOptions();
        $options->hasTwigTemplates = true;

        $this->maker->make($options);

        self::assertDirectoryExists($this->bundleDir.'/templates');
    }

    public function testMakeTranslations(): void
    {
        $options = new BundleOptions();
        $options->hasTranslations = true;

        $this->maker->make($options);

        self::assertDirectoryExists($this->bundleDir.'/translations');
    }
}
