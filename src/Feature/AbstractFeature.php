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

namespace Tito10047\BundleMaker\Feature;

/**
 * Base class of the built-in features: they describe themselves with constants.
 *
 *     final class CommandFeature extends AbstractFeature
 *     {
 *         public const NAME = 'command';
 *         protected const QUESTION = 'Add a console command?';
 *
 *         public function contribute(BundleBlueprint $blueprint): void { ... }
 *     }
 *
 * Override isEnabledByDefault() when the default answer depends on previous answers.
 */
abstract class AbstractFeature implements FeatureInterface
{
    /** @var string see FeatureInterface::getName() */
    public const NAME = '';

    /** @var string see FeatureInterface::getQuestion() */
    protected const QUESTION = '';

    /** @var FeatureGroup */
    protected const GROUP = FeatureGroup::Features;

    /** @var bool see FeatureInterface::isEnabledByDefault() */
    protected const ENABLED_BY_DEFAULT = false;

    /** @var list<string> see FeatureInterface::getRequiredFeatures() */
    protected const REQUIRED_FEATURES = [];

    public function getName(): string
    {
        return static::NAME;
    }

    public function getGroup(): FeatureGroup
    {
        return static::GROUP;
    }

    public function getQuestion(): string
    {
        return static::QUESTION;
    }

    public function isEnabledByDefault(FeatureSelection $previousAnswers): bool
    {
        return static::ENABLED_BY_DEFAULT;
    }

    public function getRequiredFeatures(): array
    {
        return static::REQUIRED_FEATURES;
    }
}
