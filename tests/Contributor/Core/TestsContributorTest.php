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
use Tito10047\BundleMaker\Blueprint\ListSlot;
use Tito10047\BundleMaker\Contributor\Core\TestsContributor;
use Tito10047\BundleMaker\Tests\Support\OptionsFactory;

final class TestsContributorTest extends TestCase
{
    public function testContributesTheTestSuite(): void
    {
        $blueprint = new BundleBlueprint(OptionsFactory::create('my-vendor/foo-bundle'));

        (new TestsContributor())->contribute($blueprint);

        self::assertSame(['phpunit.dist.xml', 'tests/App/TestKernel.php', 'tests/bootstrap.php', 'tests/Integration/MyVendorFooBundleTest.php'], array_keys($blueprint->getFiles()));
        self::assertSame([
            'phpunit/phpunit' => TestsContributor::PHPUNIT_CONSTRAINT,
            'symfony/error-handler' => '^7.4.17 || ^8.1.5',
            'symfony/filesystem' => '^7.4.17 || ^8.1.5',
            'symfony/framework-bundle' => '^7.4.17 || ^8.1.5',
        ], $blueprint->composer->getRequireDev());
        self::assertSame([], $blueprint->composer->getRequire());

        $script = $blueprint->composer->getScripts()['test'] ?? null;
        self::assertNotNull($script);
        self::assertSame(['phpunit'], $script->commands);
        self::assertSame(['test'], $blueprint->composer->getQaScriptNames());

        self::assertSame(['/phpunit.dist.xml', '/tests'], $blueprint->slots->getValues(ListSlot::GitAttributesExportIgnore));
        self::assertSame(['/.phpunit.cache/'], $blueprint->slots->getValues(ListSlot::GitIgnore));
    }
}
