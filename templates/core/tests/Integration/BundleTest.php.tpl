<?php
use Tito10047\BundleMaker\Contributor\Core\GreeterContributor;

/**
 * @var Tito10047\BundleMaker\Naming\BundleNaming     $naming
 * @var Tito10047\BundleMaker\Template\TemplateHelper $helper
 */
$imports = $helper->imports([
    GreeterContributor::interfaceName($naming),
    'Symfony\Bundle\FrameworkBundle\Test\KernelTestCase',
], $naming->testsNamespace.'\Integration');
?>
<?= "<?php\n" ?>

namespace <?= $naming->testsNamespace ?>\Integration;

<?= $imports->render() ?>

/**
 * Boots the test application, which registers the bundle.
 */
final class <?= $naming->className ?>Test extends KernelTestCase
{
    public function testGreeterCanBeAutowired(): void
    {
        $greeter = self::getContainer()->get('test.'.GreeterInterface::class);

        $this->assertInstanceOf(GreeterInterface::class, $greeter);
        $this->assertSame(<?= $helper->php(GreeterContributor::DEFAULT_GREETING.' Jane!') ?>, $greeter->greet('Jane'));
    }
}
