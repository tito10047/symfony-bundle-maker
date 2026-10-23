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
 * @see https://symfony.com/doc/current/bundles/best_practices.html#routing
 */
final readonly class RouteDefinition
{
    /**
     * @param string                               $name         prefixed with the bundle alias, e.g. "acme_blog_hello"
     * @param string                               $path         starts with "/", e.g. "/hello/{name}"
     * @param string                               $controller   service id of an invokable controller, or "service_id::method"
     * @param list<string>                         $methods      HTTP methods, uppercase
     * @param array<string, string|int|float|bool> $defaults     default values of the placeholders of the path, e.g. ['name' => 'World']
     * @param array<string, string>                $requirements regular expressions the placeholders of the path must match, e.g. ['name' => '\w+']
     */
    public function __construct(
        public string $name,
        public string $path,
        public string $controller,
        public array $methods = [],
        public array $defaults = [],
        public array $requirements = [],
    ) {
        if (!str_starts_with($path, '/')) {
            throw new \InvalidArgumentException(\sprintf('The path "%s" of route "%s" must start with "/".', $path, $name));
        }

        foreach ($methods as $method) {
            if (1 !== preg_match('/^[A-Z]+$/', $method)) {
                throw new \InvalidArgumentException(\sprintf('The HTTP method "%s" of route "%s" must be uppercase.', $method, $name));
            }
        }

        // the controller is not a default value: it has its own argument
        foreach ([...array_keys($defaults), ...array_keys($requirements)] as $placeholder) {
            if (!str_contains($path, '{'.$placeholder.'}')) {
                throw new \InvalidArgumentException(\sprintf('The route "%s" has a default value or a requirement for "%s", which is not a placeholder of its path "%s".', $name, $placeholder, $path));
            }
        }

        foreach ($requirements as $placeholder => $requirement) {
            if ('' === $requirement || false === @preg_match('{^(?:'.$requirement.')$}', '')) {
                throw new \InvalidArgumentException(\sprintf('The requirement "%s" of the "%s" placeholder of route "%s" is not a valid regular expression.', $requirement, $placeholder, $name));
            }
        }
    }
}
