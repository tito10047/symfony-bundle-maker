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

namespace Tito10047\BundleMaker\Tests\Feature;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;
use Tito10047\BundleMaker\Blueprint\BundleBlueprint;
use Tito10047\BundleMaker\Blueprint\FileTemplate;
use Tito10047\BundleMaker\Blueprint\ListSlot;
use Tito10047\BundleMaker\Contributor\CoreContributors;
use Tito10047\BundleMaker\Feature\FeatureGroup;
use Tito10047\BundleMaker\Feature\FeatureRegistry;
use Tito10047\BundleMaker\Feature\FeatureSelection;
use Tito10047\BundleMaker\Feature\GitHubActionsFeature;
use Tito10047\BundleMaker\Generator\BundleGenerator;
use Tito10047\BundleMaker\Tests\Support\OptionsFactory;

final class GitHubActionsFeatureTest extends TestCase
{
    public function testDescribesTheQuestion(): void
    {
        $feature = new GitHubActionsFeature();

        self::assertSame('ci', $feature->getName());
        self::assertSame(FeatureGroup::QualityAssurance, $feature->getGroup());
        self::assertStringEndsWith('?', $feature->getQuestion());
        self::assertTrue($feature->isEnabledByDefault(new FeatureSelection()));
    }

    public function testContributesTheWorkflowDependabotAndTheBadge(): void
    {
        $blueprint = new BundleBlueprint(OptionsFactory::create(features: ['ci' => true]));

        (new GitHubActionsFeature())->contribute($blueprint);

        self::assertEquals([
            '.github/workflows/ci.yml' => new FileTemplate('features/ci/.github/workflows/ci.yml.tpl', '.github/workflows/ci.yml'),
            '.github/dependabot.yml' => new FileTemplate('features/ci/.github/dependabot.yml.tpl', '.github/dependabot.yml'),
        ], $blueprint->getFiles());
        self::assertSame(
            ['[![CI](https://github.com/acme/blog-bundle/actions/workflows/ci.yml/badge.svg)](https://github.com/acme/blog-bundle/actions/workflows/ci.yml)'],
            $blueprint->slots->getValues(ListSlot::ReadmeBadges),
        );
        self::assertSame(['/.github'], $blueprint->slots->getValues(ListSlot::GitAttributesExportIgnore));
        self::assertSame([], $blueprint->composer->getRequireDev());
    }

    public function testTestsEverySupportedPhpAndSymfonyVersion(): void
    {
        $tests = self::job(self::renderWorkflow(['ci']), 'tests');

        self::assertSame([
            ['php' => '8.2', 'symfony' => '7.4.*', 'dependencies' => 'lowest'],
            ['php' => '8.3', 'symfony' => '7.4.*'],
            ['php' => '8.4', 'symfony' => '8.1.*', 'dependencies' => 'lowest'],
            ['php' => '8.5', 'symfony' => '7.4.*'],
            ['php' => '8.5', 'symfony' => '8.1.*', 'coverage' => true],
            ['php' => '8.5', 'symfony' => '8.*', 'next-minor' => true],
        ], self::path($tests, 'strategy', 'matrix', 'include'));
        self::assertSame('${{ matrix.next-minor == true }}', $tests['continue-on-error']);

        $steps = self::steps($tests);
        self::assertSame('matrix.next-minor', self::path($steps, 'Find the next minor version of Symfony', 'if'));
        self::assertSame('${{ steps.next-minor.outputs.symfony || matrix.symfony }}', self::path($steps, 'Install dependencies', 'env', 'SYMFONY_REQUIRE'));
    }

    public function testDisablesTheBlockingOfSecurityAdvisoriesWithTheLowestDependenciesOnly(): void
    {
        $steps = self::steps(self::job(self::renderWorkflow(['ci']), 'tests'));

        self::assertSame([
            'name' => 'Allow the versions affected by security advisories',
            'if' => "matrix.dependencies == 'lowest'",
            'run' => 'composer config --global policy.advisories.block false',
        ], $steps['Allow the versions affected by security advisories']);
    }

