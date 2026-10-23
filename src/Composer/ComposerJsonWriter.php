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

namespace Tito10047\BundleMaker\Composer;

use Composer\Json\JsonFile;
use Tito10047\BundleMaker\Blueprint\BundleBlueprint;
use Tito10047\BundleMaker\Blueprint\ComposerManifest;

/**
 * Writes the final composer.json of the bundle in one go.
 *
 * Keys owned by the bundle (identity, dependencies, autoloading, scripts) are replaced;
 * any other key of the skeleton is preserved. This plugin is removed from the
 * dependencies and from the allowed plugins.
 */
final class ComposerJsonWriter
{
    public const PLUGIN_PACKAGE = 'tito10047/symfony-bundle-maker';

    /** Order of the keys, as in the composer.json schema documentation. */
    private const KEY_ORDER = [
        'name', 'description', 'license', 'type', 'keywords', 'homepage', 'authors', 'support', 'funding',
        'require', 'require-dev', 'conflict', 'replace', 'provide', 'suggest',
        'autoload', 'autoload-dev', 'repositories', 'minimum-stability', 'prefer-stable',
        'config', 'extra', 'scripts', 'scripts-descriptions',
    ];

    public function write(BundleBlueprint $blueprint, string $file): void
    {
        $json = new JsonFile($file);
        /** @var array<string, mixed> $existing */
        $existing = $json->exists() ? $json->read() : [];

        $json->write($this->build($blueprint, $existing));
    }

    /**
     * @param array<string, mixed> $existing the current composer.json (the skeleton one)
     *
     * @return array<string, mixed>
     */
    public function build(BundleBlueprint $blueprint, array $existing = []): array
    {
        $options = $blueprint->options;
        $naming = $blueprint->naming;
        $composer = $blueprint->composer;

        $author = ['name' => $options->author->name];
        if (null !== $options->author->email) {
            $author['email'] = $options->author->email;
        }

        $manifest = [
            'name' => $naming->packageName,
            'description' => $options->description,
            'license' => $options->license->value,
            'type' => 'symfony-bundle',
            'keywords' => $composer->getKeywords(),
            'authors' => [$author],
            'require' => $composer->getRequire(),
            'require-dev' => $composer->getRequireDev(),
            'conflict' => $composer->getConflict(),
            'suggest' => $composer->getSuggest(),
            'autoload' => ['psr-4' => [$naming->namespace.'\\' => 'src/']],
            'autoload-dev' => ['psr-4' => [$naming->testsNamespace.'\\' => 'tests/']],
            'config' => $this->buildConfig($composer, $existing['config'] ?? []),
        ] + $this->buildScripts($composer);

        unset($existing['require'], $existing['require-dev'], $existing['conflict'], $existing['suggest'], $existing['scripts'], $existing['scripts-descriptions']);
        $manifest += $existing;

        $manifest = array_filter($manifest, static fn (mixed $value): bool => [] !== $value);

        return self::sortKeys($manifest);
    }

    /**
     * @return array<string, mixed>
     */
    private function buildConfig(ComposerManifest $composer, mixed $existing): array
    {
        $config = [];
        foreach (\is_array($existing) ? $existing : [] as $key => $value) {
            $config[(string) $key] = $value;
        }
        $config['sort-packages'] = true;

        $allowPlugins = \is_array($config['allow-plugins'] ?? null) ? $config['allow-plugins'] : [];
        unset($allowPlugins[self::PLUGIN_PACKAGE]);
        foreach ($composer->getAllowedPlugins() as $plugin) {
            $allowPlugins[$plugin] = true;
        }

        unset($config['allow-plugins']);
        if ([] !== $allowPlugins) {
            ksort($allowPlugins);
            $config['allow-plugins'] = $allowPlugins;
        }

        ksort($config);

        return $config;
    }

    /**
     * @return array{scripts?: array<string, string|list<string>>, scripts-descriptions?: array<string, string>}
     */
    private function buildScripts(ComposerManifest $composer): array
    {
        $scripts = [];
        $descriptions = [];
        foreach ($composer->getScripts() as $name => $script) {
            $scripts[$name] = 1 === \count($script->commands) ? $script->commands[0] : $script->commands;
            if (null !== $script->description) {
                $descriptions[$name] = $script->description;
            }
        }

        if ([] !== $qa = $composer->getQaScriptNames()) {
            $scripts[ComposerManifest::QA_SCRIPT] = array_map(static fn (string $name): string => '@'.$name, $qa);
            $descriptions[ComposerManifest::QA_SCRIPT] = ComposerManifest::QA_SCRIPT_DESCRIPTION;
        }

        if ([] === $scripts) {
            return [];
        }

        ksort($scripts);
        ksort($descriptions);

        return [] === $descriptions ? ['scripts' => $scripts] : ['scripts' => $scripts, 'scripts-descriptions' => $descriptions];
    }

    /**
     * @param array<string, mixed> $manifest
     *
     * @return array<string, mixed>
     */
    private static function sortKeys(array $manifest): array
    {
        $order = array_flip(self::KEY_ORDER);
        uksort($manifest, static fn (string $a, string $b): int => [$order[$a] ?? \PHP_INT_MAX, $a] <=> [$order[$b] ?? \PHP_INT_MAX, $b]);

        return $manifest;
    }
}
