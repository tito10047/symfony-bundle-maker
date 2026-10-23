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

namespace Tito10047\BundleMaker\Tests\Support;

use Tito10047\BundleMaker\Blueprint\BundleBlueprint;
use Tito10047\BundleMaker\Feature\FeatureGroup;
use Tito10047\BundleMaker\Feature\FeatureInterface;
use Tito10047\BundleMaker\Feature\FeatureSelection;

/**
 * Configurable feature for tests.
 */
final class TestFeature implements FeatureInterface
{
    /** @var \Closure(FeatureSelection): bool */
    private readonly \Closure $default;
    /** @var \Closure(BundleBlueprint): void */
    private readonly \Closure $contribution;

    /**
     * @param (\Closure(FeatureSelection): bool)|bool $default
     * @param (\Closure(BundleBlueprint): void)|null  $contribution
     * @param list<string>                            $requiredFeatures
     */
    public function __construct(
        private readonly string $name,
        private readonly FeatureGroup $group = FeatureGroup::Features,
        \Closure|bool $default = false,
        ?\Closure $contribution = null,
        private readonly array $requiredFeatures = [],
    ) {
        $this->default = $default instanceof \Closure ? $default : static fn (): bool => $default;
        $this->contribution = $contribution ?? static function (): void {};
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getGroup(): FeatureGroup
    {
        return $this->group;
    }

    public function getQuestion(): string
    {
        return \sprintf('Enable %s?', $this->name);
    }

    public function isEnabledByDefault(FeatureSelection $previousAnswers): bool
    {
        return ($this->default)($previousAnswers);
    }

    public function getRequiredFeatures(): array
    {
        return $this->requiredFeatures;
    }

    public function contribute(BundleBlueprint $blueprint): void
    {
        ($this->contribution)($blueprint);
    }
}