    public function testMeasuresTheCodeCoverageInTheJobOfTheLatestVersions(): void
    {
        $workflow = self::renderWorkflow(['ci']);
        $tests = self::job($workflow, 'tests');
        $steps = self::steps($tests);

        self::assertSame(['tests', 'qa'], array_keys(self::job($workflow)));
        self::assertSame(95, self::path($tests, 'env', 'MIN_COVERAGE'));
        self::assertSame("\${{ matrix.coverage && 'pcov' || 'none' }}", self::path($steps, 'Set up PHP', 'with', 'coverage'));
        self::assertSame('${{ !matrix.coverage }}', self::path($steps, 'Run the tests', 'if'));
        self::assertSame('composer test', self::path($steps, 'Run the tests', 'run'));
        foreach (['Run the tests with code coverage', 'Check the code coverage', 'Upload the coverage report'] as $name) {
            self::assertSame('matrix.coverage', self::path($steps, $name, 'if'), $name);
        }
    }

    public function testRunsTheQualityChecksButTheTestsOnce(): void
    {
        $steps = self::steps(self::job(self::renderWorkflow(['phpstan', 'cs-fixer', 'ci']), 'qa'));

        self::assertSame('composer phpstan', self::path($steps, 'composer phpstan', 'run'));
        self::assertSame('composer cs', self::path($steps, 'composer cs', 'run'));
        self::assertArrayNotHasKey('composer test', $steps);
    }

    public function testInstallsThePhpExtensionsTheTestsNeed(): void
    {
        $workflow = self::renderWorkflow(['doctrine', 'ci']);

        foreach (['tests', 'qa'] as $job) {
            self::assertSame('pdo_sqlite', self::path(self::steps(self::job($workflow, $job)), 'Set up PHP', 'with', 'extensions'), $job);
        }
    }

    public function testEveryJobHasATimeout(): void
    {
        foreach (self::job(self::renderWorkflow(['phpstan', 'ci'])) as $name => $job) {
            self::assertIsArray($job);
            self::assertIsInt($job['timeout-minutes'] ?? null, (string) $name);
        }
    }

    /**
     * @param list<string> $features the enabled features, the other ones are disabled
     *
     * @return array<mixed>
     */
    private static function renderWorkflow(array $features): array
    {
        $registry = FeatureRegistry::createDefault();
        $answers = [];
        foreach ($registry as $name => $feature) {
            $answers[$name] = \in_array($name, $features, true);
        }

        $generator = new BundleGenerator(CoreContributors::createDefault(), $registry);
        $files = $generator->render($generator->createBlueprint(OptionsFactory::create(features: $answers)));
        $workflow = Yaml::parse($files['.github/workflows/ci.yml']);
        self::assertIsArray($workflow);

        return $workflow;
    }

    /**
     * @param array<mixed> $workflow
     *
     * @return array<mixed> the job, or every job when no name is given
     */
    private static function job(array $workflow, string ...$name): array
    {
        $job = self::path($workflow, 'jobs', ...$name);
        self::assertIsArray($job);

        return $job;
    }

    /**
     * @param array<mixed> $job
     *
     * @return array<string, array<mixed>> the steps indexed by name
     */
    private static function steps(array $job): array
    {
        $list = self::path($job, 'steps');
        self::assertIsArray($list);

        $steps = [];
        foreach ($list as $step) {
            self::assertIsArray($step);
            self::assertIsString($step['name'] ?? null);
            $steps[$step['name']] = $step;
        }

        return $steps;
    }

    /**
     * @param array<mixed> $data
     */
    private static function path(array $data, string ...$keys): mixed
    {
        foreach ($keys as $key) {
            self::assertIsArray($data);
            self::assertArrayHasKey($key, $data, implode('.', $keys));
            $data = $data[$key];
        }

        return $data;
    }
}
