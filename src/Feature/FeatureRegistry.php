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

use Tito10047\BundleMaker\Options\EnvPresets;

/**
 * Ordered collection of the available features: questions are asked, and enabled features
 * contribute to the bundle, in registration order. Features must be registered group by group
 * (in the FeatureGroup order), so the registration order is also the order of the questions.
 *
 * @implements \IteratorAggregate<string, FeatureInterface>
 */
final class FeatureRegistry implements \IteratorAggregate, \Countable
{
    private const NAME_PATTERN = '/^[a-z][a-z0-9]*(?:-[a-z0-9]+)*$/';

    /** @var array<string, FeatureInterface> */
    private readonly array $features;

    public function __construct(FeatureInterface ...$features)
    {
        $indexed = [];
        $groupOrder = array_flip(array_map(static fn (FeatureGroup $group): string => $group->value, FeatureGroup::cases()));
        $currentGroup = 0;
        foreach ($features as $feature) {
            $name = $feature->getName();
            if (1 !== preg_match(self::NAME_PATTERN, $name)) {
                throw new \InvalidArgumentException(\sprintf('The feature name "%s" (%s) must be kebab-case.', $name, $feature::class));
            }

            if (\in_array(EnvPresets::featureVariable($name), EnvPresets::RESERVED, true)) {
                throw new \InvalidArgumentException(\sprintf('The feature name "%s" (%s) is reserved: its environment variable "%s" is already used.', $name, $feature::class, EnvPresets::featureVariable($name)));
            }

            if (isset($indexed[$name])) {
                throw new \LogicException(\sprintf('The feature "%s" is registered twice (%s and %s).', $name, $indexed[$name]::class, $feature::class));
            }

            $group = $groupOrder[$feature->getGroup()->value];
            if ($group < $currentGroup) {
                throw new \LogicException(\sprintf('The feature "%s" must be registered before the features of the "%s" group: features are asked group by group, in registration order.', $name, FeatureGroup::cases()[$currentGroup]->value));
            }

            foreach ($feature->getRequiredFeatures() as $required) {
                if (!isset($indexed[$required])) {
                    throw new \LogicException(\sprintf('The feature "%s" requires the feature "%s", which must be registered before it.', $name, $required));
                }
            }

            $currentGroup = $group;
            $indexed[$name] = $feature;
        }

        $this->features = $indexed;
    }

    /**
     * The built-in features, in the order they are asked.
     */
    public static function createDefault(): self
    {
        return new self(
            new SemanticConfigFeature(),
            new ControllersFeature(),
            new TwigFeature(),
            new TranslationsFeature(),
        );
    }

    public function has(string $name): bool
    {
        return isset($this->features[$name]);
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function get(string $name): FeatureInterface
    {
        return $this->features[$name] ?? throw new \InvalidArgumentException(\sprintf('Unknown feature "%s". Available features: "%s".', $name, implode('", "', array_keys($this->features))));
    }

    /**
     * @return list<FeatureInterface>
     */
    public function getByGroup(FeatureGroup $group): array
    {
        return array_values(array_filter($this->features, static fn (FeatureInterface $feature): bool => $group === $feature->getGroup()));
    }

    /**
     * @return list<FeatureInterface> enabled features, in registration order
     */
    public function getEnabled(FeatureSelection $selection): array
    {
        return array_values(array_filter($this->features, static fn (FeatureInterface $feature): bool => $selection->isEnabled($feature->getName())));
    }

    /**
     * @return \ArrayIterator<string, FeatureInterface>
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->features);
    }

    public function count(): int
    {
        return \count($this->features);
    }
}
