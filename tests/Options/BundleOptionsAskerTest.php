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

namespace Tito10047\BundleMaker\Tests\Options;

use Composer\IO\BufferIO;
use Composer\Util\ProcessExecutor;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Tito10047\BundleMaker\Feature\FeatureGroup;
use Tito10047\BundleMaker\Feature\FeatureRegistry;
use Tito10047\BundleMaker\Feature\FeatureSelection;
use Tito10047\BundleMaker\Options\BundleOptionsAsker;
use Tito10047\BundleMaker\Options\ConfigFormat;
use Tito10047\BundleMaker\Options\EnvPresets;
use Tito10047\BundleMaker\Options\GitConfig;
use Tito10047\BundleMaker\Options\InvalidPresetException;
use Tito10047\BundleMaker\Options\License;
use Tito10047\BundleMaker\Tests\Support\TestFeature;

final class BundleOptionsAskerTest extends TestCase
{
    private string $bundleDir;

    protected function setUp(): void
    {
        $this->bundleDir = sys_get_temp_dir().'/bundle-maker-asker/my-blog';
        if (!is_dir($this->bundleDir)) {
            mkdir($this->bundleDir, 0o777, true);
        }
    }

    protected function tearDown(): void
    {
        rmdir($this->bundleDir);
        rmdir(\dirname($this->bundleDir));
    }

    public function testUsesDefaultsWithoutInteraction(): void
    {
        $io = new BufferIO();

        $options = $this->createAsker($io, git: ['user.name' => 'Jane Doe', 'user.email' => 'jane@example.com'])->ask();

        self::assertSame('acme/my-blog-bundle', $options->naming->packageName);
        self::assertSame('AcmeMyBlogBundle for Symfony', $options->description);
        self::assertSame('Jane Doe', $options->author->name);
        self::assertSame('jane@example.com', $options->author->email);
        self::assertSame(License::Mit, $options->license);
        self::assertSame(ConfigFormat::Php, $options->configFormat);
        self::assertEquals(new FeatureSelection(['controllers' => false, 'twig' => false, 'phpstan' => true]), $options->features);

        $output = $io->getOutput();
        self::assertStringContainsString('Symfony bundle assistant', $output);
        self::assertMatchesRegularExpression('/^  Extension alias +acme_my_blog$/m', $output);
        self::assertStringNotContainsString('Twig namespace', $output, 'Twig is disabled.');
        self::assertMatchesRegularExpression('/^  Features +none$/m', $output);
        self::assertMatchesRegularExpression('/^  Quality assurance +phpstan$/m', $output);
        self::assertStringNotContainsString(' Identity', $output, 'Headings of unasked questions are not printed.');
    }

    public function testUsesGithubUserAsDefaultVendor(): void
    {
        $options = $this->createAsker(new BufferIO(), git: ['user.name' => 'Jane', 'github.user' => 'Jane-Doe'])->ask();

        self::assertSame('jane-doe/my-blog-bundle', $options->naming->packageName);
        self::assertNull($options->author->email);
    }

    public function testUsesPresetsWithoutInteraction(): void
    {
        $presets = new EnvPresets([
            EnvPresets::NAME => 'my-vendor/foo-bundle',
            EnvPresets::DESCRIPTION => 'Foo for Symfony',
            EnvPresets::AUTHOR_NAME => 'John',
            EnvPresets::AUTHOR_EMAIL => 'john@example.com',
            EnvPresets::LICENSE => 'apache-2.0',
            EnvPresets::CONFIG_FORMAT => 'yaml',
            EnvPresets::ALL => '1',
            'BUNDLE_MAKER_TWIG' => '0',
        ]);

        $options = $this->createAsker(new BufferIO(), $presets, ['user.name' => 'Jane'])->ask();

        self::assertSame('my-vendor/foo-bundle', $options->naming->packageName);
        self::assertSame('Foo for Symfony', $options->description);
        self::assertSame('John', $options->author->name);
        self::assertSame('john@example.com', $options->author->email);
        self::assertSame(License::Apache2, $options->license);
        self::assertSame(ConfigFormat::Yaml, $options->configFormat);
        self::assertEquals(new FeatureSelection(['controllers' => true, 'twig' => false, 'phpstan' => true]), $options->features);
    }

