<?php

namespace Zeta\EverythingPhpBundle\Tests\Integration\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

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

        return new CommandTester($application->find('zeta-everything-php:hello'));
    }
}
