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
use Tito10047\BundleMaker\Maker\BundleFileMaker;
use Tito10047\BundleMaker\Maker\BundleOptions;
use Tito10047\BundleMaker\Template\TemplateFileCreator;

class BundleFileMakerTest extends MakerTestCase
{
    public function testCreateBundleFilesWithCorrectContent(): void
    {
        $maker = new BundleFileMaker(new TemplateFileCreator($this->bundleDir));
        $options = new BundleOptions();
        $options->name = 'acme/foo-bundle';
        $options->hasConfig = true;
        $options->hasWebAssets = true;
        $options->hasTwigTemplates = true;
        $options->hasTranslations = true;
        $options->hasControllers = true;

        $commandRunner = $this->createMock(CommandRunner::class);
        $directoryMaker = new BundleDirectoryMaker($commandRunner, $this->bundleDir);
        $directoryMaker->make($options);

        $maker->make($options);

        self::assertGenFile('assets/foo-bundle.js');
        self::assertGenFile('config/routes.php');
        self::assertGenFile('config/definition.php');
        self::assertGenFile('public/foo-bundle.min.js');
        self::assertGenFile('config/services.php');
        self::assertGenFile('docs/index.md');
        self::assertGenFile('src/Controller/HelloController.php');
        self::assertGenFile('templates/hello.html.twig');
        self::assertGenFile('translations/FooBundle.fr.xlf');
        self::assertGenFile('README.md');
        self::assertGenFile('.gitattributes');
    }

    public function testThrowsExceptionWhenUnableToCreateReadmeFile(): void
    {
        $maker = new BundleFileMaker(new TemplateFileCreator('/invalid/path/to/bundle'));
        $options = new BundleOptions();
        $options->name = 'acme/foo-bundle';

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unable to create "/invalid/path/to/bundle/README.md" file.');

        $maker->make($options);
    }
}
