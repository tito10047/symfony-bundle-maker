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

use Tito10047\BundleMaker\Maker\BundleComposerJsonMaker;
use Tito10047\BundleMaker\Maker\BundleOptions;

class BundleComposerJsonMakerTest extends MakerTestCase
{
    public function testCreateComposerJsonFileWithCorrectContent(): void
    {
        file_put_contents($this->bundleDir.'/composer.json', '{}');
        $maker = new BundleComposerJsonMaker($this->bundleDir);
        $options = new BundleOptions();
        $options->name = 'acme/foo-bundle';

        $maker->make($options);

        self::assertGenFile('composer.json');
    }
}
