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

namespace Tito10047\BundleMaker\Tests\Snapshot;

use Tito10047\BundleMaker\Blueprint\BundleBlueprint;
use Tito10047\BundleMaker\Feature\FeatureRegistry;
use Tito10047\BundleMaker\Feature\FeatureSelection;
use Tito10047\BundleMaker\Naming\BundleNaming;
use Tito10047\BundleMaker\Options\Author;
use Tito10047\BundleMaker\Options\BundleOptions;
use Tito10047\BundleMaker\Options\ConfigFormat;
use Tito10047\BundleMaker\Options\License;

/**
 * A snapshot scenario: the options a bundle is generated with, and the fixture directory
 * (tests/Fixtures/<name>/) holding the expected output.
 *
 * Package names must be unique across scenarios: generated bundle classes are loaded in the same process.
 */
final readonly class Scenario
{
    /**
     * @param list<string>|true $features enabled features (true: all of them); every other feature is disabled
     */
    public function __construct(
        public string $name,
        public string $packageName = 'acme/blog-bundle',
        public ConfigFormat $configFormat = ConfigFormat::Php,
        public array|bool $features = [],
        public License $license = License::Mit,
    ) {
    }

    public function createOptions(FeatureRegistry $registry): BundleOptions
    {
        $selection = new FeatureSelection();
        foreach ($registry as $name => $feature) {
            $selection = $selection->with($name, true === $this->features || \in_array($name, $this->features, true));
        }

        $naming = new BundleNaming($this->packageName);

        return new BundleOptions($naming, $naming->className.' for Symfony', new Author('Jane Doe', 'jane@example.com'), $this->license, $this->configFormat, $selection);
    }

    public function createBlueprint(): BundleBlueprint
    {
        $features = Scenarios::createFeatureRegistry();

        return Scenarios::createGenerator($features)->createBlueprint($this->createOptions($features));
    }

    /**
     * Renders the bundle in memory.
     *
     * @return array<string, string> file contents indexed by path
     */
    public function render(): array
    {
        $features = Scenarios::createFeatureRegistry();
        $generator = Scenarios::createGenerator($features);

        return $generator->render($generator->createBlueprint($this->createOptions($features)));
    }

    public function getFixtureDir(): string
    {
        return \dirname(__DIR__).'/Fixtures/'.$this->name;
    }

    /**
     * Whether the scenario includes the test-only kitchen-sink feature.
     */
    public function hasKitchenSink(): bool
    {
        return true === $this->features || \in_array('kitchen-sink', $this->features, true);
    }
}
