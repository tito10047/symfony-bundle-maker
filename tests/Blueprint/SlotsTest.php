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

namespace Tito10047\BundleMaker\Tests\Blueprint;

use PHPUnit\Framework\TestCase;
use Tito10047\BundleMaker\Blueprint\ListSlot;
use Tito10047\BundleMaker\Blueprint\Partial;
use Tito10047\BundleMaker\Blueprint\Slot;
use Tito10047\BundleMaker\Blueprint\Slots;

final class SlotsTest extends TestCase
{
    public function testOrdersPartialsByPriorityThenRegistration(): void
    {
        $slots = new Slots();
        $slots->addPartial(Slot::BundleLoadExtension, 'a.tpl');
        $slots->addPartial(Slot::BundleLoadExtension, 'b.tpl', ['x' => 1], 5);
        $slots->addPartial(Slot::BundleLoadExtension, 'c.tpl');
        $slots->addPartial(Slot::BundleLoadExtension, 'd.tpl', priority: -1);

        self::assertEquals([
            new Partial('b.tpl', ['x' => 1], 5),
            new Partial('a.tpl'),
            new Partial('c.tpl'),
            new Partial('d.tpl', [], -1),
        ], $slots->getPartials(Slot::BundleLoadExtension));
        self::assertTrue($slots->hasPartials(Slot::BundleLoadExtension));
        self::assertFalse($slots->hasPartials(Slot::BundleConfigure));
        self::assertSame([], $slots->getPartials(Slot::BundleConfigure));
    }

    public function testKeepsUniqueValuesInRegistrationOrder(): void
    {
        $slots = new Slots();
        $slots->addValue(ListSlot::BundleUses, 'B', 'A');
        $slots->addValue(ListSlot::BundleUses, 'B', 'C');

        self::assertSame(['B', 'A', 'C'], $slots->getValues(ListSlot::BundleUses));
        self::assertSame([], $slots->getValues(ListSlot::GitIgnore));
    }

    public function testKeepsNumericValuesAsStrings(): void
    {
        $slots = new Slots();
        $slots->addValue(ListSlot::ReadmeBadges, '8', '8', '1.5');

        self::assertSame(['8', '1.5'], $slots->getValues(ListSlot::ReadmeBadges));
    }

    public function testRejectsEmptyValues(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Values of the "gitignore" slot must not be empty.');

        (new Slots())->addValue(ListSlot::GitIgnore, '');
    }

    /**
     * A slot that no master template renders is a bug: contributions to it would be silently lost.
     *
     * Core templates render the slots every bundle has. A slot owned by a feature (e.g. the
     * PHPStan slot) is rendered by the templates of that feature only: its documentation
     * must say so ("when the "<feature>" feature is enabled"), so contributors know it is
     * conditional, and templates/features/<feature>/ must render it.
     */
    public function testEverySlotIsRenderedByAMasterTemplate(): void
    {
        foreach (Slot::cases() as $slot) {
            self::assertSlotIsRendered($slot, '/\$slots->(render|renderEach)\(Slot::'.$slot->name.'\b/');
        }
        foreach (ListSlot::cases() as $slot) {
            self::assertSlotIsRendered($slot, '/\$slots->values\(ListSlot::'.$slot->name.'\)/');
        }
    }

    private static function assertSlotIsRendered(Slot|ListSlot $slot, string $pattern): void
    {
        $name = $slot::class.'::'.$slot->name;
        $docComment = (string) (new \ReflectionEnumBackedCase($slot::class, $slot->name))->getDocComment();
        $owner = 1 === preg_match('/when the "([a-z][a-z0-9-]*)" feature is enabled/', $docComment, $matches) ? $matches[1] : null;

        if (null === $owner) {
            self::assertMatchesRegularExpression($pattern, self::readTemplates('core'), $name.' must be rendered by a core template.');

            return;
        }

        self::assertDirectoryExists(self::templatesDir().'/features/'.$owner, $name.' is owned by an unknown feature.');
        self::assertMatchesRegularExpression($pattern, self::readTemplates('features/'.$owner), $name.' must be rendered by the templates of the "'.$owner.'" feature.');
    }

    private static function readTemplates(string $directory): string
    {
        $templates = '';
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(self::templatesDir().'/'.$directory, \FilesystemIterator::SKIP_DOTS));
        foreach ($iterator as $file) {
            if ($file instanceof \SplFileInfo && $file->isFile()) {
                $templates .= file_get_contents($file->getPathname());
            }
        }

        return $templates;
    }

    private static function templatesDir(): string
    {
        return \dirname(__DIR__, 2).'/templates';
    }
}
