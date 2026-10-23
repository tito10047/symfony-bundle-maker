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

use Composer\IO\BufferIO;
use PHPUnit\Framework\TestCase;
use Tito10047\BundleMaker\Blueprint\BundleBlueprint;
use Tito10047\BundleMaker\BundleCreator;
use Tito10047\BundleMaker\Contributor\Core\BundleClassContributor;
use Tito10047\BundleMaker\Contributor\Core\PackageContributor;
use Tito10047\BundleMaker\Contributor\CoreContributors;
use Tito10047\BundleMaker\Feature\FeatureGroup;
use Tito10047\BundleMaker\Feature\FeatureRegistry;
use Tito10047\BundleMaker\Generator\BundleGenerator;
use Tito10047\BundleMaker\Options\BundleOptionsAsker;
use Tito10047\BundleMaker\Options\EnvPresets;
use Tito10047\BundleMaker\Template\Exception\TemplateNotFoundException;
use Tito10047\BundleMaker\Tests\Support\FakeCommandRunner;
use Tito10047\BundleMaker\Tests\Support\Filesystem;
use Tito10047\BundleMaker\Tests\Support\TestFeature;

final class BundleCreatorTest extends TestCase
{
    private string $dir;
    private BufferIO $io;

    protected function setUp(): void
    {
        $this->dir = Filesystem::createTempDir('creator');
        $this->io = new BufferIO();
        file_put_contents($this->dir.'/composer.json', '{"name": "tito10047/symfony-bundle-skeleton"}');
    }

    protected function tearDown(): void
    {
        Filesystem::remove($this->dir);
    }

    public function testCreatesTheBundle(): void
    {
        mkdir($this->dir.'/.github/workflows', 0o777, true);
        foreach (['LICENSE', 'README.md', 'CLAUDE.md', '.gitattributes', '.github/workflows/ci.yml', '.gitignore'] as $file) {
            file_put_contents($this->dir.'/'.$file, 'skeleton');
        }
        $runner = new FakeCommandRunner();

        $this->createBundleCreator($runner, qa: true)->run();

        self::assertSame(['.gitignore', 'composer.json', 'config/services.php', 'src/AcmeBlogBundle.php'], array_keys(Filesystem::readTree($this->dir)));
        self::assertSame(['update'], $runner->calls);

        $output = $this->io->getOutput();
        self::assertStringContainsString('AcmeBlogBundle generated.', $output);
        self::assertStringContainsString('AcmeBlogBundle is ready to rock the Symfony world!', $output);
        self::assertStringContainsString('1. cd '.basename($this->dir), $output);
        self::assertStringContainsString('2. composer qa', $output);
        self::assertStringContainsString('3. git init', $output);
        self::assertStringContainsString('https://packagist.org/packages/submit', $output);
        self::assertStringContainsString('https://symfony.com/doc/current/bundles/best_practices.html', $output);
    }

    public function testSuggestsTheTestScriptWithoutQaScripts(): void
    {
        $this->createBundleCreator(new FakeCommandRunner(), test: true)->run();

        self::assertStringContainsString('2. composer test', $this->io->getOutput());
    }

    public function testDoesNotSuggestMissingScripts(): void
    {
        $this->createBundleCreator(new FakeCommandRunner())->run();

        self::assertStringContainsString('2. git init', $this->io->getOutput());
    }

    public function testTellsToChangeToTheBundleDirectoryOnlyWhenNeeded(): void
    {
        $this->createBundleCreator(new FakeCommandRunner(), test: true, userDir: $this->dir)->run();
        self::assertStringContainsString('1. composer test', $this->io->getOutput(), 'Already in the bundle directory.');

        $this->io = new BufferIO();
        $this->createBundleCreator(new FakeCommandRunner(), test: true, userDir: $this->dir.'/src')->run();
        self::assertStringContainsString('1. cd '.realpath($this->dir)."\n", $this->io->getOutput(), 'Outside the user directory: absolute.');
    }

