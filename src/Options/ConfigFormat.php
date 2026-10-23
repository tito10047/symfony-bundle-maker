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

/**
 * Format of the generated service and routing configuration files.
 *
 * The semantic configuration tree (config/definition.php) is always PHP, whatever the format.
 *
 * @see https://symfony.com/blog/new-in-symfony-7-4-better-php-configuration
 */
enum ConfigFormat: string
{
    /** Fluent PHP configurators (ContainerConfigurator, RoutingConfigurator). */
    case Php = 'php';

    /** PHP files returning a YAML-shaped array (Symfony 7.4+). */
    case PhpArray = 'php-array';

    /** YAML files (requires symfony/yaml). */
    case Yaml = 'yaml';

    public function getDescription(): string
    {
        return match ($this) {
            self::Php => 'fluent PHP configurators',
            self::PhpArray => 'PHP arrays, Symfony 7.4+',
            self::Yaml => 'YAML, requires symfony/yaml',
        };
    }

    /**
     * File extension of the configuration files, without the leading dot.
     */
    public function getFileExtension(): string
    {
        return self::Yaml === $this ? 'yaml' : 'php';
    }

    /**
     * File name of a configuration file in this format, e.g. "services.yaml" for "services".
     */
    public function getFileName(string $baseName): string
    {
        return $baseName.'.'.$this->getFileExtension();
    }
}
