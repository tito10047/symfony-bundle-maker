<?php

namespace Acme\EverythingPhpArrayBundle\Tests\Integration;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Translation\TranslatorBagInterface;
use Symfony\Component\Translation\Util\XliffUtils;

final class TranslationsTest extends KernelTestCase
{
    private const DOMAIN = 'AcmeEverythingPhpArray';

    public function testMessagesAreLoadedInTheDomainOfTheBundle(): void
    {
        $translator = self::getContainer()->get('translator');
        $this->assertInstanceOf(TranslatorBagInterface::class, $translator);

        $this->assertSame(['Welcome!' => 'Welcome!', 'Close' => 'Close'], $translator->getCatalogue('en')->all(self::DOMAIN));
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
