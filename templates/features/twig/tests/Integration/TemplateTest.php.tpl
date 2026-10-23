<?php
use Tito10047\BundleMaker\Feature\TranslationsFeature;
use Tito10047\BundleMaker\Feature\TwigFeature;

/**
 * @var Tito10047\BundleMaker\Blueprint\BundleBlueprint $blueprint
 * @var Tito10047\BundleMaker\Naming\BundleNaming       $naming
 * @var Tito10047\BundleMaker\Template\TemplateHelper   $helper
 */
$template = $helper->php(TwigFeature::templateName($naming));
?>
<?= "<?php\n" ?>

namespace <?= $naming->testsNamespace ?>\Integration;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Twig\Environment;

final class TemplateTest extends KernelTestCase
{
    public function testRendersTheGreeting(): void
    {
        $html = $this->render(['greeting' => 'Hello Jane!']);

        $this->assertStringContainsString('Hello Jane!', $html);
<?php if ($blueprint->hasFeature(TranslationsFeature::NAME)): ?>
        $this->assertStringContainsString(<?= $helper->php(TranslationsFeature::TITLE_MESSAGE) ?>, $html);
<?php endif ?>
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

        return $twig->render(<?= $template ?>, $context);
    }
}
