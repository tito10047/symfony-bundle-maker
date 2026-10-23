<?php

namespace Tito10047\EverythingYamlBundle\Command;

use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Tito10047\EverythingYamlBundle\Exception\InvalidArgumentException;
use Tito10047\EverythingYamlBundle\GreeterInterface;

/**
 * Greets someone from the command line, e.g. "php bin/console tito10047-everything-yaml:hello Jane".
 */
#[AsCommand('tito10047-everything-yaml:hello', 'Greets someone')]
final readonly class HelloCommand
{
    public function __construct(
        private GreeterInterface $greeter,
    ) {
    }

    public function __invoke(
        SymfonyStyle $io,
        #[Argument('The name of the person to greet')]
        string $name,
        #[Option('Greet in uppercase', shortcut: 'y')]
        bool $yell = false,
    ): int {
        try {
            $greeting = $this->greeter->greet($name);
        } catch (InvalidArgumentException $exception) {
            $io->error($exception->getMessage());

            return Command::INVALID;
        }

        $io->success($yell ? mb_strtoupper($greeting) : $greeting);

        return Command::SUCCESS;
    }
}
