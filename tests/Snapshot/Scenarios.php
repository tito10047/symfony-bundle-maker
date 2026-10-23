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

use Tito10047\BundleMaker\Contributor\CoreContributors;
use Tito10047\BundleMaker\Feature\FeatureGroup;
use Tito10047\BundleMaker\Feature\FeatureInterface;
use Tito10047\BundleMaker\Feature\FeatureRegistry;
use Tito10047\BundleMaker\Generator\BundleGenerator;
use Tito10047\BundleMaker\Options\ConfigFormat;
use Tito10047\BundleMaker\Options\License;
use Tito10047\BundleMaker\Template\TemplateRenderer;
use Tito10047\BundleMaker\Tests\Support\KitchenSinkFeature;

/**
 * The snapshot scenarios: the minimal and the complete bundle in each configuration format.
 * The other combinations of features are checked by the tests of the features, with Scenario::render().
 *
 * Run "UPDATE_SNAPSHOTS=1 vendor/bin/phpunit" to regenerate the fixtures and review them before committing them.
 */
final class Scenarios
{
    /** Year of the copyright notices, fixed so the snapshots do not change every year. */
    public const YEAR = 2026;

    /**
     * @return array<string, Scenario>
     */
    public static function all(): array
    {
        // every built-in feature, like BUNDLE_MAKER_ALL=1 (the test-only kitchen-sink feature is not built in)
        $everything = array_keys(iterator_to_array(FeatureRegistry::createDefault()));
        $qualityAssurance = array_map(static fn (FeatureInterface $feature): string => $feature->getName(), FeatureRegistry::createDefault()->getByGroup(FeatureGroup::QualityAssurance));

        $scenarios = [
            // the bundle generated with the default answers
            new Scenario('default', 'acme/blog-bundle', ConfigFormat::Php, ['config', ...$qualityAssurance]),
            // nothing but the core files, in each configuration format
            new Scenario('minimal-php', 'acme/minimal-php-bundle'),
            new Scenario('minimal-php-array', 'acme/minimal-php-array-bundle', ConfigFormat::PhpArray),
            new Scenario('minimal-yaml', 'acme/minimal-yaml-bundle', ConfigFormat::Yaml, license: License::Proprietary),
            // every built-in feature together (BUNDLE_MAKER_ALL=1), in each configuration format
            new Scenario('everything-php', 'acme/everything-php-bundle', ConfigFormat::Php, $everything),
            new Scenario('everything-php-array', 'acme/everything-php-array-bundle', ConfigFormat::PhpArray, $everything),
            new Scenario('everything-yaml', 'my-vendor/everything-yaml-bundle', ConfigFormat::Yaml, $everything),
            // every contribution point of the blueprint, in each configuration format
            new Scenario('kitchen-sink-php', 'acme/php-kitchen-sink-bundle', ConfigFormat::Php, ['config', 'kitchen-sink', ...$qualityAssurance]),
            new Scenario('kitchen-sink-php-array', 'acme/php-array-kitchen-sink-bundle', ConfigFormat::PhpArray, ['config', 'kitchen-sink', ...$qualityAssurance]),
            new Scenario('kitchen-sink-yaml', 'my-vendor/yaml-kitchen-sink-bundle', ConfigFormat::Yaml, ['config', 'kitchen-sink', ...$qualityAssurance]),
        ];

        return array_combine(array_map(static fn (Scenario $scenario): string => $scenario->name, $scenarios), $scenarios);
    }

    /**
     * The built-in features plus the test-only kitchen-sink feature (last of its group).
     */
    public static function createFeatureRegistry(): FeatureRegistry
    {
        $features = [];
        foreach (FeatureGroup::cases() as $group) {
            array_push($features, ...FeatureRegistry::createDefault()->getByGroup($group));
            if (FeatureGroup::Features === $group) {
                $features[] = new KitchenSinkFeature();
            }
        }

        return new FeatureRegistry(...$features);
    }

    public static function createGenerator(FeatureRegistry $features): BundleGenerator
    {
        return new BundleGenerator(CoreContributors::createDefault(self::YEAR), $features, new TemplateRenderer([\dirname(__DIR__, 2).'/templates', KitchenSinkFeature::TEMPLATE_DIR]));
    }
}
