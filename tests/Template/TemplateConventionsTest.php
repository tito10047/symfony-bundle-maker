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

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Tito10047\BundleMaker\Tests\Support\KitchenSinkFeature;

/**
 * PHP removes the newline that directly follows "?>". Templates follow one convention, so that
 * no line is ever joined to the next one by accident:
 *
 *  - a control tag ("<?php if (...): ?>", "<?php endif ?>"...) is alone on its line, at column 0:
 *    it leaves no trace in the output;
 *  - an echo tag ending a line is followed by an empty line, which outputs the newline, unless
 *    it echoes whole lines, which end with a newline themselves: a slot ("$slots->render()")
 *    or a string literal ending with "\n".
 */
final class TemplateConventionsTest extends TestCase
{
    private const WHOLE_LINES = '{<\?= (?:\$slots->render\(.*\)|"[^"]*\\\\n") \?>$}';

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideTemplates(): iterable
    {
        foreach ([\dirname(__DIR__, 2).'/templates', KitchenSinkFeature::TEMPLATE_DIR] as $dir) {
            /** @var \SplFileInfo $file */
            foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS)) as $file) {
                if (str_ends_with($file->getFilename(), '.tpl')) {
                    yield substr($file->getPathname(), \strlen(\dirname($dir)) + 1) => [$file->getPathname()];
                }
            }
        }
    }

    #[DataProvider('provideTemplates')]
    public function testFollowsTheNewlineConvention(string $path): void
    {
        $lines = explode("\n", (string) file_get_contents($path));
        $violations = [];

        foreach ($lines as $i => $line) {
            $next = $lines[$i + 1] ?? '';

            if (!str_starts_with(ltrim($line), '<?=') && str_contains($line, '<?php ') && str_contains($line, '?>') && 1 !== preg_match('{^<\?php [^\n]*\?>$}', $line)) {
                $violations[] = \sprintf('line %d: a control tag must be alone on its line, at column 0: "%s"', $i + 1, $line);
            }

            if (str_ends_with($line, '?>') && str_contains($line, '<?=') && '' !== $next && 1 !== preg_match(self::WHOLE_LINES, trim($line))) {
                $violations[] = \sprintf('line %d: an echo tag ending a line must be followed by an empty line, or it is joined to the next one: "%s"', $i + 1, $line);
            }
        }

        self::assertSame([], $violations);
    }

    /**
     * The order of the imports depends on the names: an import written in a template with a
     * computed name, e.g. a class of the bundle, is out of order for some vendors.
     */
    #[DataProvider('provideTemplates')]
    public function testBuildsTheImportsOfComputedNamesWithTheHelper(string $path): void
    {
        $violations = [];
        foreach (explode("\n", (string) file_get_contents($path)) as $i => $line) {
            if (1 === preg_match('{^use\b.*<\?=}', $line)) {
                $violations[] = \sprintf('line %d: build the imports with $helper->imports(), which sorts them: "%s"', $i + 1, $line);
            }
        }

        self::assertSame([], $violations);
    }
}
