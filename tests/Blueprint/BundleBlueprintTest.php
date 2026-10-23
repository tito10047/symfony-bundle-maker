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
use Tito10047\BundleMaker\Blueprint\BundleBlueprint;
use Tito10047\BundleMaker\Blueprint\FileTemplate;
use Tito10047\BundleMaker\Options\ConfigFormat;
use Tito10047\BundleMaker\Tests\Support\OptionsFactory;

final class BundleBlueprintTest extends TestCase
{
    public function testExposesOptionsAndComponents(): void
    {
        $options = OptionsFactory::create(features: ['config' => true]);
        $blueprint = new BundleBlueprint($options);

        self::assertSame($options, $blueprint->options);
        self::assertSame($options->naming, $blueprint->naming);
        self::assertTrue($blueprint->hasFeature('config'));
        self::assertFalse($blueprint->hasFeature('unknown'));
        self::assertTrue($blueprint->services->isEmpty());
        self::assertTrue($blueprint->routes->isEmpty());
        self::assertSame([], $blueprint->composer->getRequire());
        self::assertSame([], $blueprint->getFiles());
    }

    public function testCollectsFiles(): void
    {
        $blueprint = new BundleBlueprint(OptionsFactory::create());
        $blueprint->addFile('a.tpl', 'docs/index.md', ['x' => 1]);

        self::assertEquals([
            'docs/index.md' => new FileTemplate('a.tpl', 'docs/index.md', ['x' => 1]),
        ], $blueprint->getFiles());
    }

    public function testTellsWhetherADirectoryHasFiles(): void
    {
        $blueprint = new BundleBlueprint(OptionsFactory::create());
        $blueprint->addFile('a.tpl', 'tests/Integration/BundleTest.php');
        $blueprint->addFile('b.tpl', 'templates.md');

        self::assertTrue($blueprint->hasDirectory('tests'));
        self::assertTrue($blueprint->hasDirectory('tests/'));
        self::assertTrue($blueprint->hasDirectory('/tests/Integration'));
        self::assertFalse($blueprint->hasDirectory('templates'));
        self::assertFalse($blueprint->hasDirectory('tests/Unit'));
        // derived from the blueprint, not contributed
        self::assertFalse($blueprint->hasDirectory('config'));
    }

    public function testRejectsDuplicateDestinations(): void
    {
        $blueprint = new BundleBlueprint(OptionsFactory::create());
        $blueprint->addFile('a.tpl', 'README.md');

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('The file "README.md" is already generated from the template "a.tpl".');

        $blueprint->addFile('b.tpl', 'README.md');
    }

    /**
     * @return iterable<string, array{ConfigFormat, string, string}>
     */
    public static function provideReservedDestinations(): iterable
    {
        yield 'composer.json' => [ConfigFormat::Php, 'composer.json', '$blueprint->composer'];
        yield 'php services' => [ConfigFormat::Php, 'config/services.php', '$blueprint->services'];
        yield 'php-array routes' => [ConfigFormat::PhpArray, 'config/routes.php', '$blueprint->routes'];
        yield 'yaml services' => [ConfigFormat::Yaml, 'config/services.yaml', '$blueprint->services'];
        yield 'yaml routes' => [ConfigFormat::Yaml, 'config/routes.yaml', '$blueprint->routes'];
    }

    #[DataProvider('provideReservedDestinations')]
    public function testRejectsDestinationsDerivedFromTheBlueprint(ConfigFormat $format, string $destination, string $api): void
    {
        $blueprint = new BundleBlueprint(OptionsFactory::create(configFormat: $format));

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage(\sprintf('The file "%s" is generated from the blueprint: use %s instead.', $destination, $api));

        $blueprint->addFile('a.tpl', $destination);
    }
}
