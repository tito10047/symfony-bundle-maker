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

/**
 * Computes the "use" statements of a generated PHP file and the names to use in its code.
 *
 * Classes of the file namespace are not imported; global classes and classes whose short name
 * is already taken (the first one, alphabetically, is imported) are referenced by their
 * fully-qualified name.
 */
final class PhpImports
{
    /** @var array<string, string> FQCN => name to use in the code */
    private array $names = [];
    /** @var list<string> */
    private array $imports = [];
    /** @var list<string> */
    private array $functions;

    /**
     * @param iterable<string> $classes   fully-qualified class names, without leading backslash
     * @param iterable<string> $functions fully-qualified function names to import with "use function"
     */
    public function __construct(iterable $classes, private readonly ?string $namespace = null, iterable $functions = [])
    {
        $classes = array_values(array_unique(array_map(static fn (string $class): string => ltrim($class, '\\'), [...$classes])));
        usort($classes, self::compare(...));

        $taken = [];
        foreach ($classes as $class) {
            $position = strrpos($class, '\\');
            $shortName = false === $position ? $class : substr($class, $position + 1);
            $classNamespace = false === $position ? null : substr($class, 0, $position);

            if (null === $classNamespace) {
                $this->names[$class] = $this->qualify($class);
            } elseif ($classNamespace === $this->namespace && !isset($taken[strtolower($shortName)])) {
                $this->names[$class] = $shortName;
                $taken[strtolower($shortName)] = true;
            } elseif (isset($taken[strtolower($shortName)])) {
                $this->names[$class] = $this->qualify($class);
            } else {
                $this->names[$class] = $shortName;
                $this->imports[] = $class;
                $taken[strtolower($shortName)] = true;
            }
        }

        $functions = array_values(array_unique([...$functions]));
        usort($functions, self::compare(...));
        $this->functions = $functions;
    }

    /**
     * The name to use in the code for the given class.
     *
     * @throws \InvalidArgumentException when the class has not been registered
     */
    public function name(string $class): string
    {
        return $this->names[ltrim($class, '\\')] ?? throw new \InvalidArgumentException(\sprintf('The class "%s" has not been registered in the imports.', $class));
    }

    /**
     * The "use" statements, one per line, each line ending with a newline; an empty string when there is nothing to import.
     */
    public function render(): string
    {
        $lines = array_map(static fn (string $class): string => 'use '.$class.";\n", $this->imports);
        if ([] !== $lines && [] !== $this->functions) {
            $lines[] = "\n";
        }

        foreach ($this->functions as $function) {
            $lines[] = 'use function '.$function.";\n";
        }

        return implode('', $lines);
    }

    /**
     * Fully-qualified reference: a leading backslash is only needed inside a namespace.
     */
    private function qualify(string $class): string
    {
        return null === $this->namespace ? $class : '\\'.$class;
    }

    /**
     * Same order as the "ordered_imports" rule of PHP-CS-Fixer (alphabetical, case-insensitive).
     */
    private static function compare(string $a, string $b): int
    {
        return strcasecmp(str_replace('\\', ' ', $a), str_replace('\\', ' ', $b));
    }

    public function isEmpty(): bool
    {
        return [] === $this->imports && [] === $this->functions;
    }
}
