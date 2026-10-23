<?php
use Tito10047\BundleMaker\Contributor\Core\GreeterContributor;

/**
 * @var Tito10047\BundleMaker\Naming\BundleNaming     $naming
 * @var Tito10047\BundleMaker\Template\TemplateHelper $helper
 */
$imports = $helper->imports([
    $naming->testsNamespace.'\App\TestKernel',
    GreeterContributor::interfaceName($naming),
    'PHPUnit\Framework\Attributes\DataProvider',
    'Symfony\Bundle\FrameworkBundle\Test\KernelTestCase',
    'Symfony\Component\Config\Definition\Exception\InvalidConfigurationException',
], $naming->testsNamespace.'\Integration');
$alias = $helper->php($naming->alias);
?>
<?= "<?php\n" ?>

namespace <?= $naming->testsNamespace ?>\Integration;

<?= $imports->render() ?>

/**
 * Boots the test application with the given configuration of the bundle.
 */
final class ConfigurationTest extends KernelTestCase
{
    public function testGreetingIsPassedToTheGreeter(): void
    {
        self::bootKernel(['extension_configs' => [<?= $alias ?> => ['greeting' => 'Hi']]]);

        $greeter = self::getContainer()->get('test.'.GreeterInterface::class);

        $this->assertInstanceOf(GreeterInterface::class, $greeter);
        $this->assertSame('Hi Jane!', $greeter->greet('Jane'));
    }

    /**
     * @param array<string, mixed> $config
     */
    #[DataProvider('provideInvalidConfigurations')]
    public function testInvalidConfigurationIsRejected(array $config, string $expectedMessage): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage($expectedMessage);

        self::bootKernel(['extension_configs' => [<?= $alias ?> => $config]]);
    }

    /**
     * @return iterable<string, array{array<string, mixed>, string}>
     */
    public static function provideInvalidConfigurations(): iterable
    {
        yield 'empty greeting' => [['greeting' => ''], 'The path "<?= $naming->alias ?>.greeting" cannot contain an empty value'];
        yield 'unknown option' => [['unknown' => true], 'Unrecognized option "unknown" under "<?= $naming->alias ?>"'];
    }

    /**
     * Supports the "extension_configs" option, see TestKernel::__construct().
     *
     * @param array<mixed> $options
     */
    protected static function createKernel(array $options = []): TestKernel
    {
        return new TestKernel(extensionConfigs: $options['extension_configs'] ?? []);
    }
}
