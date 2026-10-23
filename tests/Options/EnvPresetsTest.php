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

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Tito10047\BundleMaker\Options\EnvPresets;
use Tito10047\BundleMaker\Options\InvalidPresetException;
use Tito10047\BundleMaker\Options\OptionNormalizer;

final class EnvPresetsTest extends TestCase
{
    public function testReadsTrimmedValuesAndIgnoresEmptyOnes(): void
    {
        $presets = new EnvPresets([EnvPresets::NAME => ' acme/blog-bundle ', EnvPresets::DESCRIPTION => '  ']);

        self::assertSame('acme/blog-bundle', $presets->get(EnvPresets::NAME));
        self::assertNull($presets->get(EnvPresets::DESCRIPTION));
        self::assertNull($presets->get(EnvPresets::LICENSE));
        self::assertSame([EnvPresets::NAME], $presets->getNames());
    }

    /**
     * @return iterable<array{string, bool}>
     */
    public static function provideBooleans(): iterable
    {
        foreach (['1', 'true', 'TRUE', 'yes', 'y', 'on'] as $value) {
            yield $value => [$value, true];
        }
        foreach (['0', 'false', 'No', 'n', 'off'] as $value) {
            yield $value => [$value, false];
        }
    }

    #[DataProvider('provideBooleans')]
    public function testParsesBooleans(string $value, bool $expected): void
    {
        self::assertSame($expected, (new EnvPresets([EnvPresets::ALL => $value]))->getBool(EnvPresets::ALL));
    }

    public function testUnsetBooleansAreNull(): void
    {
        self::assertNull((new EnvPresets())->getBool(EnvPresets::ALL));
    }

    public function testRejectsInvalidBooleans(): void
    {
        $this->expectException(InvalidPresetException::class);
        $this->expectExceptionMessage('Invalid value for the environment variable "BUNDLE_MAKER_ALL": "maybe" is not a boolean, use 1 or 0.');

        (new EnvPresets([EnvPresets::ALL => 'maybe']))->getBool(EnvPresets::ALL);
    }

    public function testNormalizesValidValues(): void
    {
        $presets = new EnvPresets([EnvPresets::NAME => 'acme/blog-bundle']);

        self::assertSame('AcmeBlogBundle', $presets->getValid(EnvPresets::NAME, (new OptionNormalizer())->packageName(...))?->className);
        self::assertNull($presets->getValid(EnvPresets::LICENSE, (new OptionNormalizer())->license(...)));
    }

    public function testRejectsInvalidValues(): void
    {
        $this->expectException(InvalidPresetException::class);
        $this->expectExceptionMessage('Invalid value for the environment variable "BUNDLE_MAKER_LICENSE": "WTFPL" is not a supported license.');

        (new EnvPresets([EnvPresets::LICENSE => 'WTFPL']))->getValid(EnvPresets::LICENSE, (new OptionNormalizer())->license(...));
    }

    public function testFeatureVariables(): void
    {
        self::assertSame('BUNDLE_MAKER_EVENT_LISTENER', EnvPresets::featureVariable('event-listener'));
        self::assertSame('BUNDLE_MAKER_CONFIG', EnvPresets::featureVariable('config'));
    }

    public function testReadsTheEnvironment(): void
    {
        putenv('BUNDLE_MAKER_DESCRIPTION=From the environment');
        putenv('OTHER_VARIABLE=ignored');

        try {
            $presets = EnvPresets::fromEnvironment();

            self::assertSame('From the environment', $presets->get(EnvPresets::DESCRIPTION));
            self::assertNull($presets->get('OTHER_VARIABLE'));
        } finally {
            putenv('BUNDLE_MAKER_DESCRIPTION');
            putenv('OTHER_VARIABLE');
        }
    }
}
