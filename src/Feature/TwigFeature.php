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
 * A Twig template (templates/hello.html.twig), rendered by the controller of the "controllers"
 * feature, or by the application. TwigBundle registers the templates/ directory of every bundle
 * under the "@<ShortName>" namespace, e.g. "@AcmeBlog/hello.html.twig".
 *
 * The template does not extend a layout: the application decides how to lay it out, and can
 * override it in templates/bundles/<Bundle>/.
 *
 * Dependencies: what the templates need is required at runtime when the bundle renders them
 * itself (the "controllers" feature is enabled), and only for the tests otherwise: the
 * application rendering them has it. See TwigFeature::requireForTemplates().
 *
 * @see https://symfony.com/doc/current/bundles/best_practices.html#templates
 * @see https://symfony.com/doc/current/bundles/override.html#templates
 */
final class TwigFeature extends AbstractFeature
{
    public const NAME = 'twig';
    protected const QUESTION = 'Add a Twig template (templates/)?';

    /** The lowest version supported by the Twig bridge of Symfony 7.4. */
    public const TWIG_CONSTRAINT = '^3.21';

    /**
     * Yes when the bundle has controllers: they render the template.
     */
    public function isEnabledByDefault(FeatureSelection $previousAnswers): bool
    {
        return $previousAnswers->isEnabled(ControllersFeature::NAME);
    }

    public function contribute(BundleBlueprint $blueprint): void
    {
        self::requireForTemplates($blueprint, 'symfony/twig-bundle', Constraint::SYMFONY);
        if (self::isRenderedByTheBundle($blueprint)) {
            // the controller type-hints Twig\Environment
            $blueprint->composer->require('twig/twig', self::TWIG_CONSTRAINT);
        }

        $blueprint->addFile('features/twig/templates/hello.html.twig.tpl', 'templates/hello.html.twig');
        $blueprint->addFile('features/twig/tests/Integration/TemplateTest.php.tpl', 'tests/Integration/TemplateTest.php');

        $blueprint->slots->addValue(ListSlot::TestKernelBundles, 'Symfony\Bundle\TwigBundle\TwigBundle');
        $blueprint->slots->addPartial(Slot::TestKernelConfigureContainer, 'features/twig/partials/test_kernel.php.tpl');
        $blueprint->slots->addPartial(Slot::DocsSections, 'features/twig/partials/docs.md.tpl');
    }

    /**
     * Requires a package the templates need, e.g. "symfony/twig-bundle": at runtime when the bundle
     * renders them itself (the "controllers" feature is enabled), for the tests otherwise.
     */
    public static function requireForTemplates(BundleBlueprint $blueprint, string $package, string $constraint): void
    {
        if (self::isRenderedByTheBundle($blueprint)) {
            $blueprint->composer->require($package, $constraint);
        } else {
            $blueprint->composer->requireDev($package, $constraint);
        }
    }

    /**
     * Whether the bundle renders its templates itself (with the controller of the "controllers" feature).
     */
    public static function isRenderedByTheBundle(BundleBlueprint $blueprint): bool
    {
        return $blueprint->hasFeature(self::NAME) && $blueprint->hasFeature(ControllersFeature::NAME);
    }

    /**
     * Name of the template, e.g. "@AcmeBlog/hello.html.twig".
     */
    public static function templateName(BundleNaming $naming): string
    {
        return '@'.$naming->twigNamespace.'/hello.html.twig';
    }

    /**
     * Directory overriding the templates of the bundle in an application, e.g. "templates/bundles/AcmeBlogBundle/".
     */
    public static function overrideDirectory(BundleNaming $naming): string
    {
        return 'templates/bundles/'.$naming->className.'/';
    }
}
