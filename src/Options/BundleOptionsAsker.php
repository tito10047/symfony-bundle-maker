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

use Composer\IO\IOInterface;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Tito10047\BundleMaker\Feature\FeatureGroup;
use Tito10047\BundleMaker\Feature\FeatureInterface;
use Tito10047\BundleMaker\Feature\FeatureRegistry;
use Tito10047\BundleMaker\Feature\FeatureSelection;
use Tito10047\BundleMaker\Naming\BundleNaming;

/**
 * The bundle assistant: asks the questions, shows a summary of the derived names and
 * asks for a final confirmation (on refusal, questions are asked again with the previous
 * answers as defaults).
 *
 * Default answers, by precedence: previous answer, BUNDLE_MAKER_* preset, computed default.
 * Without interaction, defaults are used as is; they are validated in any case. Every preset
 * is validated before the first question, so a typo never wastes the answers given so far.
 * A preset enabling a feature whose required feature is disabled is only rejected without
 * interaction: otherwise, the required feature is asked first, and the feature only if it is enabled.
 */
final class BundleOptionsAsker
{
    private const FALLBACK_VENDOR = 'acme';
    private const FALLBACK_NAME = 'blog-bundle';

    public function __construct(
        private readonly IOInterface $io,
        private readonly FeatureRegistry $features,
        private readonly string $bundleDir,
        private readonly EnvPresets $presets = new EnvPresets(),
        private readonly GitConfig $git = new GitConfig(),
        private readonly OptionNormalizer $normalizer = new OptionNormalizer(),
        private readonly OptionsSummary $summary = new OptionsSummary(),
    ) {
    }

    /**
     * @throws InvalidPresetException when an environment preset is invalid or unknown
     */
    public function ask(): BundleOptions
    {
        $this->validatePresets();
        $this->io->write(['', '<info>Symfony bundle assistant</info>', '']);

        $previous = null;
        while (true) {
            $options = $this->askOptions($previous);

            $this->io->write($this->summary->render($options, $this->features));

            if ($this->io->askConfirmation('Generate the bundle with these options? [<comment>Y/n</comment>] ', true)) {
                return $options;
            }

            $this->io->write(['', 'Let\'s try again, your previous answers are now the defaults.', '']);
            $previous = $options;
        }
    }

    /**
     * @throws InvalidPresetException
     */
    private function validatePresets(): void
    {
        $this->presets->getValid(EnvPresets::NAME, $this->normalizer->packageName(...));
        $this->presets->getValid(EnvPresets::DESCRIPTION, $this->normalizer->description(...));
        $this->presets->getValid(EnvPresets::AUTHOR_NAME, $this->normalizer->authorName(...));
        $this->presets->getValid(EnvPresets::AUTHOR_EMAIL, $this->normalizer->authorEmail(...));
        $this->presets->getValid(EnvPresets::LICENSE, $this->normalizer->license(...));
        $this->presets->getValid(EnvPresets::CONFIG_FORMAT, $this->normalizer->configFormat(...));

        $known = EnvPresets::RESERVED;
        foreach ($this->features as $feature) {
            $known[] = $variable = EnvPresets::featureVariable($feature->getName());
            $this->presets->getBool($variable);
        }
        $this->presets->getBool(EnvPresets::ALL);

        if ([] !== $unknown = array_values(array_diff($this->presets->getNames(), $known))) {
            throw InvalidPresetException::fromUnknownVariables($unknown, $known);
        }

        // presets are only default answers in interactive mode: the user can still enable the required feature
        if (!$this->io->isInteractive()) {
            $this->validateRequiredFeaturePresets();
        }
    }

