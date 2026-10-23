<?php

declare(strict_types=1);

/*
 * This file is part of the tito10047/symfony-bundle-maker package.
 *
 * (c) Jozef Môstka <jozef@mostka.sk>
 * (c) Yonel Ceruto <yonelceruto@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tito10047\BundleMaker\Composer;

use Composer\Command\BaseCommand;
use Composer\Command\RemoveCommand;
use Composer\Command\RequireCommand;
use Composer\Composer;
use Composer\Console\Application;
use Composer\Factory;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;

class CommandRunner
{
    private readonly OutputInterface $output;

    public function __construct(
        private readonly Composer $composer,
        private readonly Application $application = new Application(),
        ?OutputInterface $output = null,
    ) {
        $this->output = $output ?? Factory::createOutput();
    }

    /**
     * @param string|list<string> $packages
     */
    public function require(string|array $packages, bool $dev = false): int
    {
        return $this->run(new RequireCommand(), $packages, $dev);
    }

    /**
     * @param string|list<string> $packages
     */
    public function remove(string|array $packages, bool $dev = false): int
    {
        return $this->run(new RemoveCommand(), $packages, $dev);
    }

    /**
     * @param string|list<string> $packages
     */
    private function run(BaseCommand $command, string|array $packages, bool $dev): int
    {
        $command->setComposer($this->composer);
        $command->setApplication($this->application);

        $input = ['packages' => (array) $packages];
        if ($dev) {
            $input['--dev'] = true;
        }

        return $command->run(new ArrayInput($input), $this->output);
    }
}
