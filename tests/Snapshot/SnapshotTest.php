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

namespace Tito10047\BundleMaker\Tests\Snapshot;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Tito10047\BundleMaker\Tests\Support\Filesystem;

/**
 * Compares the generated bundles with the fixtures of tests/Fixtures/<scenario>/.
 *
 * Run "UPDATE_SNAPSHOTS=1 vendor/bin/phpunit" to regenerate the fixtures, then review the diff.
 */
final class SnapshotTest extends TestCase
{
    /**
     * @return iterable<string, array{Scenario}>
     */
    public static function provideScenarios(): iterable
    {
        foreach (Scenarios::all() as $name => $scenario) {
            yield $name => [$scenario];
        }
    }

    #[DataProvider('provideScenarios')]
    public function testGeneratedBundleMatchesTheSnapshot(Scenario $scenario): void
    {
        $features = Scenarios::createFeatureRegistry();
        $dir = Filesystem::createTempDir('snapshot-'.$scenario->name);
        copy(__DIR__.'/skeleton.composer.json', $dir.'/composer.json');

        try {
            Scenarios::createGenerator($features)->generate($scenario->createOptions($features), $dir);
            $actual = Filesystem::readTree($dir);
        } finally {
            Filesystem::remove($dir);
        }

        if (self::isUpdating()) {
            $this->updateSnapshot($scenario->getFixtureDir(), $actual);
        }

        self::assertDirectoryExists($scenario->getFixtureDir(), 'Run "UPDATE_SNAPSHOTS=1 vendor/bin/phpunit" to create the snapshot.');
        $expected = Filesystem::readTree($scenario->getFixtureDir());

        self::assertSame(array_keys($expected), array_keys($actual), 'The generated files differ from the snapshot.');
        foreach ($expected as $path => $content) {
            self::assertSame($content, $actual[$path], \sprintf('The generated file "%s" differs from the snapshot.', $path));
        }
    }

    public function testEveryFixtureBelongsToAScenario(): void
    {
        $directories = glob(\dirname(__DIR__).'/Fixtures/*', \GLOB_ONLYDIR);
        $fixtures = array_map(basename(...), false === $directories ? [] : $directories);
        $orphans = array_diff($fixtures, array_keys(Scenarios::all()));

        if (self::isUpdating()) {
            foreach ($orphans as $orphan) {
                Filesystem::remove(\dirname(__DIR__).'/Fixtures/'.$orphan);
            }
            $orphans = [];
        }

        self::assertSame([], array_values($orphans), 'These fixtures have no scenario anymore.');
    }

    private static function isUpdating(): bool
    {
        return '1' === getenv('UPDATE_SNAPSHOTS');
    }

    /**
     * @param array<string, string> $files
     */
    private function updateSnapshot(string $fixtureDir, array $files): void
    {
        Filesystem::remove($fixtureDir);
        foreach ($files as $path => $content) {
            $file = $fixtureDir.'/'.$path;
            if (!is_dir(\dirname($file))) {
                mkdir(\dirname($file), 0o777, true);
            }
            file_put_contents($file, $content);
        }
    }
}
