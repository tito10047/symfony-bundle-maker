<?php

namespace Acme\EverythingPhpBundle\Command;

use Acme\EverythingPhpBundle\Exception\InvalidArgumentException;
use Acme\EverythingPhpBundle\GreeterInterface;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Greets someone from the command line, e.g. "php bin/console acme-everything-php:hello Jane".
 */
#[AsCommand('acme-everything-php:hello', 'Greets someone')]
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
