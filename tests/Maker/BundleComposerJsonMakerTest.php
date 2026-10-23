<?php

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

        $this->assertGenFile('composer.json');
    }
}
