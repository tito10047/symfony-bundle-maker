<?php
use Tito10047\BundleMaker\Contributor\Core\GreeterContributor;
use Tito10047\BundleMaker\Feature\CommandFeature;

/**
 * @var Tito10047\BundleMaker\Naming\BundleNaming     $naming
 * @var Tito10047\BundleMaker\Template\TemplateHelper $helper
 */
$imports = $helper->imports([
    $naming->namespace.'\Exception\InvalidArgumentException',
    GreeterContributor::interfaceName($naming),
    'Symfony\Component\Console\Attribute\Argument',
    'Symfony\Component\Console\Attribute\AsCommand',
    'Symfony\Component\Console\Attribute\Option',
    'Symfony\Component\Console\Command\Command',
    'Symfony\Component\Console\Style\SymfonyStyle',
], $naming->namespace.'\Command');
?>
<?= "<?php\n" ?>

namespace <?= $naming->namespace ?>\Command;

<?= $imports->render() ?>

/**
 * Greets someone from the command line, e.g. "php bin/console <?= CommandFeature::commandName($naming) ?> Jane".
 */
#[<?= $imports->name('Symfony\Component\Console\Attribute\AsCommand') ?>(<?= $helper->php(CommandFeature::commandName($naming)) ?>, 'Greets someone')]
final readonly class HelloCommand
{
    public function __construct(
        private <?= $imports->name(GreeterContributor::interfaceName($naming)) ?> $greeter,
    ) {
    }

    public function __invoke(
        <?= $imports->name('Symfony\Component\Console\Style\SymfonyStyle') ?> $io,
        #[<?= $imports->name('Symfony\Component\Console\Attribute\Argument') ?>('The name of the person to greet')]
        string $name,
        #[<?= $imports->name('Symfony\Component\Console\Attribute\Option') ?>('Greet in uppercase', shortcut: 'y')]
        bool $yell = false,
    ): int {
        try {
            $greeting = $this->greeter->greet($name);
        } catch (<?= $imports->name($naming->namespace.'\Exception\InvalidArgumentException') ?> $exception) {
            $io->error($exception->getMessage());

            return <?= $imports->name('Symfony\Component\Console\Command\Command') ?>::INVALID;
        }

        $io->success($yell ? mb_strtoupper($greeting) : $greeting);

        return <?= $imports->name('Symfony\Component\Console\Command\Command') ?>::SUCCESS;
    }
}