    /**
     * A feature enabled by its own variable cannot be enabled when a feature it requires is disabled.
     *
     * @throws InvalidPresetException
     */
    private function validateRequiredFeaturePresets(): void
    {
        foreach ($this->features as $feature) {
            if (true !== $this->presets->getBool($variable = EnvPresets::featureVariable($feature->getName()))) {
                continue;
            }
            foreach ($feature->getRequiredFeatures() as $required) {
                $requiredVariable = EnvPresets::featureVariable($required);
                $disabledBy = match (true) {
                    null !== $this->presets->getBool($requiredVariable) => $requiredVariable,
                    null !== $this->presets->getBool(EnvPresets::ALL) => EnvPresets::ALL,
                    default => null,
                };
                if (null !== $disabledBy && false === $this->presets->getBool($disabledBy)) {
                    throw InvalidPresetException::fromDisabledRequirement($variable, $feature->getName(), $required, $disabledBy);
                }
            }
        }
    }

    private function askOptions(?BundleOptions $previous): BundleOptions
    {
        $this->section('Identity');
        $presetNaming = $this->presets->getValid(EnvPresets::NAME, $this->normalizer->packageName(...));
        $naming = $this->askPackageName($previous->naming->packageName ?? $presetNaming->packageName ?? $this->defaultPackageName());

        // only the user can describe the bundle: the fallback is for the non-interactive mode
        $description = $this->askString(
            'Description',
            $previous->description ?? $this->presets->getValid(EnvPresets::DESCRIPTION, $this->normalizer->description(...)) ?? ($this->io->isInteractive() ? null : $naming->className.' for Symfony'),
            $this->normalizer->description(...),
        );

        $authorName = $this->askString(
            'Author name',
            $previous->author->name ?? $this->presets->getValid(EnvPresets::AUTHOR_NAME, $this->normalizer->authorName(...)) ?? $this->git->get('user.name'),
            $this->normalizer->authorName(...),
        );

        // a "none" preset must not fall back to the Git email
        $authorEmail = $this->askString(
            \sprintf('Author email ("%s" to omit it)', OptionNormalizer::NO_EMAIL),
            match (true) {
                null !== $previous => $previous->author->email,
                null !== $this->presets->get(EnvPresets::AUTHOR_EMAIL) => $this->presets->getValid(EnvPresets::AUTHOR_EMAIL, $this->normalizer->authorEmail(...)),
                default => $this->git->get('user.email'),
            },
            $this->normalizer->authorEmail(...),
        );

        $license = $this->askChoice(
            'License',
            array_combine(License::identifiers(), array_map(static fn (License $license): string => $license->getName(), License::cases())),
            ($previous->license ?? $this->presets->getValid(EnvPresets::LICENSE, $this->normalizer->license(...)) ?? License::Mit)->value,
            $this->normalizer->license(...),
        );

        $this->section('Configuration');
        $configFormat = $this->askChoice(
            'Configuration format',
            array_combine(array_map(static fn (ConfigFormat $format): string => $format->value, ConfigFormat::cases()), array_map(static fn (ConfigFormat $format): string => $format->getDescription(), ConfigFormat::cases())),
            ($previous->configFormat ?? $this->presets->getValid(EnvPresets::CONFIG_FORMAT, $this->normalizer->configFormat(...)) ?? ConfigFormat::Php)->value,
            $this->normalizer->configFormat(...),
        );

        $selection = new FeatureSelection();
        foreach (FeatureGroup::cases() as $group) {
            $features = $this->features->getByGroup($group);
            if ([] === $features) {
                continue;
            }

            $this->section($group->value);
            foreach ($features as $feature) {
                // a feature is not asked, and stays disabled, when a feature it requires is disabled
                $selection = $selection->with($feature->getName(), $selection->allowsFeature($feature) && $this->askFeature($feature, $selection, $previous));
            }
        }

        return new BundleOptions($naming, $description, new Author($authorName, $authorEmail), $license, $configFormat, $selection);
    }

    private function askPackageName(string $default): BundleNaming
    {
        while (true) {
            $naming = $this->askString('Package name (vendor/name)', $default, $this->normalizer->packageName(...));
            if ($naming->hasBundleSuffix()) {
                return $naming;
            }

            $suggestion = $naming->suggestedPackageName();
            $this->io->writeError(\sprintf('  <warning>Bundle package names should end with "-bundle", e.g. "%s".</warning>', $suggestion));

            if (!$this->io->isInteractive() || $this->io->askConfirmation(\sprintf('  Keep "%s" anyway? [<comment>y/N</comment>] ', $naming->packageName), false)) {
                return $naming;
            }

            $default = $suggestion;
        }
    }

