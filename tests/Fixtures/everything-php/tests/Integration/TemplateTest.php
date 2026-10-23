<?php

namespace Zeta\EverythingPhpBundle\Tests\Integration;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Twig\Environment;

final class TemplateTest extends KernelTestCase
{
    public function testRendersTheGreeting(): void
    {
        $html = $this->render(['greeting' => 'Hello Jane!']);

        $this->assertStringContainsString('Hello Jane!', $html);
        $this->assertStringContainsString('Welcome!', $html);
        $this->assertStringContainsString('data-controller="zeta--everything-php-bundle--hello"', $html);
        $this->assertStringContainsString('data-action="zeta--everything-php-bundle--hello#close"', $html);
    }

    public function testEscapesTheGreeting(): void
    {
        $html = $this->render(['greeting' => 'Hello <b>Jane</b>!']);

        $this->assertStringContainsString('Hello &lt;b&gt;Jane&lt;/b&gt;!', $html);
    }

    /**
     * @param array<string, mixed> $context
     */
    private function render(array $context): string
    {
        $twig = self::getContainer()->get('twig');
        $this->assertInstanceOf(Environment::class, $twig);

        return $twig->render('@ZetaEverythingPhp/hello.html.twig', $context);
    }
}
