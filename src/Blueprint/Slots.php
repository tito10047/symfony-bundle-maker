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
 * Contributions to the slots of the shared templates.
 */
final class Slots
{
    /** @var array<string, list<Partial>> */
    private array $partials = [];
    /** @var array<string, array<string, string>> keys are the values, so they are unique; PHP casts numeric ones to int */
    private array $values = [];

    /**
     * @param array<string, mixed> $parameters extra variables for the partial
     * @param int                  $priority   higher priorities are rendered first
     */
    public function addPartial(Slot $slot, string $template, array $parameters = [], int $priority = 0): void
    {
        $this->partials[$slot->value][] = new Partial($template, $parameters, $priority);
    }

    public function addValue(ListSlot $slot, string ...$values): void
    {
        foreach ($values as $value) {
            if ('' === $value) {
                throw new \InvalidArgumentException(\sprintf('Values of the "%s" slot must not be empty.', $slot->value));
            }

            $this->values[$slot->value][$value] = $value;
        }
    }

    /**
     * @return list<Partial> highest priority first, registration order for equal priorities
     */
    public function getPartials(Slot $slot): array
    {
        $partials = $this->partials[$slot->value] ?? [];
        // usort() is stable since PHP 8.0
        usort($partials, static fn (Partial $a, Partial $b): int => $b->priority <=> $a->priority);

        return $partials;
    }

    public function hasPartials(Slot $slot): bool
    {
        return [] !== ($this->partials[$slot->value] ?? []);
    }

    /**
     * @return list<string> in registration order
     */
    public function getValues(ListSlot $slot): array
    {
        return array_values($this->values[$slot->value] ?? []);
    }
}