    /**
     * @template T
     *
     * @param callable(?string): T $normalizer
     *
     * @return T
     */
    private function askString(string $label, ?string $default, callable $normalizer): mixed
    {
        $question = null === $default || '' === $default
            ? \sprintf('  %s: ', $label)
            : \sprintf('  %s [<comment>%s</comment>]: ', $label, OutputFormatter::escape($default));

        $answer = $this->io->askAndValidate($question, static function (mixed $answer) use ($normalizer): ?string {
            $answer = \is_string($answer) ? $answer : null;
            $normalizer($answer);

            return $answer;
        }, null, $default);

        // without interaction, the default answer is returned as is: normalize (and validate) it here
        return $normalizer(\is_string($answer) ? $answer : null);
    }

    /**
     * Asks for one of the given choices; "?" lists them with their description.
     *
     * @template T
     *
     * @param array<string, string> $choices    descriptions indexed by value
     * @param callable(?string): T  $normalizer
     *
     * @return T
     */
    private function askChoice(string $label, array $choices, string $default, callable $normalizer): mixed
    {
        while (true) {
            $answer = $this->askString(
                $label.' ("?" to list them)',
                $default,
                static fn (?string $answer): mixed => '?' === trim((string) $answer) ? null : $normalizer($answer),
            );
            if (null !== $answer) {
                return $answer;
            }

            $width = max([0, ...array_map(strlen(...), array_keys($choices))]);
            foreach ($choices as $value => $description) {
                $this->io->write(\sprintf('    <comment>%s</comment>  %s', str_pad($value, $width), $description));
            }
        }
    }

    private function askFeature(FeatureInterface $feature, FeatureSelection $answered, ?BundleOptions $previous): bool
    {
        $name = $feature->getName();
        $default = $previous?->hasFeature($name)
            ?? $this->presets->getBool(EnvPresets::featureVariable($name))
            ?? $this->presets->getBool(EnvPresets::ALL)
            ?? $feature->isEnabledByDefault($answered);

        return $this->io->askConfirmation(\sprintf('  %s [<comment>%s</comment>] ', $feature->getQuestion(), $default ? 'Y/n' : 'y/N'), $default);
    }

    /**
     * Without interaction, questions are not printed: neither are their headings and hints.
     */
    private function section(string $title): void
    {
        $this->hint(['', \sprintf(' <info>%s</info>', $title)]);
    }

    /**
     * @param string|list<string> $lines
     */
    private function hint(string|array $lines): void
    {
        if ($this->io->isInteractive()) {
            $this->io->write($lines);
        }
    }

    /**
     * Like "composer init": "<github.user>/<directory name>", with the "-bundle" suffix.
     */
    private function defaultPackageName(): string
    {
        $vendor = self::slugify($this->git->get('github.user')) ?? self::FALLBACK_VENDOR;
        $directory = realpath($this->bundleDir);
        $name = self::slugify(basename(false === $directory ? $this->bundleDir : $directory)) ?? self::FALLBACK_NAME;
        if (!str_ends_with($name, 'bundle')) {
            $name .= '-bundle';
        }

        $packageName = $vendor.'/'.$name;

        return 1 === preg_match(BundleNaming::PACKAGE_NAME_PATTERN, $packageName) && 'bundle' !== $name ? $packageName : self::FALLBACK_VENDOR.'/'.self::FALLBACK_NAME;
    }

    private static function slugify(?string $value): ?string
    {
        $slug = trim((string) preg_replace('/[^a-z0-9]+/', '-', strtolower((string) $value)), '-');

        return 1 === preg_match('/^[a-z]/', $slug) ? $slug : null;
    }
}
