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

namespace Tito10047\BundleMaker;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\EventDispatcher\ScriptExecutionException;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;
use Composer\Util\Platform;
use Symfony\Component\Console\Formatter\OutputFormatter;

/**
 * Composer plugin entry point: turns the skeleton into a bundle right after "composer create-project".
 */
final class Plugin implements PluginInterface, EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            ScriptEvents::POST_CREATE_PROJECT_CMD => 'onPostCreateProject',
        ];
    }

    public function activate(Composer $composer, IOInterface $io): void
    {
    }

    public function deactivate(Composer $composer, IOInterface $io): void
    {
    }

    public function uninstall(Composer $composer, IOInterface $io): void
    {
    }

    /**
     * "composer create-project" runs it from the directory of the new project.
     *
     * A failure is reported in one line (the trace with -v), and fails the command: the skeleton
     * is left untouched, so the assistant can run again once the issue is fixed.
     *
     * @throws ScriptExecutionException when the bundle could not be created
     */
    public function onPostCreateProject(Event $event): void
    {
        $io = $event->getIO();

        try {
            BundleCreator::createDefault($io, Platform::getCwd())->run();
        } catch (\Exception $e) {
            $io->writeError(['', '<error>'.OutputFormatter::escape($e->getMessage()).'</error>']);
            if ($io->isVerbose()) {
                $io->writeError(OutputFormatter::escape((string) $e));
            }
            $io->writeError(\sprintf('Fix the issue, then run the assistant again with "<comment>composer run-script %s</comment>".', ScriptEvents::POST_CREATE_PROJECT_CMD));

            throw new ScriptExecutionException('The bundle could not be created.', 1, $e);
        }
    }
}