    public function testEmailPresetCanOmitTheGitEmail(): void
    {
        $options = $this->createAsker(new BufferIO(), new EnvPresets([EnvPresets::AUTHOR_EMAIL => 'none']), ['user.name' => 'Jane', 'user.email' => 'jane@example.com'])->ask();

        self::assertNull($options->author->email);
    }

    public function testAllPresetCanDisableEverything(): void
    {
        $options = $this->createAsker(new BufferIO(), new EnvPresets([EnvPresets::ALL => '0']), ['user.name' => 'Jane'])->ask();

        self::assertEquals(new FeatureSelection(['controllers' => false, 'twig' => false, 'phpstan' => false]), $options->features);
    }

    public function testRejectsInvalidPresets(): void
    {
        $this->expectException(InvalidPresetException::class);
        $this->expectExceptionMessage('Invalid value for the environment variable "BUNDLE_MAKER_CONFIG_FORMAT"');

        $this->createAsker(new BufferIO(), new EnvPresets([EnvPresets::CONFIG_FORMAT => 'xml']), ['user.name' => 'Jane'])->ask();
    }

    /**
     * @return iterable<string, array{array<string, string>, string}>
     */
    public static function provideInvalidPresetsOfLaterQuestions(): iterable
    {
        yield 'feature' => [['BUNDLE_MAKER_PHPSTAN' => 'maybe'], 'Invalid value for the environment variable "BUNDLE_MAKER_PHPSTAN": "maybe" is not a boolean'];
        yield 'all' => [[EnvPresets::ALL => 'maybe'], 'Invalid value for the environment variable "BUNDLE_MAKER_ALL"'];
        yield 'license' => [[EnvPresets::LICENSE => 'WTFPL'], 'Invalid value for the environment variable "BUNDLE_MAKER_LICENSE"'];
        yield 'unknown' => [['BUNDLE_MAKER_PHPSTAM' => '1', 'BUNDLE_MAKER_X' => '0'], 'Unknown environment variable "BUNDLE_MAKER_PHPSTAM", "BUNDLE_MAKER_X". Known variables: "BUNDLE_MAKER_NAME",'];
    }

    /**
     * @param array<string, string> $presets
     */
    #[DataProvider('provideInvalidPresetsOfLaterQuestions')]
    public function testRejectsInvalidPresetsBeforeTheFirstQuestion(array $presets, string $message): void
    {
        $io = new BufferIO();
        $io->setUserInputs([]);

        try {
            $this->createAsker($io, new EnvPresets($presets), ['user.name' => 'Jane'], self::createRegistryWithRequirement())->ask();
            self::fail('An exception should have been thrown.');
        } catch (InvalidPresetException $e) {
            self::assertStringContainsString($message, $e->getMessage());
        }

        self::assertStringNotContainsString('Package name', $io->getOutput());
    }

    /**
     * @return iterable<string, array{array<string, string>, string}>
     */
    public static function provideContradictingPresets(): iterable
    {
        yield 'disabled requirement' => [['BUNDLE_MAKER_HEADER' => '1', 'BUNDLE_MAKER_CONTROLLERS' => '0'], 'The environment variable "BUNDLE_MAKER_HEADER" enables the feature "header", which requires the feature "controllers", disabled by "BUNDLE_MAKER_CONTROLLERS".'];
        yield 'requirement disabled by all' => [['BUNDLE_MAKER_HEADER' => '1', EnvPresets::ALL => '0'], 'which requires the feature "controllers", disabled by "BUNDLE_MAKER_ALL".'];
    }

    /**
     * @param array<string, string> $presets
     */
    #[DataProvider('provideContradictingPresets')]
    public function testRejectsContradictingPresetsWithoutInteraction(array $presets, string $message): void
    {
        $this->expectException(InvalidPresetException::class);
        $this->expectExceptionMessage($message);

        $this->createAsker(new BufferIO(), new EnvPresets($presets), ['user.name' => 'Jane'], self::createRegistryWithRequirement())->ask();
    }

    public function testAsksTheRequiredFeatureFirstWhenPresetsContradictEachOther(): void
    {
        $io = new BufferIO();
        $io->setUserInputs([
            'acme/blog-bundle', // package name
            'The best blog',    // description
            '',                 // author name from git
            '',                 // author email from git
            '',                 // license
            '',                 // config format
            'y',                // controllers, disabled by its preset
            '',                 // header, enabled by its preset
            'n',                // phpstan
            'y',                // confirm
        ]);
        $presets = new EnvPresets(['BUNDLE_MAKER_HEADER' => '1', 'BUNDLE_MAKER_CONTROLLERS' => '0']);

        $options = $this->createAsker($io, $presets, ['user.name' => 'Jane', 'user.email' => 'jane@example.com'], self::createRegistryWithRequirement())->ask();

        self::assertEquals(new FeatureSelection(['controllers' => true, 'header' => true, 'phpstan' => false]), $options->features);
        self::assertStringContainsString('Enable controllers? [y/N]', $io->getOutput());
        self::assertStringContainsString('Enable header? [Y/n]', $io->getOutput());
    }

