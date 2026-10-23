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

namespace Tito10047\BundleMaker\Contributor\Core;

use Tito10047\BundleMaker\Blueprint\BundleBlueprint;
use Tito10047\BundleMaker\Blueprint\ComposerManifest;
use Tito10047\BundleMaker\Blueprint\Constraint;
use Tito10047\BundleMaker\Blueprint\ListSlot;
use Tito10047\BundleMaker\Contributor\ContributorInterface;

/**
 * The test suite every bundle has, run with "composer test" (a plain "phpunit"):
 *
 *  - phpunit.dist.xml, failing on any deprecation triggered by the code of the bundle;
 *  - tests/App/TestKernel.php, a minimal application registering the bundle (see the TestKernel* slots);
 *  - tests/bootstrap.php, removing the caches of the test kernels, so that every run compiles fresh containers;
 *  - tests/Integration/<BundleClass>Test.php, a smoke test booting it;
 *  - unit tests in tests/Unit/, integration tests in tests/Integration/, functional tests in tests/Functional/.
 *
 * @see https://symfony.com/doc/current/bundles/best_practices.html#tests
 */
final class TestsContributor implements ContributorInterface
{
    /**
     * PHPUnit 11.5 is the last version supporting PHP 8.2, PHPUnit 12 requires PHP 8.3 and 13 PHP 8.4:
     * Composer installs the latest one the PHP version supports.
     */
    public const PHPUNIT_CONSTRAINT = '^11.5 || ^12.0 || ^13.0';

    public function contribute(BundleBlueprint $blueprint): void
    {
        $naming = $blueprint->naming;

        $blueprint->composer->requireDev('phpunit/phpunit', self::PHPUNIT_CONSTRAINT);
        // the test kernel (MicroKernelTrait) and KernelTestCase
        $blueprint->composer->requireDev('symfony/framework-bundle', Constraint::SYMFONY);
        // not required by the bundle itself, but its lowest versions leak an exception handler, see Constraint::SYMFONY
        $blueprint->composer->requireDev('symfony/error-handler', Constraint::SYMFONY);
        // tests/bootstrap.php removes the caches of the test kernels
        $blueprint->composer->requireDev('symfony/filesystem', Constraint::SYMFONY);
        $blueprint->composer->addScript(ComposerManifest::TEST_SCRIPT, 'phpunit', 'Runs the tests (PHPUnit)', qa: true);

        $blueprint->addFile('core/phpunit.dist.xml.tpl', 'phpunit.dist.xml');
        $blueprint->addFile('core/tests/App/TestKernel.php.tpl', 'tests/App/TestKernel.php');
        $blueprint->addFile('core/tests/bootstrap.php.tpl', 'tests/bootstrap.php');
        $blueprint->addFile('core/tests/Integration/BundleTest.php.tpl', 'tests/Integration/'.$naming->className.'Test.php');

        $blueprint->slots->addValue(ListSlot::GitAttributesExportIgnore, '/phpunit.dist.xml', '/tests');
        $blueprint->slots->addValue(ListSlot::GitIgnore, '/.phpunit.cache/');
    }
}
