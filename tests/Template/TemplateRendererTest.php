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
use Tito10047\BundleMaker\Template\TemplateRenderer;

class TemplateRendererTest extends TestCase
{
    public function testRender(): void
    {
        $renderer = new TemplateRenderer(__DIR__.'/../../templates');
        $content = $renderer->render('docs/index.md.template', ['bundle-name' => 'FooBundle']);

        self::assertSame('# FooBundle', trim($content));
    }
}
