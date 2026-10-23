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

namespace Tito10047\BundleMaker\Tests\Blueprint;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Tito10047\BundleMaker\Blueprint\ComposerManifest;
use Tito10047\BundleMaker\Blueprint\ComposerScript;

final class ComposerManifestTest extends TestCase
{
    public function testSortsRequirementsLikeComposer(): void
    {
        $manifest = new ComposerManifest();
        $manifest->require('symfony/http-kernel', '^7.4');
        $manifest->require('ext-json', '*');
        $manifest->require('doctrine/orm', '^3.0');
        $manifest->require('lib-icu', '*');
        $manifest->require('php', '>=8.2');
        $manifest->require('php', '>=8.2');

        self::assertSame([
            'php' => '>=8.2',
            'ext-json' => '*',
            'lib-icu' => '*',
            'doctrine/orm' => '^3.0',
            'symfony/http-kernel' => '^7.4',
        ], $manifest->getRequire());
    }

    public function testRuntimeDependenciesWinOverDevelopmentOnes(): void
    {
        $manifest = new ComposerManifest();
        $manifest->requireDev('symfony/yaml', '^7.4');
        $manifest->requireDev('phpunit/phpunit', '^11.5');
        $manifest->require('symfony/yaml', '^7.4');
        $manifest->requireDev('symfony/yaml', '^7.4');

        self::assertSame(['symfony/yaml' => '^7.4'], $manifest->getRequire());
        self::assertSame(['phpunit/phpunit' => '^11.5'], $manifest->getRequireDev());
    }

    /**
     * @return iterable<string, array{callable(ComposerManifest): void}>
     */
    public static function provideConflictingConstraints(): iterable
    {
        yield 'require' => [static function (ComposerManifest $manifest): void {
            $manifest->require('acme/foo', '^1.0');
            $manifest->require('acme/foo', '^2.0');
        }];
        yield 'require-dev' => [static function (ComposerManifest $manifest): void {
            $manifest->requireDev('acme/foo', '^1.0');
            $manifest->requireDev('acme/foo', '^2.0');
        }];
        yield 'require then require-dev' => [static function (ComposerManifest $manifest): void {
            $manifest->require('acme/foo', '^1.0');
            $manifest->requireDev('acme/foo', '^2.0');
        }];
        yield 'require-dev then require' => [static function (ComposerManifest $manifest): void {
            $manifest->requireDev('acme/foo', '^1.0');
            $manifest->require('acme/foo', '^2.0');
        }];
    }

    /**
     * @param callable(ComposerManifest): void $requirements
     */
    #[DataProvider('provideConflictingConstraints')]
    public function testRejectsConflictingConstraints(callable $requirements): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('The package "acme/foo" is required with conflicting constraints');

        $requirements(new ComposerManifest());
    }

    /**
     * @return iterable<array{string}>
     */
    public static function provideInvalidPackageNames(): iterable
    {
        yield ['Acme/Foo'];
        yield ['acme'];
        yield ['acme/'];
        yield ['ext-'];
    }

    #[DataProvider('provideInvalidPackageNames')]
    public function testRejectsInvalidPackageNames(string $package): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(\sprintf('The package name "%s" is invalid.', $package));

        (new ComposerManifest())->require($package, '*');
    }

    public function testCollectsKeywordsPluginsAndScripts(): void
    {
        $manifest = new ComposerManifest();
        $manifest->addKeyword('symfony');
        $manifest->addKeyword('symfony-ux');
        $manifest->addKeyword('symfony');
        $manifest->allowPlugin('phpstan/extension-installer');
        $manifest->allowPlugin('acme/plugin');
        $manifest->allowPlugin('acme/plugin');
        $manifest->addScript('test', 'phpunit', 'Runs the tests', qa: true);
        $manifest->addScript('cs:fix', ['php-cs-fixer fix']);

        self::assertSame(['symfony', 'symfony-ux'], $manifest->getKeywords());
        self::assertSame(['acme/plugin', 'phpstan/extension-installer'], $manifest->getAllowedPlugins());
        self::assertEquals([
            'test' => new ComposerScript('test', ['phpunit'], 'Runs the tests', true),
            'cs:fix' => new ComposerScript('cs:fix', ['php-cs-fixer fix']),
        ], $manifest->getScripts());
        self::assertSame(['test'], $manifest->getQaScriptNames());
        self::assertTrue($manifest->hasScript(ComposerManifest::TEST_SCRIPT));
        self::assertTrue($manifest->hasScript('cs:fix'));
        self::assertFalse($manifest->hasScript('cs'));
    }

    public function testExposesTheConstraintOfARequiredPackage(): void
    {
        $manifest = new ComposerManifest();
        $manifest->require('symfony/config', '^7.4.17 || ^8.1.5');
        $manifest->requireDev('phpunit/phpunit', '^11.5 || ^12.0');

        self::assertSame('^7.4.17 || ^8.1.5', $manifest->getConstraint('symfony/config'));
        self::assertSame('^11.5 || ^12.0', $manifest->getConstraint('phpunit/phpunit'));
        self::assertNull($manifest->getConstraint('twig/twig'));
    }

    public function testRejectsDuplicateScripts(): void
    {
        $manifest = new ComposerManifest();
        $manifest->addScript('test', 'phpunit');

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('The Composer script "test" is already defined.');

        $manifest->addScript('test', 'phpunit');
    }

    public function testTheQaScriptIsReserved(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('The "qa" script is generated');

        (new ComposerManifest())->addScript('qa', ['@test']);
    }

    /**
     * @return iterable<array{string}>
     */
    public static function provideInvalidScriptNames(): iterable
    {
        yield ['Test'];
        yield ['cs fix'];
        yield [':fix'];
        yield ['cs:'];
        yield ['1test'];
    }

    #[DataProvider('provideInvalidScriptNames')]
    public function testRejectsInvalidScriptNames(string $name): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(\sprintf('The Composer script name "%s" is invalid', $name));

        (new ComposerManifest())->addScript($name, 'echo');
    }
}