    public function testDoesNotAskAFeatureEnabledByItsPresetWhenItsRequiredFeatureIsDisabled(): void
    {
        $io = new BufferIO();
        $io->setUserInputs([
            'acme/blog-bundle', // package name
            'The best blog',    // description
            '',                 // author name from git
            '',                 // author email from git
            '',                 // license
            '',                 // config format
            '',                 // controllers, disabled by BUNDLE_MAKER_ALL: header is not asked
            'n',                // phpstan
            'y',                // confirm
        ]);
        $presets = new EnvPresets(['BUNDLE_MAKER_HEADER' => '1', EnvPresets::ALL => '0']);

        $options = $this->createAsker($io, $presets, ['user.name' => 'Jane', 'user.email' => 'jane@example.com'], self::createRegistryWithRequirement())->ask();

        self::assertEquals(new FeatureSelection(['controllers' => false, 'header' => false, 'phpstan' => false]), $options->features);
        self::assertStringNotContainsString('Enable header?', $io->getOutput());
    }

    public function testDisablesAFeatureWhoseRequiredFeatureIsDisabled(): void
    {
        $presets = new EnvPresets([EnvPresets::ALL => '1', 'BUNDLE_MAKER_CONTROLLERS' => '0']);

        $options = $this->createAsker(new BufferIO(), $presets, ['user.name' => 'Jane'], self::createRegistryWithRequirement())->ask();

        self::assertEquals(new FeatureSelection(['controllers' => false, 'header' => false, 'phpstan' => true]), $options->features);
    }

    public function testDoesNotAskAFeatureWhoseRequiredFeatureIsDisabled(): void
    {
        $io = new BufferIO();
        $io->setUserInputs([
            'acme/blog-bundle', // package name
            'The best blog',    // description
            '',                 // author name from git
            '',                 // author email from git
            '',                 // license
            '',                 // config format
            'n',                // controllers: header is not asked
            'n',                // phpstan
            'y',                // confirm
        ]);

        $options = $this->createAsker($io, git: ['user.name' => 'Jane', 'user.email' => 'jane@example.com'], features: self::createRegistryWithRequirement())->ask();

        self::assertFalse($options->features->isEnabled('header'));
        self::assertStringNotContainsString('Enable header?', $io->getOutput());
    }

    public function testFailsWithoutInteractionWhenNoAuthorIsKnown(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The author name must not be empty.');

        $this->createAsker(new BufferIO())->ask();
    }

    public function testWarnsAboutTheBundleSuffixWithoutInteraction(): void
    {
        $io = new BufferIO();

        $options = $this->createAsker($io, new EnvPresets([EnvPresets::NAME => 'acme/blog']), ['user.name' => 'Jane'])->ask();

        self::assertSame('acme/blog', $options->naming->packageName);
        self::assertStringContainsString('Bundle package names should end with "-bundle", e.g. "acme/blog-bundle".', $io->getOutput());
    }

