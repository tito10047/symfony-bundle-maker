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

namespace Tito10047\BundleMaker\Tests\Contributor\Core;

use PHPUnit\Framework\TestCase;
use Tito10047\BundleMaker\Blueprint\BundleBlueprint;
use Tito10047\BundleMaker\Blueprint\FileTemplate;
use Tito10047\BundleMaker\Contributor\Core\BundleClassContributor;
use Tito10047\BundleMaker\Contributor\Core\DocumentationContributor;
use Tito10047\BundleMaker\Contributor\Core\GreeterContributor;
use Tito10047\BundleMaker\Contributor\Core\LicenseContributor;
use Tito10047\BundleMaker\Contributor\Core\PackageContributor;
use Tito10047\BundleMaker\Contributor\Core\RepositoryContributor;
use Tito10047\BundleMaker\Contributor\Core\TestsContributor;
use Tito10047\BundleMaker\Contributor\CoreContributors;
use Tito10047\BundleMaker\Options\ConfigFormat;
use Tito10047\BundleMaker\Tests\Support\OptionsFactory;

final class CoreContributorsTest extends TestCase
{
    public function testDefaultContributors(): void
    {
        $contributors = iterator_to_array(CoreContributors::createDefault(2030));

        self::assertSame([
            PackageContributor::class,
            BundleClassContributor::class,
            GreeterContributor::class,
            TestsContributor::class,
            DocumentationContributor::class,
            LicenseContributor::class,
            RepositoryContributor::class,
        ], array_map(static fn (object $contributor): string => $contributor::class, $contributors));

        self::assertSame(['year' => 2030], self::renderLicenseParameters(CoreContributors::createDefault(2030)));
    }

    public function testDefaultLicenseYearIsTheCurrentYear(): void
    {
        self::assertSame(['year' => (int) date('Y')], self::renderLicenseParameters(CoreContributors::createDefault()));
    }

    public function testPackageContributor(): void
    {
        $blueprint = new BundleBlueprint(OptionsFactory::create());
        (new PackageContributor())->contribute($blueprint);

        self::assertSame(['symfony', 'symfony-bundle'], $blueprint->composer->getKeywords());
        self::assertSame([
            'php' => '>=8.2',
            'symfony/config' => '^7.4.17 || ^8.1.5',
            'symfony/dependency-injection' => '^7.4.17 || ^8.1.5',
            'symfony/http-kernel' => '^7.4.17 || ^8.1.5',
        ], $blueprint->composer->getRequire());
        self::assertSame([], $blueprint->composer->getRequireDev());
    }

    public function testPackageContributorRequiresYamlForTheYamlFormat(): void
    {
        $blueprint = new BundleBlueprint(OptionsFactory::create(configFormat: ConfigFormat::Yaml));
        (new PackageContributor())->contribute($blueprint);

        self::assertSame('^7.4.17 || ^8.1.5', $blueprint->composer->getRequire()['symfony/yaml'] ?? null);
    }

    public function testBundleClassContributor(): void
    {
        $blueprint = new BundleBlueprint(OptionsFactory::create('my-vendor/foo-bundle'));
        (new BundleClassContributor())->contribute($blueprint);

        self::assertEquals(['src/MyVendorFooBundle.php' => new FileTemplate('core/src/Bundle.php.tpl', 'src/MyVendorFooBundle.php')], $blueprint->getFiles());
    }

    /**
     * @return array<string, mixed>
     */
    private static function renderLicenseParameters(CoreContributors $contributors): array
    {
        $blueprint = new BundleBlueprint(OptionsFactory::create());
        foreach ($contributors as $contributor) {
            if ($contributor instanceof LicenseContributor) {
                $contributor->contribute($blueprint);
            }
        }

        return $blueprint->getFiles()['LICENSE']->parameters;
    }
}
