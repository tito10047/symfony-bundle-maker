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

namespace Tito10047\BundleMaker\Blueprint;

/**
 * Composer contributions: dependencies, keywords, scripts and allowed plugins.
 *
 * The final composer.json is written once from this manifest and installed with
 * a single "composer update".
 */
final class ComposerManifest
{
    /** Aggregated script running every script flagged with "qa: true". */
    public const QA_SCRIPT = 'qa';

    /** Script running the test suite: the CI workflow runs it on every PHP and Symfony version it tests. */
    public const TEST_SCRIPT = 'test';

    /** Description of the aggregated "qa" script. */
    public const QA_SCRIPT_DESCRIPTION = 'Runs every quality check';

    /** @var array<string, string> */
    private array $require = [];
    /** @var array<string, string> */
    private array $requireDev = [];
    /** @var array<string, string> */
    private array $conflict = [];
    /** @var array<string, string> */
    private array $suggest = [];
    /** @var array<string, true> */
    private array $keywords = [];
    /** @var array<string, ComposerScript> */
    private array $scripts = [];
    /** @var array<string, true> */
    private array $allowedPlugins = [];

    /**
     * Adds a runtime dependency. Requiring the same package twice with the same constraint is a no-op;
     * a package already required for development is promoted to a runtime dependency.
     *
     * @throws \LogicException on conflicting constraints
     */
    public function require(string $package, string $constraint): void
    {
        if (isset($this->requireDev[$package])) {
            self::add($this->requireDev, $package, $constraint);
            unset($this->requireDev[$package]);
        }

        self::add($this->require, $package, $constraint);
    }

    /**
     * Adds a development dependency. If the package is already a runtime dependency, this is a no-op
     * as long as the constraints match (a runtime dependency is also available during development).
     *
     * @throws \LogicException on conflicting constraints
     */
    public function requireDev(string $package, string $constraint): void
    {
        if (isset($this->require[$package])) {
            self::add($this->require, $package, $constraint);

            return;
        }

        self::add($this->requireDev, $package, $constraint);
    }

    /**
     * Declares versions of a package the bundle does not work with, e.g. too old versions of an optional dependency.
     *
     * @throws \LogicException on conflicting constraints
     */
    public function conflict(string $package, string $constraint): void
    {
        self::add($this->conflict, $package, $constraint);
    }

    /**
     * Suggests an optional package, e.g. the integration the bundle provides for it.
     */
    public function suggest(string $package, string $reason): void
    {
        $this->suggest[$package] = $reason;
    }

    public function addKeyword(string $keyword): void
    {
        $this->keywords[$keyword] = true;
    }

    /**
     * @param string|non-empty-list<string> $commands
     * @param bool                          $qa       whether the aggregated "composer qa" script runs it
     */
    public function addScript(string $name, string|array $commands, ?string $description = null, bool $qa = false): void
    {
        if (self::QA_SCRIPT === $name) {
            throw new \LogicException(\sprintf('The "%s" script is generated from the scripts flagged with "qa: true".', self::QA_SCRIPT));
        }

        if (isset($this->scripts[$name])) {
            throw new \LogicException(\sprintf('The Composer script "%s" is already defined.', $name));
        }

        $this->scripts[$name] = new ComposerScript($name, \is_string($commands) ? [$commands] : $commands, $description, $qa);
    }

    public function allowPlugin(string $package): void
    {
        $this->allowedPlugins[$package] = true;
    }

    /**
     * The constraint the package is required with, for runtime or for development; null when it is not required.
     */
    public function getConstraint(string $package): ?string
    {
        return $this->require[$package] ?? $this->requireDev[$package] ?? null;
    }

    public function hasScript(string $name): bool
    {
        return isset($this->scripts[$name]);
    }

    /**
     * @return array<string, string> sorted like Composer's "sort-packages" (platform packages first)
     */
    public function getRequire(): array
    {
        return self::sortPackages($this->require);
    }

    /**
     * @return array<string, string> sorted like Composer's "sort-packages" (platform packages first)
     */
    public function getRequireDev(): array
    {
        return self::sortPackages($this->requireDev);
    }

    /**
     * @return array<string, string>
     */
    public function getConflict(): array
    {
        return self::sortPackages($this->conflict);
    }

    /**
     * @return array<string, string>
     */
    public function getSuggest(): array
    {
        return self::sortPackages($this->suggest);
    }

    /**
     * @return list<string>
     */
    public function getKeywords(): array
    {
        return array_keys($this->keywords);
    }

    /**
     * @return array<string, ComposerScript>
     */
    public function getScripts(): array
    {
        return $this->scripts;
    }

    /**
     * Names of the scripts run by the aggregated "qa" script, in registration order.
     *
     * @return list<string>
     */
    public function getQaScriptNames(): array
    {
        return array_keys(array_filter($this->scripts, static fn (ComposerScript $script): bool => $script->qa));
    }

    /**
     * @return list<string>
     */
    public function getAllowedPlugins(): array
    {
        $plugins = array_keys($this->allowedPlugins);
        sort($plugins);

        return $plugins;
    }

    /**
     * @param array<string, string> $packages
     */
    private static function add(array &$packages, string $package, string $constraint): void
    {
        if (1 !== preg_match('{^(?:php|(?:ext|lib)-[a-z0-9._-]+|[a-z0-9]([_.-]?[a-z0-9]+)*/[a-z0-9](([_.]|-{1,2})?[a-z0-9]+)*)$}', $package)) {
            throw new \InvalidArgumentException(\sprintf('The package name "%s" is invalid.', $package));
        }

        if (isset($packages[$package]) && $packages[$package] !== $constraint) {
            throw new \LogicException(\sprintf('The package "%s" is required with conflicting constraints "%s" and "%s".', $package, $packages[$package], $constraint));
        }

        $packages[$package] = $constraint;
    }

    /**
     * @param array<string, string> $packages
     *
     * @return array<string, string>
     */
    private static function sortPackages(array $packages): array
    {
        uksort($packages, static function (string $a, string $b): int {
            return [self::packageWeight($a), $a] <=> [self::packageWeight($b), $b];
        });

        return $packages;
    }

    private static function packageWeight(string $package): int
    {
        return match (true) {
            'php' === $package => 0,
            str_starts_with($package, 'ext-') => 1,
            str_starts_with($package, 'lib-') => 2,
            default => 3,
        };
    }
}
