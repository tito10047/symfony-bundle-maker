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

use Composer\IO\IOInterface;
use Composer\Util\Filesystem;
use Composer\Util\Platform;
use Composer\Util\ProcessExecutor;
use Tito10047\BundleMaker\Blueprint\BundleBlueprint;
use Tito10047\BundleMaker\Blueprint\ComposerManifest;
use Tito10047\BundleMaker\Composer\CommandRunner;
use Tito10047\BundleMaker\Composer\CommandRunnerInterface;
use Tito10047\BundleMaker\Contributor\CoreContributors;
use Tito10047\BundleMaker\Feature\FeatureRegistry;
use Tito10047\BundleMaker\Generator\BundleGenerator;
use Tito10047\BundleMaker\Options\BundleOptionsAsker;
use Tito10047\BundleMaker\Options\EnvPresets;

/**
 * Turns the freshly created skeleton project into the bundle:
 *
 *  1. asks the questions and shows the summary
 *  2. renders the bundle in memory
 *  3. removes the files of the skeleton itself (README, LICENSE, CI...), then writes the
 *     files of the bundle and composer.json
 *  4. runs a single "composer update" (which also uninstalls this plugin)
 *  5. prints the next steps
 *
 * Nothing is modified before the bundle is rendered: when the assistant fails, the
 * skeleton is left as is and the assistant can run again.
 *
 * Once the update has started, the plugin files are removed from vendor/: the code running
 * after it must only use classes that are already loaded.
 */
final class BundleCreator
{
    /** Files of the bundle-skeleton repository that must not end up in the bundle. */
    public const SKELETON_FILES = ['.github', '.gitattributes', 'CLAUDE.md', 'LICENSE', 'README.md'];

    public function __construct(
        private readonly IOInterface $io,
        private readonly string $bundleDir,
        private readonly BundleOptionsAsker $asker,
        private readonly BundleGenerator $generator,
        private readonly CommandRunnerInterface $commandRunner,
        private readonly Filesystem $filesystem = new Filesystem(),
        private readonly ?string $userDir = null,
    ) {
    }

    /**
     * Wires the default collaborators: built-in features, BUNDLE_MAKER_* presets and in-process Composer commands.
     *
     * The directory the user ran "composer create-project" from is read from $PWD: Composer has left it by now.
     */
    public static function createDefault(IOInterface $io, string $bundleDir): self
    {
        $features = FeatureRegistry::createDefault();

        return new self(
            $io,
            $bundleDir,
            new BundleOptionsAsker($io, $features, $bundleDir, EnvPresets::fromEnvironment()),
            new BundleGenerator(CoreContributors::createDefault(), $features),
            new CommandRunner($io, $bundleDir),
            userDir: \is_string($pwd = Platform::getEnv('PWD')) && '' !== $pwd ? $pwd : null,
        );
    }

    public function run(): void
    {
        $options = $this->asker->ask();
        $blueprint = $this->generator->createBlueprint($options);
        $files = $this->generator->render($blueprint);

        $this->removeSkeletonFiles();
        $this->generator->write($blueprint, $files, $this->bundleDir);
        $this->io->write(\sprintf('<info>%s generated.</info> Installing its dependencies...', $options->naming->className));

        $nextSteps = $this->nextSteps($blueprint);

        try {
            $status = $this->commandRunner->update();
        } catch (\Exception $e) {
            $this->io->writeError('<error>'.$e->getMessage().'</error>');
            $status = 1;
        }

        if (0 !== $status) {
            $this->io->writeError(['', '<warning>The dependencies could not be installed, fix the issue above and run "composer update".</warning>']);
            $this->io->write($nextSteps);

            return;
        }

        $this->io->write(['', \sprintf('<bg=green;fg=white> %s is ready to rock the Symfony world! </>', $options->naming->className)]);
        $this->io->write($nextSteps);
    }

    private function removeSkeletonFiles(): void
    {
        foreach (self::SKELETON_FILES as $file) {
            $path = $this->bundleDir.'/'.$file;
            if (file_exists($path) || is_link($path)) {
                $this->filesystem->remove($path);
            }
        }
    }

    /**
     * The bundle directory, relative to the directory of the user when it is inside it,
     * absolute otherwise; null when the user is already in it.
     */
    private function relativeBundleDir(): ?string
    {
        $bundleDir = realpath($this->bundleDir);
        $bundleDir = false === $bundleDir ? $this->bundleDir : $bundleDir;
        $userDir = null === $this->userDir ? false : realpath($this->userDir);

        return match (true) {
            false === $userDir => $bundleDir,
            $userDir === $bundleDir => null,
            str_starts_with($bundleDir, rtrim($userDir, '/\\').\DIRECTORY_SEPARATOR) => substr($bundleDir, \strlen(rtrim($userDir, '/\\')) + 1),
            default => $bundleDir,
        };
    }

    /**
     * @return list<string>
     */
    private function nextSteps(BundleBlueprint $blueprint): array
    {
        $steps = [];
        if (null !== $directory = $this->relativeBundleDir()) {
            $steps[] = 'cd '.(1 === preg_match('{^[\w./:@+-]+$}', $directory) ? $directory : ProcessExecutor::escape($directory));
        }
        if ([] !== $blueprint->composer->getQaScriptNames()) {
            $steps[] = 'composer '.ComposerManifest::QA_SCRIPT;
        } elseif ($blueprint->composer->hasScript(ComposerManifest::TEST_SCRIPT)) {
            $steps[] = 'composer '.ComposerManifest::TEST_SCRIPT;
        }
        $steps[] = 'git init && git add -A && git commit -m "Initial commit"';
        $steps[] = 'Push it to GitHub and submit it to Packagist: https://packagist.org/packages/submit';

        $lines = [
            '',
            ' <info>Next steps</info>',
            '',
        ];
        foreach ($steps as $i => $step) {
            $lines[] = \sprintf('  %d. %s', $i + 1, $step);
        }
        $lines[] = '';
        $lines[] = ' Keep following the best practices: <comment>https://symfony.com/doc/current/bundles/best_practices.html</comment>';
        $lines[] = '';

        return $lines;
    }
}