    public function testReportsUpdateFailures(): void
    {
        $runner = new FakeCommandRunner(2);

        $this->createBundleCreator($runner, qa: true)->run();

        self::assertSame(['update'], $runner->calls);
        self::assertStringNotContainsString('ready to rock', $this->io->getOutput());
        self::assertStringContainsString('The dependencies could not be installed, fix the issue above and run "composer update".', $this->io->getOutput());
        self::assertStringContainsString('Next steps', $this->io->getOutput());
    }

    public function testReportsUpdateExceptions(): void
    {
        $runner = new FakeCommandRunner(new \RuntimeException('Network is down'));

        $this->createBundleCreator($runner)->run();

        self::assertStringContainsString('Network is down', $this->io->getOutput());
        self::assertStringContainsString('The dependencies could not be installed', $this->io->getOutput());
    }

    public function testDefaultInstanceGeneratesTheBundleAndRunsComposer(): void
    {
        // Packagist is disabled: the update fails fast and offline, which is reported, not thrown
        file_put_contents($this->dir.'/composer.json', '{"name": "tito10047/symfony-bundle-skeleton", "repositories": [{"packagist.org": false}]}');
        $presets = ['BUNDLE_MAKER_NAME=acme/default-bundle', 'BUNDLE_MAKER_AUTHOR_NAME=Jane', 'BUNDLE_MAKER_CONFIG=0'];
        array_map(putenv(...), $presets);

        try {
            BundleCreator::createDefault($this->io, $this->dir)->run();
        } finally {
            array_map(static fn (string $preset): bool => putenv(explode('=', $preset)[0]), $presets);
        }

        self::assertFileExists($this->dir.'/src/AcmeDefaultBundle.php');
        self::assertStringContainsString('The dependencies could not be installed', $this->io->getOutput());
        self::assertStringContainsString('"name": "acme/default-bundle"', (string) file_get_contents($this->dir.'/composer.json'));
    }

    public function testLeavesTheSkeletonUntouchedWhenTheBundleCannotBeRendered(): void
    {
        file_put_contents($this->dir.'/README.md', 'skeleton');
        $runner = new FakeCommandRunner();

        try {
            $this->createBundleCreator($runner, broken: true)->run();
            self::fail('An exception should have been thrown.');
        } catch (TemplateNotFoundException) {
        }

        self::assertSame(['README.md', 'composer.json'], array_keys(Filesystem::readTree($this->dir)));
        self::assertStringEqualsFile($this->dir.'/README.md', 'skeleton');
        self::assertSame([], $runner->calls);
    }

    private function createBundleCreator(FakeCommandRunner $runner, bool $qa = false, bool $test = false, bool $broken = false, ?string $userDir = null): BundleCreator
    {
        $features = new FeatureRegistry(
            new TestFeature('broken', default: $broken, contribution: static function (BundleBlueprint $blueprint): void {
                $blueprint->addFile('missing.tpl', 'missing.txt');
            }),
            new TestFeature('testing', default: $test, contribution: static function (BundleBlueprint $blueprint): void {
                $blueprint->composer->addScript('test', 'phpunit');
            }),
            new TestFeature('cs', FeatureGroup::QualityAssurance, default: $qa, contribution: static function (BundleBlueprint $blueprint): void {
                $blueprint->composer->addScript('cs', 'php-cs-fixer check', qa: true);
                $blueprint->composer->addScript('cs:fix', 'php-cs-fixer fix');
            }),
        );
        $presets = new EnvPresets([EnvPresets::NAME => 'acme/blog-bundle', EnvPresets::AUTHOR_NAME => 'Jane']);

        return new BundleCreator(
            $this->io,
            $this->dir,
            new BundleOptionsAsker($this->io, $features, $this->dir, $presets),
            // the bundle class only: these tests are about the orchestration, not the generated files
            new BundleGenerator(new CoreContributors(new PackageContributor(), new BundleClassContributor()), $features),
            $runner,
            userDir: $userDir ?? \dirname($this->dir),
        );
    }
}
