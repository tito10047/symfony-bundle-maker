<?php

namespace Tito10047\BundleMaker\Tests\Maker;

use Tito10047\BundleMaker\Maker\BundleMaker;
use Tito10047\BundleMaker\Maker\BundleOptions;

class BundleMakerTest extends MakerTestCase
{
    public function testCreateBundleWithCorrectNamespaceAndClassName(): void
    {
        $maker = new BundleMaker($this->bundleDir);
        $options = new BundleOptions();
        $options->name = 'acme/foo-bundle';

        $maker->make($options);

        $this->assertGenFile('src/FooBundle.php');
    }

    public function testCreateConfigureMethodWhenHasConfigOptionIsTrue(): void
    {
        $maker = new BundleMaker($this->bundleDir);
        $options = new BundleOptions();
        $options->name = 'acme/foo-with-config-bundle';
        $options->hasConfig = true;

        $maker->make($options);

        $this->assertGenFile('src/FooWithConfigBundle.php');
    }
}
