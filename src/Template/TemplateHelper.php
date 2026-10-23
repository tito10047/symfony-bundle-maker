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

namespace Tito10047\BundleMaker\Template;

use Tito10047\BundleMaker\Blueprint\ArgumentType;
use Tito10047\BundleMaker\Blueprint\ServiceArgument;
use Tito10047\BundleMaker\Blueprint\ServiceConfiguration;
use Tito10047\BundleMaker\Options\ConfigFormat;

/**
 * Encoding and formatting helpers, exposed to every template as "$helper".
 *
 * Templates must encode every value they did not write themselves with the helper
 * matching the output context: php() in PHP code, yaml() in YAML, xml() in XML.
 *
 * @phpstan-import-type ConfigValue from ServiceConfiguration
 *
 * @phpstan-type Encodable string|int|float|bool|null|array<array-key, string|int|float|bool|null>
 */
final class TemplateHelper
{
    /**
     * Encodes a scalar, null, or a one-level array of them as a PHP literal
     * (single-quoted strings, short array syntax).
     *
     * @param Encodable $value
     */
    public function php(string|int|float|bool|array|null $value): string
    {
        if (!\is_array($value)) {
            return $this->phpScalar($value);
        }

        if (array_is_list($value)) {
            return '['.implode(', ', array_map($this->phpScalar(...), $value)).']';
        }

        $items = [];
        foreach ($value as $key => $item) {
            $items[] = $this->phpScalar($key).' => '.$this->phpScalar($item);
        }

        return '['.implode(', ', $items).']';
    }

    /**
     * Encodes a scalar, null, or a one-level array of them as a YAML flow value
     * (strings are always single-quoted, keys only when needed).
     *
     * @param Encodable $value
     */
    public function yaml(string|int|float|bool|array|null $value): string
    {
        if (!\is_array($value)) {
            return $this->yamlScalar($value);
        }

        if (array_is_list($value)) {
            return '['.implode(', ', array_map($this->yamlScalar(...), $value)).']';
        }

        $items = [];
        foreach ($value as $key => $item) {
            $items[] = $this->yamlKey((string) $key).': '.$this->yamlScalar($item);
        }

        return '{ '.implode(', ', $items).' }';
    }

    /**
     * Encodes a mapping key for YAML: plain when safe (e.g. "acme_blog.greeter", "Acme\Greeter"), single-quoted otherwise.
     */
    public function yamlKey(string $key): string
    {
        $plain = 1 === preg_match('/^[a-zA-Z_.][a-zA-Z0-9_.\\\\-]*$/', $key) && !\in_array(strtolower($key), ['true', 'false', 'null'], true);

        return $plain ? $key : $this->yamlScalar($key);
    }

    /**
     * Escapes a string for XML text and attribute values.
     */
    public function xml(string $value): string
    {
        return htmlspecialchars($value, \ENT_XML1 | \ENT_QUOTES | \ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * Encodes the literal value of a container parameter for the given format.
     *
     * "%" is doubled so the value is never resolved as a parameter; in YAML-shaped formats,
     * a leading "@" is doubled so the value is never resolved as a service reference.
     *
     * @param ConfigValue $value
     */
    public function configValue(string|int|float|bool|array|null $value, ConfigFormat $format): string
    {
        $value = $this->escapeConfigLiteral($value, $format);

        return ConfigFormat::Yaml === $format ? $this->yaml($value) : $this->php($value);
    }

    /**
     * Encodes a service argument for the given format: service('id')/param('name') helpers
     * in the fluent PHP format, "@id"/"%name%" strings in the YAML-shaped formats (YAML and PHP array).
     *
     * The PHP array format does not use the helpers: they are functions declared in the file of
     * ContainerConfigurator, which static analysis cannot autoload.
     */
    public function serviceArgument(ServiceArgument $argument, ConfigFormat $format): string
    {
        return match ($argument->type) {
            ArgumentType::Service => ConfigFormat::Php === $format
                ? 'service('.$this->php($argument->getReference()).')'
                : $this->encode('@'.$argument->getReference(), $format),
            ArgumentType::Parameter => ConfigFormat::Php === $format
                ? 'param('.$this->php($argument->getReference()).')'
                : $this->encode('%'.$argument->getReference().'%', $format),
        };
    }

    /**
     * Indents every non-empty line of the text with the given number of spaces.
     */
    public function indent(string $text, int $spaces): string
    {
        if (0 === $spaces) {
            return $text;
        }

        return (string) preg_replace('/^(?=[^\n])/m', str_repeat(' ', $spaces), $text);
    }

    /**
     * @param iterable<string> $classes   fully-qualified class names
     * @param iterable<string> $functions fully-qualified function names
     */
    public function imports(iterable $classes, ?string $namespace = null, iterable $functions = []): PhpImports
    {
        return new PhpImports($classes, $namespace, $functions);
    }

    private function encode(string $value, ConfigFormat $format): string
    {
        return ConfigFormat::Yaml === $format ? $this->yaml($value) : $this->php($value);
    }

    private function phpScalar(string|int|float|bool|null $value): string
    {
        return match (true) {
            null === $value => 'null',
            \is_bool($value) => $value ? 'true' : 'false',
            \is_string($value) => "'".strtr($value, ['\\' => '\\\\', "'" => "\\'"])."'",
            default => var_export($value, true),
        };
    }

    private function yamlScalar(string|int|float|bool|null $value): string
    {
        return match (true) {
            null === $value => 'null',
            \is_bool($value) => $value ? 'true' : 'false',
            \is_string($value) => "'".str_replace("'", "''", $value)."'",
            default => var_export($value, true),
        };
    }

    /**
     * @param ConfigValue $value
     *
     * @return ConfigValue
     */
    private function escapeConfigLiteral(string|int|float|bool|array|null $value, ConfigFormat $format): string|int|float|bool|array|null
    {
        if (\is_array($value)) {
            return array_map(fn (string|int|float|bool|null $item): string|int|float|bool|null => $this->escapeScalar($item, $format), $value);
        }

        return $this->escapeScalar($value, $format);
    }

    private function escapeScalar(string|int|float|bool|null $value, ConfigFormat $format): string|int|float|bool|null
    {
        if (!\is_string($value)) {
            return $value;
        }

        $value = str_replace('%', '%%', $value);
        if (ConfigFormat::Php !== $format && str_starts_with($value, '@')) {
            $value = '@'.$value;
        }

        return $value;
    }
}
