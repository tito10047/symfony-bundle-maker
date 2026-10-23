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

use Composer\Command\UpdateCommand;
use Composer\Composer;
use Composer\Console\Application;
use Composer\Factory;
use Composer\IO\IOInterface;
use Composer\PartialComposer;
use Composer\Plugin\PluginManager;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Runs "composer update" in-process against the bundle composer.json, with a fresh Composer
 * instance, so it sees the composer.json written by the plugin.
 */
final class CommandRunner implements CommandRunnerInterface
{
    private readonly OutputInterface $output;

    public function __construct(
        private readonly IOInterface $io,
        private readonly string $workingDir,
        private readonly Application $application = new Application(),
        ?OutputInterface $output = null,
    ) {
        $this->output = $output ?? Factory::createOutput();
    }

    /**
     * Runs "composer update", which installs the bundle dependencies and uninstalls this plugin.
     *
     * Plugins of the bundle (e.g. phpstan/extension-installer) run as in any update. This plugin is
     * still installed when the update starts, but no longer allowed by composer.json: it is allowed
     * in memory, so Composer neither warns about it nor prompts for it. composer.json is never modified.
     */
    public function update(): int
    {
        $file = $this->workingDir.'/composer.json';
        if (!is_file($file)) {
            throw new \RuntimeException(\sprintf('Unable to read "%s".', $file));
        }

        // like "composer --working-dir": plugins, scripts and relative paths run from the bundle directory
        $cwd = getcwd();
        if (!chdir($this->workingDir)) {
            throw new \RuntimeException(\sprintf('Unable to change the working directory to "%s".', $this->workingDir));
        }

        try {
            $command = new UpdateCommand();
            $command->setApplication($this->application);
            $command->setIO($this->io);
            $command->setComposer(self::createComposer($this->io, $file, $this->workingDir));

            $input = new ArrayInput([], $command->getDefinition());
            $input->setInteractive($this->io->isInteractive());

            return $command->run($input, $this->output);
        } finally {
            if (false !== $cwd) {
                chdir($cwd);
            }
        }
    }

    /**
     * Factory::create() would disable the plugins of the project, as for any composer.json given by path:
     * Factory::createComposer() with the project directory as working directory loads them like "composer" does.
     */
    private static function createComposer(IOInterface $io, string $file, string $workingDir): Composer
    {
        $factory = new class extends Factory {
            protected function createPluginManager(IOInterface $io, Composer $composer, ?PartialComposer $globalComposer = null, $disablePlugins = false): PluginManager
            {
                // the plugin manager reads allow-plugins when created: allow this plugin in memory, before
                $config = $composer->getConfig();
                if (\is_array($config->get('allow-plugins'))) {
                    $config->merge(['config' => ['allow-plugins' => [ComposerJsonWriter::PLUGIN_PACKAGE => true]]]);
                }

                return parent::createPluginManager($io, $composer, $globalComposer, $disablePlugins);
            }
        };

        return $factory->createComposer($io, $file, false, $workingDir);
    }
}
