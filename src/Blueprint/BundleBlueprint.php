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

use Tito10047\BundleMaker\Naming\BundleNaming;
use Tito10047\BundleMaker\Options\BundleOptions;

/**
 * Everything the generated bundle is made of, collected from the contributors
 * (core contributors first, then the enabled features) before anything is written.
 *
 *  - $composer: dependencies, keywords, scripts, plugins of composer.json
 *  - $services: parameters, services and aliases of config/services.{php,yaml}
 *  - $routes:   routes of config/routes.{php,yaml} (generated only when not empty)
 *  - $slots:    partial templates and values injected into the shared templates
 *  - files:     files rendered from templates (addFile())
 */
final class BundleBlueprint
{
    public readonly BundleNaming $naming;
    public readonly ComposerManifest $composer;
    public readonly ServiceConfiguration $services;
    public readonly RoutingConfiguration $routes;
    public readonly Slots $slots;

    /** @var array<string, FileTemplate> */
    private array $files = [];

    public function __construct(public readonly BundleOptions $options)
    {
        $this->naming = $options->naming;
        $this->composer = new ComposerManifest();
        $this->services = new ServiceConfiguration($this->naming->alias);
        $this->routes = new RoutingConfiguration($this->naming->alias);
        $this->slots = new Slots();
    }

    /**
     * Renders the template into the given destination of the generated bundle.
     *
     * @param string               $template    path relative to the templates directory, e.g. "features/twig/hello.html.twig.tpl"
     * @param string               $destination path relative to the bundle root, e.g. "templates/hello.html.twig"
     * @param array<string, mixed> $parameters  extra template variables, on top of the standard ones
     *
     * @throws \LogicException when the destination is already generated, or is derived from the blueprint
     *                         (composer.json, config/services.*, config/routes.*)
     */
    public function addFile(string $template, string $destination, array $parameters = []): void
    {
        $format = $this->options->configFormat;
        $reserved = [
            'composer.json' => '$blueprint->composer',
            'config/'.$format->getFileName('services') => '$blueprint->services',
            'config/'.$format->getFileName('routes') => '$blueprint->routes',
        ];
        if (isset($reserved[$destination])) {
            throw new \LogicException(\sprintf('The file "%s" is generated from the blueprint: use %s instead.', $destination, $reserved[$destination]));
        }

        if (isset($this->files[$destination])) {
            throw new \LogicException(\sprintf('The file "%s" is already generated from the template "%s".', $destination, $this->files[$destination]->template));
        }

        $this->files[$destination] = new FileTemplate($template, $destination, $parameters);
    }

    /**
     * Whether a contributed file is generated inside the given directory (at any depth), e.g. "tests".
     *
     * Files derived from the blueprint (composer.json, config/services.*, config/routes.*) are not
     * contributed: "config" is only reported when a contributor adds a file to it.
     */
    public function hasDirectory(string $path): bool
    {
        $prefix = trim($path, '/').'/';
        foreach ($this->files as $destination => $file) {
            if (str_starts_with($destination, $prefix)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<string, FileTemplate> indexed by destination
     */
    public function getFiles(): array
    {
        return $this->files;
    }

    /**
     * Shortcut for BundleOptions::hasFeature().
     */
    public function hasFeature(string $feature): bool
    {
        return $this->options->hasFeature($feature);
    }
}
