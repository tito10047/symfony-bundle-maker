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

namespace Tito10047\BundleMaker\Naming;

/**
 * Every name derived from the Composer package name of the generated bundle.
 *
 * Given "acme/blog-bundle":
 *
 *  - namespace:              Acme\BlogBundle
 *  - class name:             AcmeBlogBundle
 *  - extension alias:        acme_blog (what AbstractBundle derives at runtime)
 *  - translation domain:     AcmeBlog
 *  - Twig namespace:         AcmeBlog (used as "@AcmeBlog/...")
 *  - slug:                   acme-blog (file names, command prefixes, CSS classes...)
 *  - public assets directory: bundles/acmeblog (what "assets:install" creates)
 *
 * @see https://symfony.com/doc/current/bundles/best_practices.html#bundle-name
 */
final readonly class BundleNaming
{
    /**
     * Composer package name restricted to segments that can become PHP identifiers
     * (each vendor and project name starts with a letter).
     */
    public const PACKAGE_NAME_PATTERN = '{^[a-z][a-z0-9]*(?:[_.-][a-z0-9]+)*/[a-z][a-z0-9]*(?:(?:[_.]|-{1,2})[a-z0-9]+)*$}';

    private const BUNDLE_SUFFIX = 'Bundle';

    public string $vendor;
    public string $project;
    public string $vendorNamespace;
    public string $namespace;
    public string $testsNamespace;
    public string $className;
    /** @var class-string */
    public string $fqcn;
    public string $shortName;
    public string $alias;
    public string $translationDomain;
    public string $twigNamespace;
    public string $slug;
    public string $publicAssetsDirectory;

    /**
     * @throws \InvalidArgumentException when the package name cannot be turned into a bundle name
     */
    public function __construct(public string $packageName)
    {
        if (1 !== preg_match(self::PACKAGE_NAME_PATTERN, $packageName)) {
            throw new \InvalidArgumentException(\sprintf('The package name "%s" is invalid: it must be lowercase, in the "vendor/name" format, and both the vendor and the name must start with a letter (e.g. "acme/blog-bundle").', $packageName));
        }

        [$this->vendor, $this->project] = explode('/', $packageName, 2);

        $this->vendorNamespace = Inflector::classify($this->vendor);
        $baseName = Inflector::classify($this->projectWithoutSuffix());

        if ('' === $baseName) {
            throw new \InvalidArgumentException(\sprintf('The package name "%s" is invalid: the name must describe the bundle, not only be "bundle" (e.g. "%s/blog-bundle").', $packageName, $this->vendor));
        }

        $this->namespace = $this->vendorNamespace.'\\'.$baseName.self::BUNDLE_SUFFIX;
        $this->testsNamespace = $this->namespace.'\\Tests';
        $this->shortName = self::startsWithWord($baseName, $this->vendorNamespace) ? $baseName : $this->vendorNamespace.$baseName;
        $this->className = $this->shortName.self::BUNDLE_SUFFIX;
        /** @var class-string $fqcn */
        $fqcn = $this->namespace.'\\'.$this->className;
        $this->fqcn = $fqcn;
        $this->alias = Inflector::underscore($this->shortName);
        $this->translationDomain = $this->shortName;
        $this->twigNamespace = $this->shortName;
        $this->slug = str_replace('_', '-', $this->alias);
        $this->publicAssetsDirectory = 'bundles/'.strtolower($this->shortName);
    }

    /**
     * Whether the package name follows the "-bundle" suffix convention.
     */
    public function hasBundleSuffix(): bool
    {
        return str_ends_with($this->project, '-bundle');
    }

    /**
     * The conventional package name, e.g. "acme/blog-bundle" for "acme/blog".
     */
    public function suggestedPackageName(): string
    {
        return $this->hasBundleSuffix() ? $this->packageName : $this->vendor.'/'.$this->projectWithoutSuffix().'-bundle';
    }

    /**
     * "blog" for "blog-bundle", "blog_bundle", "blogbundle" or "blog".
     */
    private function projectWithoutSuffix(): string
    {
        return (string) preg_replace('/[-_.]*bundle$/', '', $this->project);
    }

    /**
     * Avoids "AcmeAcmeBlogBundle" for "acme/acme-blog-bundle" while keeping "AcmeAcmeblogBundle" for "acme/acmeblog-bundle".
     */
    private static function startsWithWord(string $name, string $prefix): bool
    {
        if (!str_starts_with($name, $prefix)) {
            return false;
        }

        $next = substr($name, \strlen($prefix), 1);

        return '' === $next || ctype_upper($next) || ctype_digit($next);
    }
}
