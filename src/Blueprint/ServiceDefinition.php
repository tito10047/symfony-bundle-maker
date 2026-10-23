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
 * An explicit service definition, following the bundle best practices:
 * no autowiring, no autoconfiguration, private.
 *
 * @see https://symfony.com/doc/current/bundles/best_practices.html#services
 */
final readonly class ServiceDefinition
{
    /**
     * @param string                $id        e.g. "acme_blog.greeter" (public API) or ".acme_blog.cache_warmer" (internal)
     * @param string                $class     fully-qualified class name, without leading backslash
     * @param list<ServiceArgument> $arguments positional constructor arguments
     * @param list<ServiceTag>      $tags
     */
    public function __construct(
        public string $id,
        public string $class,
        public array $arguments = [],
        public array $tags = [],
    ) {
        if (!Identifiers::isClassName($class)) {
            throw new \InvalidArgumentException(\sprintf('The class "%s" of service "%s" is not a valid fully-qualified class name (without leading backslash).', $class, $id));
        }
    }
}
