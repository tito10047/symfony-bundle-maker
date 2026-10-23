<?php
/**
 * @var Tito10047\BundleMaker\Naming\BundleNaming     $naming
 * @var Tito10047\BundleMaker\Template\TemplateHelper $helper
 */
$imports = $helper->imports([
    $naming->namespace.'\Exception\InvalidArgumentException',
    $naming->namespace.'\Greeter',
    'PHPUnit\Framework\Attributes\DataProvider',
    'PHPUnit\Framework\TestCase',
], $naming->testsNamespace.'\Unit');
?>
<?= "<?php\n" ?>

namespace <?= $naming->testsNamespace ?>\Unit;

<?= $imports->render() ?>

final class GreeterTest extends TestCase
{
    public function testGreetsTheGivenName(): void
    {
        $this->assertSame('Hello Jane!', (new Greeter('Hello'))->greet('Jane'));
    }

    public function testTrimsTheName(): void
    {
        $this->assertSame('Hi Jane!', (new Greeter('Hi'))->greet("  Jane\n"));
    }

    #[DataProvider('provideEmptyNames')]
    public function testRejectsAnEmptyName(string $name): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The name to greet must not be empty.');

        (new Greeter('Hello'))->greet($name);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideEmptyNames(): iterable
    {
        yield 'empty' => [''];
        yield 'whitespace only' => [" \t\n"];
    }
}
