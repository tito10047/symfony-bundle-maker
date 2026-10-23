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

namespace Tito10047\BundleMaker\Blueprint;

/**
 * Text slots of the shared ("master") templates.
 *
 * A slot receives partial templates from any contributor; the master template renders
 * them, ordered by priority (highest first, then registration order), separated by a
 * blank line and indented as the master requires. Partials are written without indentation.
 *
 * Adding a case requires rendering it in the master template that owns it.
 */
enum Slot: string
{
    /**
     * Statements of AcmeBlogBundle::configure(DefinitionConfigurator $definition).
     * The method is only generated when the slot is not empty.
     */
    case BundleConfigure = 'bundle.configure';

    /**
     * Statements of AcmeBlogBundle::loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder),
     * rendered after the import of the services file.
     */
    case BundleLoadExtension = 'bundle.load_extension';

    /**
     * Statements of AcmeBlogBundle::prependExtension(ContainerConfigurator $container, ContainerBuilder $builder).
     * The method is only generated when the slot is not empty.
     */
    case BundlePrependExtension = 'bundle.prepend_extension';

    /**
     * Statements of AcmeBlogBundle::build(ContainerBuilder $container), rendered after "parent::build($container);".
     * The method is only generated when the slot is not empty.
     */
    case BundleBuild = 'bundle.build';

    /**
     * Installation sections of README.md, for every application, e.g. importing the routes or installing the assets.
     *
     * They follow the standard instructions (Flex, then the two steps without Flex). Each partial is one
     * section: its first line is the title (plain text, without "#"), followed by a blank line and the
     * Markdown instructions. The master template renders each as a "### <title>" section.
     *
     * @see https://symfony.com/doc/current/bundles/best_practices.html#installation-instructions
     */
    case ReadmeInstallationSteps = 'readme.installation_steps';

    /** Markdown sections (starting with "## ") of README.md, after the installation instructions. */
    case ReadmeSections = 'readme.sections';

    /**
     * Markdown sections (starting with "## ") of docs/index.md, after the installation section.
     * The configuration reference uses priority 10, so it comes before the usage sections.
     */
    case DocsSections = 'docs.sections';

    /** Statements of the test kernel's configureContainer(ContainerConfigurator $container) method. */
    case TestKernelConfigureContainer = 'test_kernel.configure_container';

    /**
     * Statements of the test kernel's configureRoutes(RoutingConfigurator $routes) method.
     * The method is only generated when the slot is not empty.
     */
    case TestKernelConfigureRoutes = 'test_kernel.configure_routes';
}
