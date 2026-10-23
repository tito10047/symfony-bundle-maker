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

namespace Tito10047\BundleMaker\Tests\Composer;

use Composer\IO\BufferIO;
use Composer\Util\Platform;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\NullOutput;
use Tito10047\BundleMaker\Composer\CommandRunner;
use Tito10047\BundleMaker\Composer\ComposerJsonWriter;
use Tito10047\BundleMaker\Tests\Support\Filesystem;

/**
 * Runs real Composer commands, offline: the project has no dependency and Packagist is disabled.
 *
 * Composer runs in a Composer home of its own: the global configuration and credentials of the
 * machine must not change the result, e.g. a GitHub token that the tested Composer version rejects.
 */
final class CommandRunnerTest extends TestCase
{
    private string $dir;
    /** @var array<non-empty-string, string|false> the environment variables changed by the test, with their original values */
    private array $env = [];

    protected function setUp(): void
    {
        $this->dir = Filesystem::createTempDir('runner');
        $this->setEnv('COMPOSER_NO_AUDIT', '1');
        $this->setEnv('COMPOSER_HOME', $this->dir.'/composer-home');
        $this->setEnv('COMPOSER_CACHE_DIR', $this->dir.'/composer-cache');
        $this->setEnv('COMPOSER_AUTH', false);
    }

    protected function tearDown(): void
    {
        Filesystem::remove($this->dir);
        foreach ($this->env as $name => $value) {
            false === $value ? Platform::clearEnv($name) : Platform::putEnv($name, $value);
        }
    }

    public function testUpdateRunsThePluginsOfTheProject(): void
    {
        // installs this plugin first, like "composer create-project" does with the skeleton
        $this->createPlugin('maker', ComposerJsonWriter::PLUGIN_PACKAGE);
        $this->createPlugin('marker', 'acme/marker-plugin');
        $this->writeComposerJson([
            'require' => [ComposerJsonWriter::PLUGIN_PACKAGE => '*'],
            'config' => ['allow-plugins' => [ComposerJsonWriter::PLUGIN_PACKAGE => true]],
        ]);
        $io = new BufferIO();
        self::assertSame(0, (new CommandRunner($io, $this->dir, output: new NullOutput()))->update(), $io->getOutput());

        // the generated composer.json: this plugin is neither required nor allowed anymore
        $composerJson = $this->writeComposerJson([
            'require' => ['acme/marker-plugin' => '*'],
            'config' => ['allow-plugins' => ['acme/marker-plugin' => true]],
        ]);
        $io = new BufferIO();
        $status = (new CommandRunner($io, $this->dir, output: new NullOutput()))->update();

        self::assertSame(0, $status, $io->getOutput());
        self::assertFileExists($this->dir.'/marker.activated', 'The plugins of the project must run.');
        self::assertFileDoesNotExist(getcwd().'/maker.activated', 'Plugins must run from the bundle directory.');
        self::assertDirectoryDoesNotExist($this->dir.'/vendor/'.ComposerJsonWriter::PLUGIN_PACKAGE);
        self::assertStringNotContainsString(ComposerJsonWriter::PLUGIN_PACKAGE.'" plugin was not loaded', $io->getOutput());
        self::assertSame($composerJson, file_get_contents($this->dir.'/composer.json'), 'composer.json must not be modified.');
    }

    public function testUpdateKeepsGlobalPluginPolicies(): void
    {
        $composerJson = $this->writeComposerJson(['config' => ['allow-plugins' => false]]);

        $status = (new CommandRunner(new BufferIO(), $this->dir, output: new NullOutput()))->update();

        self::assertSame(0, $status);
        self::assertSame($composerJson, file_get_contents($this->dir.'/composer.json'));
    }

    public function testUpdateFailsWithoutComposerJson(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unable to read "'.$this->dir.'/composer.json".');

        (new CommandRunner(new BufferIO(), $this->dir, output: new NullOutput()))->update();
    }

    /**
     * Creates a plugin package, in a path repository of the project, writing "<directory>.activated" when activated.
     */
    private function createPlugin(string $directory, string $name): void
    {
        $namespace = 'Tito10047\\BundleMaker\\Tests\\Fixtures\\'.ucfirst($directory).uniqid();
        mkdir($this->dir.'/plugins/'.$directory, 0o777, true);
        file_put_contents($this->dir.'/plugins/'.$directory.'/composer.json', json_encode([
            'name' => $name,
            'version' => '1.0.0',
            'type' => 'composer-plugin',
            'require' => ['composer-plugin-api' => '^2.0'],
            'autoload' => ['psr-4' => [$namespace.'\\' => '']],
            'extra' => ['class' => $namespace.'\\Plugin'],
        ], \JSON_THROW_ON_ERROR));
        file_put_contents($this->dir.'/plugins/'.$directory.'/Plugin.php', <<<PHP
            <?php

            namespace {$namespace};

            final class Plugin implements \\Composer\\Plugin\\PluginInterface
            {
                public function activate(\\Composer\\Composer \$composer, \\Composer\\IO\\IOInterface \$io): void
                {
                    touch('{$directory}.activated');
                }

                public function deactivate(\\Composer\\Composer \$composer, \\Composer\\IO\\IOInterface \$io): void
                {
                }

                public function uninstall(\\Composer\\Composer \$composer, \\Composer\\IO\\IOInterface \$io): void
                {
                }
            }
            PHP);
    }

    /**
     * @param array<string, mixed> $extra
     */
    private function writeComposerJson(array $extra): string
    {
        $content = json_encode([
            'name' => 'acme/runner-test',
            'repositories' => [['packagist.org' => false], ['type' => 'path', 'url' => 'plugins/*', 'options' => ['symlink' => false]]],
        ] + $extra, \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES | \JSON_THROW_ON_ERROR)."\n";
        file_put_contents($this->dir.'/composer.json', $content);

        return $content;
    }

    /**
     * @param non-empty-string $name
     * @param string|false     $value false removes the variable
     */
    private function setEnv(string $name, string|false $value): void
    {
        $this->env[$name] ??= Platform::getEnv($name);
        false === $value ? Platform::clearEnv($name) : Platform::putEnv($name, $value);
    }
}
