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
use Tito10047\BundleMaker\Template\Exception\TemplateNotFoundException;
use Tito10047\BundleMaker\Template\Exception\TemplateRenderingException;
use Tito10047\BundleMaker\Template\TemplateRenderer;

final class TemplateRendererTest extends TestCase
{
    private TemplateRenderer $renderer;

    protected function setUp(): void
    {
        $this->renderer = new TemplateRenderer([__DIR__.'/templates']);
    }

    public function testRendersParameters(): void
    {
        self::assertSame("Hello world!\n", $this->renderer->render('hello.txt.tpl', ['name' => 'world']));
    }

    public function testControlStructuresOnTheirOwnLineLeaveNoBlankLine(): void
    {
        self::assertSame("yes\nend\n", $this->renderer->render('control.txt.tpl', ['flag' => true]));
        self::assertSame("end\n", $this->renderer->render('control.txt.tpl', ['flag' => false]));
    }

    public function testTemplatesHaveNoAccessToTheRenderer(): void
    {
        self::assertSame('nested static', $this->renderer->render('nested/scope.txt.tpl'));
    }

    public function testUndefinedVariablesAbortTheRendering(): void
    {
        $level = ob_get_level();
        // like Composer, see Composer\Util\ErrorHandler::register(): PHPUnit lowers it while a test runs
        $errorReporting = error_reporting(\E_ALL);

        try {
            $this->renderer->render('undefined.txt.tpl');
            self::fail('An exception should have been thrown.');
        } catch (TemplateRenderingException $e) {
            self::assertStringContainsString('"undefined.txt.tpl"', $e->getMessage());
            self::assertStringContainsString('Undefined variable $undefined', $e->getMessage());
            self::assertInstanceOf(\ErrorException::class, $e->getPrevious());
        } finally {
            error_reporting($errorReporting);
        }

        self::assertSame($level, ob_get_level(), 'Output buffers must be cleaned.');
    }

    public function testExceptionsAreWrapped(): void
    {
        $this->expectException(TemplateRenderingException::class);
        $this->expectExceptionMessage('An error occurred while rendering the template "throws.txt.tpl": boom');

        $this->renderer->render('throws.txt.tpl');
    }

    public function testErrorHandlerIsRestored(): void
    {
        $sentinel = static fn (): bool => false;
        set_error_handler($sentinel);

        try {
            $this->renderer->render('hello.txt.tpl', ['name' => 'world']);
            try {
                $this->renderer->render('undefined.txt.tpl');
            } catch (TemplateRenderingException) {
            }

            $current = set_error_handler(static fn (): bool => false);
            restore_error_handler();
        } finally {
            restore_error_handler();
        }

        self::assertSame($sentinel, $current);
    }

    public function testDeprecationsAndSilencedErrorsAreLeftToThePreviousErrorHandler(): void
    {
        $errors = [];
        $errorReporting = error_reporting(\E_ALL);
        set_error_handler(static function (int $severity, string $message) use (&$errors): bool {
            $errors[] = [$severity, $message, 0 !== (error_reporting() & $severity)];

            return true;
        });

        try {
            $output = $this->renderer->render('tolerated.txt.tpl');
        } finally {
            restore_error_handler();
            error_reporting($errorReporting);
        }

        self::assertSame('ok ', $output);
        self::assertSame([
            [\E_USER_DEPRECATED, 'Since acme 1.1: old.', true],
            [\E_WARNING, 'Undefined variable $undefined', false],
        ], $errors);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideInvalidParameterNames(): iterable
    {
        yield 'this' => ['this'];
        yield 'leading underscore' => ['__path'];
        yield 'leading digit' => ['1abc'];
        yield 'dash' => ['foo-bar'];
    }

    #[DataProvider('provideInvalidParameterNames')]
    public function testRejectsInvalidParameterNames(string $name): void
    {
        $this->expectException(TemplateRenderingException::class);
        $this->expectExceptionMessage(\sprintf('Invalid parameter name "%s"', $name));

        $this->renderer->render('hello.txt.tpl', [$name => 'x', 'name' => 'world']);
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function provideInvalidTemplateNames(): iterable
    {
        yield 'missing' => ['missing.txt.tpl', 'Template "missing.txt.tpl" not found'];
        yield 'wrong extension' => ['hello.txt', 'ending with ".tpl"'];
        yield 'absolute' => ['/etc/passwd.tpl', 'relative to the template directory'];
        yield 'parent directory' => ['nested/../hello.txt.tpl', 'relative to the template directory'];
    }

    #[DataProvider('provideInvalidTemplateNames')]
    public function testRejectsInvalidTemplates(string $template, string $message): void
    {
        $this->expectException(TemplateNotFoundException::class);
        $this->expectExceptionMessage($message);

        $this->renderer->render($template);
    }

    public function testSearchesDirectoriesInOrder(): void
    {
        $overrides = sys_get_temp_dir().'/bundle-maker-overrides-'.bin2hex(random_bytes(4));
        mkdir($overrides);
        file_put_contents($overrides.'/hello.txt.tpl', 'Overridden <?= $name ?>');

        try {
            $renderer = new TemplateRenderer([$overrides, __DIR__.'/templates']);

            self::assertSame('Overridden world', $renderer->render('hello.txt.tpl', ['name' => 'world']));
            self::assertSame("override\n", $renderer->render('override.txt.tpl'));
        } finally {
            unlink($overrides.'/hello.txt.tpl');
            rmdir($overrides);
        }
    }
}
