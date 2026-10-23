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
 * Routes rendered into config/routes.{php,yaml} in the configuration format chosen by the user.
 * The routing file is only generated when at least one route is defined.
 *
 * @see https://symfony.com/doc/current/bundles/best_practices.html#routing
 */
final class RoutingConfiguration
{
    /** @var array<string, RouteDefinition> */
    private array $routes = [];

    public function __construct(private readonly string $alias)
    {
    }

    public function add(RouteDefinition $route): void
    {
        if (1 !== preg_match('/^'.preg_quote($this->alias, '/').'_[a-z0-9_]+$/', $route->name)) {
            throw new \InvalidArgumentException(\sprintf('The route name "%s" must be prefixed with the bundle alias, e.g. "%s_hello".', $route->name, $this->alias));
        }

        if (isset($this->routes[$route->name])) {
            throw new \LogicException(\sprintf('The route "%s" is already defined.', $route->name));
        }

        $this->routes[$route->name] = $route;
    }

    /**
     * @return array<string, RouteDefinition>
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    public function isEmpty(): bool
    {
        return [] === $this->routes;
    }
}
