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

use Tito10047\BundleMaker\Template\Exception\TemplateNotFoundException;
use Tito10047\BundleMaker\Template\Exception\TemplateRenderingException;

/**
 * Renders native PHP templates ("*.tpl" files) in an isolated scope.
 *
 * Templates are plain PHP: parameters become local variables, output is captured
 * through output buffering. Any PHP notice or warning raised while rendering
 * (e.g. an undefined variable) aborts the rendering with an exception, unless
 * error_reporting() excludes it (e.g. silenced with "@"). Deprecations are left
 * to the previous error handler.
 */
final class TemplateRenderer
{
    public const EXTENSION = '.tpl';

    /** @var non-empty-list<string> */
    private readonly array $templateDirs;

    /**
     * @param list<string> $templateDirs directories searched in order; defaults to the templates/ directory of this package
     */
    public function __construct(array $templateDirs = [])
    {
        $dirs = array_map(static fn (string $dir): string => rtrim($dir, '/'), $templateDirs);
        $this->templateDirs = [] === $dirs ? [\dirname(__DIR__, 2).'/templates'] : $dirs;
    }

    /**
     * @param string               $template   path relative to the template directory, e.g. "core/src/Bundle.php.tpl"
     * @param array<string, mixed> $parameters variables exposed to the template
     *
     * @throws TemplateNotFoundException
     * @throws TemplateRenderingException
     */
    public function render(string $template, array $parameters = []): string
    {
        $path = $this->locate($template);

        foreach (array_keys($parameters) as $name) {
            if (1 !== preg_match('/^[a-zA-Z][a-zA-Z0-9_]*$/', $name) || 'this' === $name) {
                throw new TemplateRenderingException(\sprintf('Invalid parameter name "%s" for template "%s": it must be a valid PHP variable name.', $name, $template));
            }
        }

        $level = ob_get_level();
        ob_start();
        $previousHandler = null;
        $previousHandler = set_error_handler(static function (int $severity, string $message, string $file, int $line) use (&$previousHandler): bool {
            if (0 === (error_reporting() & $severity) || \E_DEPRECATED === $severity || \E_USER_DEPRECATED === $severity) {
                return null !== $previousHandler && false !== $previousHandler($severity, $message, $file, $line);
            }

            throw new \ErrorException($message, 0, $severity, $file, $line);
        });

        try {
            self::evaluate($path, $parameters);

            return (string) ob_get_clean();
        } catch (\Throwable $e) {
            while (ob_get_level() > $level) {
                ob_end_clean();
            }

            if ($e instanceof TemplateRenderingException) {
                throw $e;
            }

            throw new TemplateRenderingException(\sprintf('An error occurred while rendering the template "%s": %s', $template, $e->getMessage()), previous: $e);
        } finally {
            restore_error_handler();
        }
    }

    private function locate(string $template): string
    {
        if (!str_ends_with($template, self::EXTENSION) || str_starts_with($template, '/') || \in_array('..', explode('/', $template), true)) {
            throw new TemplateNotFoundException(\sprintf('Invalid template name "%s": it must be a path relative to the template directory ending with "%s".', $template, self::EXTENSION));
        }

        foreach ($this->templateDirs as $dir) {
            if (is_file($path = $dir.'/'.$template)) {
                return $path;
            }
        }

        throw new TemplateNotFoundException(\sprintf('Template "%s" not found in "%s".', $template, implode('", "', $this->templateDirs)));
    }

    /**
     * Static scope: templates have no access to $this.
     *
     * @param array<string, mixed> $__parameters
     */
    private static function evaluate(string $__path, array $__parameters): void
    {
        extract($__parameters, \EXTR_SKIP);
        unset($__parameters);

        include $__path;
    }
}
