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

namespace Tito10047\BundleMaker\Generator;

use Composer\Util\Filesystem;
use Tito10047\BundleMaker\Blueprint\BundleBlueprint;
use Tito10047\BundleMaker\Blueprint\Constraint;
use Tito10047\BundleMaker\Blueprint\FileTemplate;
use Tito10047\BundleMaker\Composer\ComposerJsonWriter;
use Tito10047\BundleMaker\Contributor\CoreContributors;
use Tito10047\BundleMaker\Feature\FeatureRegistry;
use Tito10047\BundleMaker\Options\BundleOptions;
use Tito10047\BundleMaker\Template\SlotRenderer;
use Tito10047\BundleMaker\Template\TemplateHelper;
use Tito10047\BundleMaker\Template\TemplateRenderer;

/**
 * Generates the bundle files in two phases:
 *
 *  1. contribution: core contributors, then the enabled features, describe the bundle in a blueprint;
 *  2. rendering: every file of the blueprint is rendered from its template, in memory;
 *     then the files are written, and composer.json last.
 *
 * Besides contributed files, the configuration files are always derived from the blueprint:
 * config/services.{php,yaml} is always generated, config/routes.{php,yaml} when routes exist
 * (symfony/routing is then required).
 */
final class BundleGenerator
{
    public function __construct(
        private readonly CoreContributors $coreContributors,
        private readonly FeatureRegistry $features,
        private readonly TemplateRenderer $renderer = new TemplateRenderer(),
        private readonly ComposerJsonWriter $composerJsonWriter = new ComposerJsonWriter(),
        private readonly TemplateHelper $helper = new TemplateHelper(),
    ) {
    }

    /**
     * Runs the contribution phase only.
     */
    public function createBlueprint(BundleOptions $options): BundleBlueprint
    {
        $blueprint = new BundleBlueprint($options);

        foreach ($this->coreContributors as $contributor) {
            $contributor->contribute($blueprint);
        }

        foreach ($this->features->getEnabled($options->features) as $feature) {
            $feature->contribute($blueprint);
        }

        $blueprint->services->validate();
        if (!$blueprint->routes->isEmpty()) {
            $blueprint->composer->require('symfony/routing', Constraint::SYMFONY);
        }

        return $blueprint;
    }

    /**
     * Renders every file of the blueprint, without writing anything.
     *
     * @return array<string, string> file contents indexed by path relative to the bundle root, sorted by path
     */
    public function render(BundleBlueprint $blueprint): array
    {
        $slots = new SlotRenderer($blueprint->slots, $this->renderer, $this->helper);
        $context = [
            'options' => $blueprint->options,
            'naming' => $blueprint->naming,
            'blueprint' => $blueprint,
            'slots' => $slots,
            'helper' => $this->helper,
        ];
        $slots->setContext($context);

        $contents = [];
        foreach ($this->resolveFiles($blueprint) as $destination => $file) {
            if ([] !== $conflicts = array_intersect_key($file->parameters, $context)) {
                throw new \LogicException(\sprintf('The parameters of "%s" cannot override the standard template variables "%s".', $destination, implode('", "', array_keys($conflicts))));
            }

            $contents[$destination] = $this->renderer->render($file->template, $file->parameters + $context);
        }

        ksort($contents);

        return $contents;
    }

    /**
     * Generates the bundle into the given directory, composer.json included.
     *
     * @return BundleBlueprint the blueprint the bundle was generated from
     */
    public function generate(BundleOptions $options, string $bundleDir): BundleBlueprint
    {
        $blueprint = $this->createBlueprint($options);
        $this->write($blueprint, $this->render($blueprint), $bundleDir);

        return $blueprint;
    }

    /**
     * Writes the rendered files, then composer.json, into the given directory.
     *
     * @param array<string, string> $files file contents indexed by path relative to the bundle root, see render()
     */
    public function write(BundleBlueprint $blueprint, array $files, string $bundleDir): void
    {
        $filesystem = new Filesystem();

        foreach ($files as $destination => $content) {
            $path = $bundleDir.'/'.$destination;
            $filesystem->ensureDirectoryExists(\dirname($path));

            if (false === @file_put_contents($path, $content)) {
                throw new \RuntimeException(\sprintf('Unable to write the file "%s".', $path));
            }
        }

        $this->composerJsonWriter->write($blueprint, $bundleDir.'/composer.json');
    }

    /**
     * @return array<string, FileTemplate>
     */
    private function resolveFiles(BundleBlueprint $blueprint): array
    {
        $format = $blueprint->options->configFormat;
        $files = $blueprint->getFiles();

        foreach (['services' => true, 'routes' => !$blueprint->routes->isEmpty()] as $baseName => $generated) {
            if ($generated) {
                $fileName = $format->getFileName($baseName);
                $files['config/'.$fileName] = new FileTemplate(\sprintf('core/config/%s/%s.tpl', $format->value, $fileName), 'config/'.$fileName);
            }
        }

        return $files;
    }
}
