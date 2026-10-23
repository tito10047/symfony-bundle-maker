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

namespace Tito10047\BundleMaker\Options;

use Tito10047\BundleMaker\Feature\FeatureSelection;
use Tito10047\BundleMaker\Naming\BundleNaming;

/**
 * Answers of the bundle assistant: everything needed to generate the bundle.
 */
final readonly class BundleOptions
{
    public function __construct(
        public BundleNaming $naming,
        public string $description,
        public Author $author,
        public License $license,
        public ConfigFormat $configFormat,
        public FeatureSelection $features,
    ) {
    }

    /**
     * Whether the feature is enabled; false for a feature that is not registered, e.g. one of the tests only.
     */
    public function hasFeature(string $feature): bool
    {
        return $this->features->has($feature) && $this->features->isEnabled($feature);
    }
}
