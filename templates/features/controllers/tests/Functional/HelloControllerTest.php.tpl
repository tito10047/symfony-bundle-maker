<?php
use Tito10047\BundleMaker\Contributor\Core\GreeterContributor;
use Tito10047\BundleMaker\Feature\ControllersFeature;

/**
 * @var Tito10047\BundleMaker\Naming\BundleNaming     $naming
 * @var Tito10047\BundleMaker\Template\TemplateHelper $helper
 */
$greeting = GreeterContributor::DEFAULT_GREETING;
?>
<?= "<?php\n" ?>

namespace <?= $naming->testsNamespace ?>\Functional;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Requests the pages of the bundle like a browser does, and only checks the responses.
 */
final class HelloControllerTest extends WebTestCase
{
    #[DataProvider('provideGreetings')]
    public function testGreetsTheNameOfTheUrl(string $url, string $greeting): void
    {
        $client = self::createClient();

        $client->request('GET', $url);

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'text/plain; charset=UTF-8');
        $this->assertSame($greeting, $client->getResponse()->getContent());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function provideGreetings(): iterable
    {
        yield 'name' => ['/hello/Jane', <?= $helper->php($greeting.' Jane!') ?>];
        yield 'several words' => ['/hello/Jane%20Doe', <?= $helper->php($greeting.' Jane Doe!') ?>];
        yield 'no name' => ['/hello', <?= $helper->php($greeting.' '.ControllersFeature::DEFAULT_NAME.'!') ?>];
    }

    public function testRejectsABlankName(): void
    {
        $client = self::createClient();

        $client->request('GET', '/hello/%20');

        $this->assertResponseStatusCodeSame(400);
    }

    public function testOnlyAcceptsGetRequests(): void
    {
        $client = self::createClient();

        $client->request('POST', '/hello/Jane');

        $this->assertResponseStatusCodeSame(405);
    }
}
