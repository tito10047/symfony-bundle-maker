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

use Tito10047\BundleMaker\Blueprint\ListSlot;
use Tito10047\BundleMaker\Blueprint\Slot;
use Tito10047\BundleMaker\Blueprint\Slots;

/**
 * Renders slot contributions inside master templates, exposed to every template as "$slots".
 */
final class SlotRenderer
{
    /** @var array<string, mixed> */
    private array $context = [];

    public function __construct(
        private readonly Slots $slots,
        private readonly TemplateRenderer $renderer,
        private readonly TemplateHelper $helper = new TemplateHelper(),
    ) {
    }

    /**
     * Sets the standard variables given to every partial (on top of its own parameters).
     *
     * @param array<string, mixed> $context
     */
    public function setContext(array $context): void
    {
        $this->context = $context;
    }

    /**
     * Renders the partials of the slot, separated by a blank line and indented with the given
     * number of spaces. The result ends with a newline, or is empty when the slot is empty.
     */
    public function render(Slot $slot, int $indent = 0): string
    {
        $chunks = array_map(static fn (string $chunk): string => $chunk."\n", $this->renderEach($slot));

        return $this->helper->indent(implode("\n", $chunks), $indent);
    }

    /**
     * Renders each partial of the slot separately, for master templates that lay them out themselves
     * (e.g. numbered installation steps). Partials rendering nothing are skipped.
     *
     * @return list<string> without trailing newlines, by priority (highest first, then registration order)
     */
    public function renderEach(Slot $slot): array
    {
        $chunks = [];
        foreach ($this->slots->getPartials($slot) as $partial) {
            if ([] !== $conflicts = array_intersect_key($partial->parameters, $this->context)) {
                throw new \LogicException(\sprintf('The partial "%s" cannot override the standard template variables "%s".', $partial->template, implode('", "', array_keys($conflicts))));
            }

            $content = rtrim($this->renderer->render($partial->template, $partial->parameters + $this->context), "\n");
            if ('' !== $content) {
                $chunks[] = $content;
            }
        }

        return $chunks;
    }

    public function has(Slot $slot): bool
    {
        return $this->slots->hasPartials($slot);
    }

    /**
     * @return list<string> in registration order
     */
    public function values(ListSlot $slot): array
    {
        return $this->slots->getValues($slot);
    }
}