    public function testAsksQuestionsInteractively(): void
    {
        $io = new BufferIO();
        $io->setUserInputs([
            'Acme/Invalid',      // invalid package name, asked again
            'acme/blog',         // valid, without the -bundle suffix
            'n',                 // do not keep it: asked again with "acme/blog-bundle" as default
            '',                  // accept the suggestion
            '',                  // no description: it is required, asked again
            'The best blog',     // description
            '',                  // author name from git
            'not-an-email',      // invalid email, asked again
            'none',              // no email (the default comes from git)
            '?',                 // lists the licenses, asked again
            'bsd-3-clause',      // license
            '?',                 // lists the configuration formats, asked again
            'php-array',         // config format
            'y',                 // controllers
            '',                  // twig: defaults to yes because controllers are enabled
            'n',                 // phpstan
            'y',                 // confirm
        ]);

        $options = $this->createAsker($io, git: ['user.name' => 'Jane <info>', 'user.email' => 'jane@example.com'])->ask();

        self::assertSame('acme/blog-bundle', $options->naming->packageName);
        self::assertSame('The best blog', $options->description);
        self::assertSame('Jane <info>', $options->author->name);
        self::assertNull($options->author->email);
        self::assertSame(License::Bsd3Clause, $options->license);
        self::assertSame(ConfigFormat::PhpArray, $options->configFormat);
        self::assertEquals(new FeatureSelection(['controllers' => true, 'twig' => true, 'phpstan' => false]), $options->features);

        $output = $io->getOutput();
        self::assertStringContainsString('The package name "Acme/Invalid" is invalid', $output);
        self::assertStringContainsString('"not-an-email" is not a valid email address.', $output);
        self::assertStringContainsString('Keep "acme/blog" anyway?', $output);
        self::assertStringContainsString('Package name (vendor/name) [acme/blog-bundle]', $output);
        self::assertStringContainsString(' Identity', $output);
        self::assertStringContainsString('  Description: The description must not be empty.', $output, 'No default description is proposed.');
        self::assertStringContainsString('Author name [Jane <info>]', $output, 'Defaults are escaped.');
        self::assertStringContainsString('License ("?" to list them) [MIT]', $output);
        self::assertStringContainsString("    BSD-3-Clause       BSD 3-Clause License\n", $output);
        self::assertStringContainsString("    php-array  PHP arrays, Symfony 7.4+\n", $output);
        self::assertMatchesRegularExpression('/^  Twig namespace +@AcmeBlog$/m', $output);
    }

    public function testKeepsAPackageNameWithoutSuffixWhenConfirmed(): void
    {
        $io = new BufferIO();
        $io->setUserInputs(['acme/blog', 'y', '', '', '', '', '', '', '', '', '', 'y']);

        $options = $this->createAsker($io, git: ['user.name' => 'Jane'])->ask();

        self::assertSame('acme/blog', $options->naming->packageName);
    }

    public function testAsksAgainWithPreviousAnswersAsDefaultsWhenNotConfirmed(): void
    {
        $io = new BufferIO();
        $io->setUserInputs([
            // first round
            'acme/first-bundle', 'First', 'John', 'john@example.com', 'MPL-2.0', 'yaml', 'y', 'n', 'n',
            'n', // not confirmed
            // second round: only the description changes
            '', 'Second', '', '', '', '', '', '', '',
            'y',
        ]);

        $options = $this->createAsker($io, git: ['user.name' => 'Jane'])->ask();

        self::assertSame('acme/first-bundle', $options->naming->packageName);
        self::assertSame('Second', $options->description);
        self::assertSame('John', $options->author->name);
        self::assertSame('john@example.com', $options->author->email);
        self::assertSame(License::Mpl2, $options->license);
        self::assertSame(ConfigFormat::Yaml, $options->configFormat);
        self::assertEquals(new FeatureSelection(['controllers' => true, 'twig' => false, 'phpstan' => false]), $options->features);
        self::assertStringContainsString('your previous answers are now the defaults', $io->getOutput());
    }

    /**
     * A registry with a "header" feature that requires the "controllers" feature.
     */
    private static function createRegistryWithRequirement(): FeatureRegistry
    {
        return new FeatureRegistry(
            new TestFeature('controllers'),
            new TestFeature('header', requiredFeatures: ['controllers']),
            new TestFeature('phpstan', FeatureGroup::QualityAssurance, default: true),
        );
    }

    /**
     * @param array<string, string> $git
     */
    private function createAsker(BufferIO $io, EnvPresets $presets = new EnvPresets(), array $git = [], ?FeatureRegistry $features = null): BundleOptionsAsker
    {
        $process = $this->createMock(ProcessExecutor::class);
        $process->method('execute')->willReturnCallback(static function (array $command, mixed &$output) use ($git): int {
            $key = $command[3];
            if (!\is_string($key) || !isset($git[$key])) {
                return 1;
            }
            $output = $git[$key]."\n";

            return 0;
        });

        $features ??= new FeatureRegistry(
            new TestFeature('controllers'),
            new TestFeature('twig', default: static fn (FeatureSelection $previous): bool => $previous->isEnabled('controllers')),
            new TestFeature('phpstan', FeatureGroup::QualityAssurance, default: true),
        );

        return new BundleOptionsAsker($io, $features, $this->bundleDir, $presets, new GitConfig($process));
    }
}
