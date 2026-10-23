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
 * Parameters, services and aliases rendered into config/services.{php,yaml}
 * in the configuration format chosen by the user.
 *
 * Naming rules of the best practices are enforced: parameters and service ids are
 * prefixed with the bundle alias ("acme_blog."), internal services with a dot (".acme_blog.").
 *
 * @see https://symfony.com/doc/current/bundles/best_practices.html#services
 *
 * @phpstan-type ConfigScalar string|int|float|bool|null
 * @phpstan-type ConfigValue ConfigScalar|list<ConfigScalar>
 */
final class ServiceConfiguration
{
    /** @var array<string, ConfigValue> */
    private array $parameters = [];
    /** @var array<string, ServiceDefinition> */
    private array $services = [];
    /** @var array<string, ServiceAlias> */
    private array $aliases = [];

    public function __construct(private readonly string $alias)
    {
    }

    /**
     * @param ConfigValue $value
     */
    public function setParameter(string $name, string|int|float|bool|array|null $value): void
    {
        if (1 !== preg_match('/^'.preg_quote($this->alias, '/').'\.[a-z0-9_]+(?:\.[a-z0-9_]+)*$/', $name)) {
            throw new \InvalidArgumentException(\sprintf('The parameter "%s" must be prefixed with the bundle alias, e.g. "%s.%s".', $name, $this->alias, 'some_option'));
        }

        if (isset($this->parameters[$name])) {
            throw new \LogicException(\sprintf('The parameter "%s" is already defined.', $name));
        }

        $this->parameters[$name] = $value;
    }

    public function add(ServiceDefinition $service): void
    {
        if (!Identifiers::isPrefixedServiceId($service->id, $this->alias)) {
            throw new \InvalidArgumentException(\sprintf('The service id "%s" must be prefixed with the bundle alias: "%2$s.<name>", or ".%2$s.<name>" for internal services.', $service->id, $this->alias));
        }

        $this->assertIdIsAvailable($service->id);
        $this->services[$service->id] = $service;
    }

    public function addAlias(ServiceAlias $alias): void
    {
        if (!Identifiers::isPrefixedServiceId($alias->id, $this->alias) && !Identifiers::isNamespacedClassName($alias->id)) {
            throw new \InvalidArgumentException(\sprintf('The alias "%s" must be a namespaced class or interface name, or an id prefixed with the bundle alias "%s.".', $alias->id, $this->alias));
        }

        if (!Identifiers::isPrefixedServiceId($alias->target, $this->alias)) {
            throw new \InvalidArgumentException(\sprintf('The alias "%s" must target a service of the bundle, "%s" given.', $alias->id, $alias->target));
        }

        $this->assertIdIsAvailable($alias->id);
        $this->aliases[$alias->id] = $alias;
    }

    /**
     * @return array<string, ConfigValue>
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @return array<string, ServiceDefinition>
     */
    public function getServices(): array
    {
        return $this->services;
    }

    /**
     * @return array<string, ServiceAlias>
     */
    public function getAliases(): array
    {
        return $this->aliases;
    }

    /**
     * Every class referenced by service definitions and aliases, to build "use" statements.
     *
     * @return list<string>
     */
    public function getReferencedClasses(): array
    {
        $classes = array_map(static fn (ServiceDefinition $service): string => $service->class, array_values($this->services));
        foreach ($this->aliases as $alias) {
            if (Identifiers::isNamespacedClassName($alias->id)) {
                $classes[] = $alias->id;
            }
        }

        return array_values(array_unique($classes));
    }

    public function hasArgumentOfType(ArgumentType $type): bool
    {
        foreach ($this->services as $service) {
            foreach ($service->arguments as $argument) {
                if ($type === $argument->type) {
                    return true;
                }
            }
        }

        return false;
    }

    public function isEmpty(): bool
    {
        return [] === $this->parameters && [] === $this->services && [] === $this->aliases;
    }

    /**
     * @throws \LogicException when an alias targets an undefined service
     */
    public function validate(): void
    {
        foreach ($this->aliases as $alias) {
            if (!isset($this->services[$alias->target]) && !isset($this->aliases[$alias->target])) {
                throw new \LogicException(\sprintf('The alias "%s" targets the undefined service "%s".', $alias->id, $alias->target));
            }
        }
    }

    private function assertIdIsAvailable(string $id): void
    {
        if (isset($this->services[$id]) || isset($this->aliases[$id])) {
            throw new \LogicException(\sprintf('The service or alias "%s" is already defined.', $id));
        }
    }
}
