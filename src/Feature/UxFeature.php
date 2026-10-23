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
use Tito10047\BundleMaker\Blueprint\ListSlot;
use Tito10047\BundleMaker\Blueprint\Slot;
use Tito10047\BundleMaker\Naming\BundleNaming;

/**
 * A Symfony UX bundle: a Stimulus controller (assets/src/hello_controller.js) declared in
 * assets/package.json, and the "symfony-ux" keyword, so Symfony Flex enables the controller
 * in the assets/controllers.json file of applications.
 *
 * The controller is plain JavaScript (an ES module), shipped without build step. When the
 * application uses AssetMapper, the bundle adds assets/src/ to its paths, under the
 * "@<vendor>/<package>" namespace (the name of assets/package.json), in prependExtension().
 *
 * The template of the "twig" feature uses the controller with the stimulus_*() functions
 * of StimulusBundle.
 *
 * @see https://symfony.com/doc/current/frontend/create_ux_bundle.html
 */
final class UxFeature extends AbstractFeature
{
    public const NAME = 'ux';
    protected const QUESTION = 'Add a Stimulus controller (Symfony UX, assets/)?';

    /** Name of the controller in assets/package.json. */
    public const CONTROLLER = 'hello';

    /** StimulusBundle 2.13 is the first version supporting Symfony 7, 3.x requires PHP 8.4. */
    public const STIMULUS_BUNDLE_CONSTRAINT = '^2.13 || ^3.0';

    /** Version of Stimulus required by the controller, as a peer dependency. */
    public const STIMULUS_CONSTRAINT = '^3.0.0';

    public function contribute(BundleBlueprint $blueprint): void
    {
        $blueprint->composer->addKeyword('symfony-ux');
        // the bundle only exposes its controllers to AssetMapper when the application uses it
        $blueprint->composer->requireDev('symfony/asset-mapper', Constraint::SYMFONY);
        if ($blueprint->hasFeature(TwigFeature::NAME)) {
            // the stimulus_*() functions of the template
            TwigFeature::requireForTemplates($blueprint, 'symfony/stimulus-bundle', self::STIMULUS_BUNDLE_CONSTRAINT);
            $blueprint->slots->addValue(ListSlot::TestKernelBundles, 'Symfony\UX\StimulusBundle\StimulusBundle');
        }

        $blueprint->addFile('features/ux/assets/package.json.tpl', 'assets/package.json');
        $blueprint->addFile('features/ux/assets/src/controller.js.tpl', 'assets/src/'.self::CONTROLLER.'_controller.js');
        $blueprint->addFile('features/ux/tests/Integration/AssetMapperTest.php.tpl', 'tests/Integration/AssetMapperTest.php');

        $blueprint->slots->addValue(ListSlot::BundleUses, 'Symfony\Component\AssetMapper\AssetMapperInterface');
        $blueprint->slots->addPartial(Slot::BundlePrependExtension, 'features/ux/partials/bundle_prepend_extension.php.tpl');
        $blueprint->slots->addPartial(Slot::ReadmeInstallationSteps, 'features/ux/partials/readme_step.md.tpl');
        $blueprint->slots->addPartial(Slot::DocsSections, 'features/ux/partials/docs.md.tpl');
    }

    /**
     * Name of the package in assets/package.json, and namespace of its files in AssetMapper:
     * "@" followed by the Composer package name, e.g. "@acme/blog-bundle". StimulusBundle
     * finds the PHP package from this name.
     */
    public static function packageName(BundleNaming $naming): string
    {
        return '@'.$naming->packageName;
    }

    /**
     * Name of the controller for the stimulus_*() Twig functions, e.g. "acme/blog-bundle/hello".
     */
    public static function controllerName(BundleNaming $naming): string
    {
        return $naming->packageName.'/'.self::CONTROLLER;
    }

    /**
     * Identifier of the controller in HTML attributes, e.g. "acme--blog-bundle--hello",
     * normalized like StimulusBundle does.
     */
    public static function controllerIdentifier(BundleNaming $naming): string
    {
        return str_replace(['_', '/'], ['-', '--'], self::controllerName($naming));
    }
}
