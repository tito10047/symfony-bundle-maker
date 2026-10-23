<?php

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
