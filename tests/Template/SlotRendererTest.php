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

namespace Tito10047\BundleMaker\Tests\Template;

use PHPUnit\Framework\TestCase;
use Tito10047\BundleMaker\Blueprint\ListSlot;
use Tito10047\BundleMaker\Blueprint\Slot;
use Tito10047\BundleMaker\Blueprint\Slots;
use Tito10047\BundleMaker\Template\SlotRenderer;
use Tito10047\BundleMaker\Template\TemplateRenderer;

final class SlotRendererTest extends TestCase
{
    private Slots $slots;
    private SlotRenderer $renderer;

    protected function setUp(): void
    {
        $this->slots = new Slots();
        $this->renderer = new SlotRenderer($this->slots, new TemplateRenderer([__DIR__.'/templates']));
        $this->renderer->setContext(['naming' => 'ctx']);
    }

    public function testRendersAnEmptySlotAsAnEmptyString(): void
    {
        self::assertFalse($this->renderer->has(Slot::BundleBuild));
        self::assertSame('', $this->renderer->render(Slot::BundleBuild, 8));
    }

    public function testRendersPartialsByPrioritySeparatedByABlankLineAndIndented(): void
    {
        $this->slots->addPartial(Slot::BundleBuild, 'slots/value.txt.tpl', ['value' => 'second']);
        $this->slots->addPartial(Slot::BundleBuild, 'slots/empty.txt.tpl');
        $this->slots->addPartial(Slot::BundleBuild, 'slots/lines.txt.tpl', priority: -5);
        $this->slots->addPartial(Slot::BundleBuild, 'slots/value.txt.tpl', ['value' => 'first'], 10);

        self::assertTrue($this->renderer->has(Slot::BundleBuild));
        self::assertSame("  ctx: first\n\n  ctx: second\n\n  line 1\n  line 2\n", $this->renderer->render(Slot::BundleBuild, 2));
    }

    public function testRendersEachPartialSeparately(): void
    {
        $this->slots->addPartial(Slot::ReadmeInstallationSteps, 'slots/lines.txt.tpl');
        $this->slots->addPartial(Slot::ReadmeInstallationSteps, 'slots/empty.txt.tpl');
        $this->slots->addPartial(Slot::ReadmeInstallationSteps, 'slots/value.txt.tpl', ['value' => 'first'], 10);

        self::assertSame(['ctx: first', "line 1\nline 2"], $this->renderer->renderEach(Slot::ReadmeInstallationSteps));
        self::assertSame([], $this->renderer->renderEach(Slot::DocsSections));
    }

    public function testPartialsCannotOverrideTheContext(): void
    {
        $this->slots->addPartial(Slot::BundleBuild, 'slots/value.txt.tpl', ['naming' => 'x', 'value' => 'y']);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('The partial "slots/value.txt.tpl" cannot override the standard template variables "naming".');

        $this->renderer->render(Slot::BundleBuild);
    }

    public function testExposesListValues(): void
    {
        $this->slots->addValue(ListSlot::GitIgnore, '/b', '/a', '/b');

        self::assertSame(['/b', '/a'], $this->renderer->values(ListSlot::GitIgnore));
        self::assertSame([], $this->renderer->values(ListSlot::BundleUses));
    }
}
