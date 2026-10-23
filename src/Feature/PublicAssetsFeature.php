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

namespace Tito10047\BundleMaker\Feature;

use Tito10047\BundleMaker\Blueprint\BundleBlueprint;
use Tito10047\BundleMaker\Blueprint\Constraint;
use Tito10047\BundleMaker\Blueprint\Slot;
use Tito10047\BundleMaker\Naming\BundleNaming;

/**
 * Web assets served as is: a stylesheet in public/, e.g. public/css/acme-blog.css.
 *
 * The "assets:install" command of FrameworkBundle copies (or links) the public/ directory of
 * every bundle to the public/bundles/<shortname>/ directory of the application, e.g.
 * public/bundles/acmeblog/; AssetMapper serves it under the same path. Applications link the
 * stylesheet in their layout: the template of the "twig" feature is a fragment, it does not.
 *
 * @see https://symfony.com/doc/current/bundles/best_practices.html#directory-structure
 */
final class PublicAssetsFeature extends AbstractFeature
{
    public const NAME = 'public-assets';
    protected const QUESTION = 'Add public web assets (public/, installed with assets:install)?';

    public function contribute(BundleBlueprint $blueprint): void
    {
        $blueprint->composer->require('symfony/framework-bundle', Constraint::SYMFONY);
        // the test installs the assets with the "assets:install" command
        $blueprint->composer->requireDev('symfony/console', Constraint::SYMFONY);

        $blueprint->addFile('features/public-assets/public/css/stylesheet.css.tpl', 'public/'.self::stylesheetPath($blueprint->naming));
        $blueprint->addFile('features/public-assets/tests/Integration/PublicAssetsTest.php.tpl', 'tests/Integration/PublicAssetsTest.php');

        $blueprint->slots->addPartial(Slot::ReadmeInstallationSteps, 'features/public-assets/partials/readme_step.md.tpl');
        $blueprint->slots->addPartial(Slot::DocsSections, 'features/public-assets/partials/docs.md.tpl');
    }

    /**
     * Path of the stylesheet in the public/ directory, e.g. "css/acme-blog.css".
     */
    public static function stylesheetPath(BundleNaming $naming): string
    {
        return 'css/'.$naming->slug.'.css';
    }

    /**
     * Public path of the stylesheet in the application, e.g. "bundles/acmeblog/css/acme-blog.css".
     */
    public static function stylesheetPublicPath(BundleNaming $naming): string
    {
        return $naming->publicAssetsDirectory.'/'.self::stylesheetPath($naming);
    }
}
