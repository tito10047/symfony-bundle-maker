<?php
/**
 * @var Tito10047\BundleMaker\Naming\BundleNaming     $naming
 * @var Tito10047\BundleMaker\Template\TemplateHelper $helper
 * @var list<string>                                  $messages
 */
?>
<?= "<?php\n" ?>

namespace <?= $naming->testsNamespace ?>\Integration;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Translation\TranslatorBagInterface;
use Symfony\Component\Translation\Util\XliffUtils;

final class TranslationsTest extends KernelTestCase
{
    private const DOMAIN = <?= $helper->php($naming->translationDomain) ?>;

    public function testMessagesAreLoadedInTheDomainOfTheBundle(): void
    {
        $translator = self::getContainer()->get('translator');
        $this->assertInstanceOf(TranslatorBagInterface::class, $translator);

        $this->assertSame(<?= $helper->php(array_combine($messages, $messages)) ?>, $translator->getCatalogue('en')->all(self::DOMAIN));
    }

    public function testTranslationFilesAreValidXliff(): void
    {
        foreach (glob(\dirname(__DIR__, 2).'/translations/*.xlf') ?: [] as $file) {
            $document = new \DOMDocument();
            $document->load($file);

            $this->assertSame([], XliffUtils::validateSchema($document), $file);
        }
    }
}
