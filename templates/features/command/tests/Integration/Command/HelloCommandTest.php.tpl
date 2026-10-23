<?php
use Tito10047\BundleMaker\Feature\CommandFeature;

/**
 * @var Tito10047\BundleMaker\Naming\BundleNaming     $naming
 * @var Tito10047\BundleMaker\Template\TemplateHelper $helper
 */
$imports = $helper->imports([
    'Symfony\Bundle\FrameworkBundle\Console\Application',
    'Symfony\Bundle\FrameworkBundle\Test\KernelTestCase',
    'Symfony\Component\Console\Command\Command',
    'Symfony\Component\Console\Tester\CommandTester',
], $naming->testsNamespace.'\Integration\Command');
?>
<?= "<?php\n" ?>

namespace <?= $naming->testsNamespace ?>\Integration\Command;

<?= $imports->render() ?>

final class HelloCommandTest extends KernelTestCase
{
    public function testGreetsTheGivenName(): void
    {
        $tester = $this->createCommandTester();

        $tester->execute(['name' => 'Jane']);

        $tester->assertCommandIsSuccessful();
        $this->assertStringContainsString('[OK] Hello Jane!', $tester->getDisplay());
    }

    public function testYellsTheGreeting(): void
    {
        $tester = $this->createCommandTester();

        $tester->execute(['name' => 'Jane', '--yell' => true]);

        $tester->assertCommandIsSuccessful();
        $this->assertStringContainsString('[OK] HELLO JANE!', $tester->getDisplay());
    }

    public function testRejectsAnEmptyName(): void
    {
        $tester = $this->createCommandTester();

        $tester->execute(['name' => ' ']);

        $this->assertSame(Command::INVALID, $tester->getStatusCode());
        $this->assertStringContainsString('[ERROR] The name to greet must not be empty.', $tester->getDisplay());
    }

    private function createCommandTester(): CommandTester
    {
        $application = new Application(self::bootKernel());

        return new CommandTester($application->find(<?= $helper->php(CommandFeature::commandName($naming)) ?>));
    }
}
