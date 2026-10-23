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

namespace Tito10047\BundleMaker\Tests;

use Composer\Composer;
use Composer\EventDispatcher\ScriptExecutionException;
use Composer\IO\BufferIO;
use Composer\IO\NullIO;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;
use Tito10047\BundleMaker\Options\InvalidPresetException;
use Tito10047\BundleMaker\Plugin;
use Tito10047\BundleMaker\Tests\Support\Filesystem;

final class PluginTest extends TestCase
{
    public function testSubscribesToTheProjectCreation(): void
    {
        self::assertSame([ScriptEvents::POST_CREATE_PROJECT_CMD => 'onPostCreateProject'], Plugin::getSubscribedEvents());
    }

    public function testLifecycleHooksHaveNoSideEffects(): void
    {
        $plugin = new Plugin();
        $composer = new Composer();
        $io = new NullIO();

        $plugin->activate($composer, $io);
        $plugin->deactivate($composer, $io);
        $plugin->uninstall($composer, $io);

        $this->addToAssertionCount(1);
    }

    public function testReportsFailuresInOneLineAndHowToRunTheAssistantAgain(): void
    {
        foreach ([OutputInterface::VERBOSITY_NORMAL => false, OutputInterface::VERBOSITY_VERBOSE => true] as $verbosity => $trace) {
            $io = new BufferIO('', $verbosity);
            $exception = $this->createProjectWithInvalidLicense($io);

            self::assertSame('The bundle could not be created.', $exception->getMessage());
            self::assertSame(1, $exception->getCode());
            self::assertInstanceOf(InvalidPresetException::class, $exception->getPrevious());

            $output = $io->getOutput();
            self::assertStringContainsString("\nInvalid value for the environment variable \"BUNDLE_MAKER_LICENSE\": \"WTFPL\" is not a supported license.", $output);
            self::assertStringContainsString('Fix the issue, then run the assistant again with "composer run-script post-create-project-cmd".', $output);
            self::assertSame($trace, str_contains($output, '#0 '), 'The trace is only shown in verbose mode.');
        }
    }

    private function createProjectWithInvalidLicense(BufferIO $io): ScriptExecutionException
    {
        $dir = Filesystem::createTempDir('plugin');
        file_put_contents($dir.'/composer.json', '{"name": "tito10047/symfony-bundle-skeleton"}');
        $cwd = (string) getcwd();
        chdir($dir);
        putenv('BUNDLE_MAKER_LICENSE=WTFPL');

        try {
            (new Plugin())->onPostCreateProject(new Event(ScriptEvents::POST_CREATE_PROJECT_CMD, new Composer(), $io));
        } catch (ScriptExecutionException $e) {
            return $e;
        } finally {
            putenv('BUNDLE_MAKER_LICENSE');
            chdir($cwd);
            self::assertSame(['composer.json'], array_keys(Filesystem::readTree($dir)));
            Filesystem::remove($dir);
        }

        self::fail('An exception should have been thrown.');
    }
}
