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

use Symfony\Component\Console\Formatter\OutputFormatter;
use Tito10047\BundleMaker\Feature\FeatureGroup;
use Tito10047\BundleMaker\Feature\FeatureInterface;
use Tito10047\BundleMaker\Feature\FeatureRegistry;
use Tito10047\BundleMaker\Feature\TwigFeature;

/**
 * Summary of the answers and of the derived names, shown before the final confirmation.
 */
final class OptionsSummary
{
    /**
     * @return list<string> console lines (with Composer IO formatting tags)
     */
    public function render(BundleOptions $options, FeatureRegistry $features): array
    {
        $naming = $options->naming;
        $author = $options->author->name.(null !== $options->author->email ? ' <'.$options->author->email.'>' : '');

        $rows = [
            'Package' => $naming->packageName,
            'Description' => $options->description,
            'Author' => $author,
            'License' => $options->license->value,
            'Config format' => $options->configFormat->value.' ('.$options->configFormat->getDescription().')',
            'Namespace' => $naming->namespace,
            'Bundle class' => $naming->fqcn,
            'Extension alias' => $naming->alias,
        ];
        if ($options->hasFeature(TwigFeature::NAME)) {
            $rows['Twig namespace'] = '@'.$naming->twigNamespace;
        }

        foreach (FeatureGroup::cases() as $group) {
            $groupFeatures = $features->getByGroup($group);
            if ([] === $groupFeatures) {
                continue;
            }

            $enabled = array_filter($groupFeatures, static fn (FeatureInterface $feature): bool => $options->hasFeature($feature->getName()));
            $rows[$group->value] = [] === $enabled ? 'none' : implode(', ', array_map(static fn (FeatureInterface $feature): string => $feature->getName(), $enabled));
        }

        $width = max(array_map(strlen(...), array_keys($rows)));
        $lines = ['', ' <info>Summary</info>', ''];
        foreach ($rows as $label => $value) {
            $lines[] = \sprintf('  %s  <comment>%s</comment>', str_pad($label, $width), OutputFormatter::escape($value));
        }
        $lines[] = '';

        return $lines;
    }
}
