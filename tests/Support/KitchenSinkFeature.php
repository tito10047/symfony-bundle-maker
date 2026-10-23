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

namespace Tito10047\BundleMaker\Tests\Support;

use Tito10047\BundleMaker\Blueprint\BundleBlueprint;
use Tito10047\BundleMaker\Blueprint\Constraint;
use Tito10047\BundleMaker\Blueprint\ListSlot;
use Tito10047\BundleMaker\Blueprint\RouteDefinition;
use Tito10047\BundleMaker\Blueprint\ServiceAlias;
use Tito10047\BundleMaker\Blueprint\ServiceArgument;
use Tito10047\BundleMaker\Blueprint\ServiceDefinition;
use Tito10047\BundleMaker\Blueprint\ServiceTag;
use Tito10047\BundleMaker\Blueprint\Slot;
use Tito10047\BundleMaker\Feature\AbstractFeature;

/**
 * Test-only feature exercising every contribution point of the blueprint,
 * so snapshots cover each rendering branch in every configuration format.
 */
final class KitchenSinkFeature extends AbstractFeature
{
    public const NAME = 'kitchen-sink';
    protected const QUESTION = 'Add every possible contribution?';
    public const TEMPLATE_DIR = __DIR__.'/templates';

    public function contribute(BundleBlueprint $blueprint): void
    {
        $naming = $blueprint->naming;
        $alias = $naming->alias;

        $blueprint->composer->require('symfony/http-foundation', Constraint::SYMFONY);
        // already required by a core contributor with the same constraint: no-op
        $blueprint->composer->requireDev('symfony/framework-bundle', Constraint::SYMFONY);
        $blueprint->composer->requireDev('symfony/browser-kit', Constraint::SYMFONY);
        $blueprint->composer->addKeyword('kitchen-sink');
        $blueprint->composer->addScript('lint', ['@php -l src/', '@test'], qa: true);
        $blueprint->composer->addScript('kitchen-sink', 'echo kitchen-sink', 'Checks the kitchen sink');
        $blueprint->composer->allowPlugin('phpstan/extension-installer');

        $blueprint->services->setParameter($alias.'.kitchen_sink.greeting', 'Hello %name%');
        $blueprint->services->setParameter($alias.'.channels', ['email', '@sms', 'it\'s']);
        $blueprint->services->setParameter($alias.'.enabled', true);

        $blueprint->services->add(new ServiceDefinition($alias.'.kitchen_sink', $naming->namespace.'\\KitchenSink', [
            ServiceArgument::parameter($alias.'.kitchen_sink.greeting'),
            ServiceArgument::service($alias.'.formatter'),
        ]));
        $blueprint->services->add(new ServiceDefinition($alias.'.formatter', $naming->namespace.'\\Formatter\\Formatter'));
        $blueprint->services->add(new ServiceDefinition('.'.$alias.'.cache_warmer', $naming->namespace.'\\CacheWarmer\\Formatter', [
            ServiceArgument::service($alias.'.formatter'),
        ], [
            new ServiceTag('kernel.cache_warmer', ['priority' => 10]),
        ]));
        $blueprint->services->add(new ServiceDefinition($alias.'.controller.hello', $naming->namespace.'\\Controller\\HelloController', [
            ServiceArgument::service($alias.'.greeter'),
        ], [
            new ServiceTag('controller.service_arguments'),
        ]));
        $blueprint->services->addAlias(new ServiceAlias($naming->namespace.'\\Formatter\\FormatterInterface', $alias.'.formatter'));
        $blueprint->services->addAlias(new ServiceAlias($alias.'.default_greeter', $alias.'.greeter'));

        $blueprint->routes->add(new RouteDefinition($alias.'_hello', '/hello/{name}', $alias.'.controller.hello', ['GET', 'HEAD'], ['name' => 'World'], ['name' => '\\w+']));
        $blueprint->routes->add(new RouteDefinition($alias.'_page', '/page/{page}.{_format}', $alias.'.controller.hello::page', defaults: ['page' => 1, '_format' => 'html'], requirements: ['page' => '\\d+', '_format' => 'html|json']));
        $blueprint->routes->add(new RouteDefinition($alias.'_home', '/', $alias.'.controller.hello'));

        $blueprint->slots->addValue(ListSlot::BundleUses, 'Symfony\\Component\\DependencyInjection\\Compiler\\PassConfig');
        $blueprint->slots->addPartial(Slot::BundleLoadExtension, 'kitchen-sink/partials/load_extension.php.tpl');
        $blueprint->slots->addPartial(Slot::BundlePrependExtension, 'kitchen-sink/partials/prepend_extension.php.tpl', ['extension' => 'framework']);
        $blueprint->slots->addPartial(Slot::BundleBuild, 'kitchen-sink/partials/build.php.tpl');
        $blueprint->slots->addPartial(Slot::BundleBuild, 'kitchen-sink/partials/build_first.php.tpl', priority: 10);

        $blueprint->slots->addValue(ListSlot::ReadmeBadges, '[![Kitchen sink](https://img.shields.io/badge/kitchen-sink-blue)](https://example.com)', '[![PHP](https://img.shields.io/badge/php-8.2-blue)](https://php.net)');
        $blueprint->slots->addPartial(Slot::ReadmeInstallationSteps, 'kitchen-sink/partials/readme_step.md.tpl', ['title' => 'Import the Routes']);
        $blueprint->slots->addPartial(Slot::ReadmeInstallationSteps, 'kitchen-sink/partials/readme_step.md.tpl', ['title' => 'Install the Assets'], priority: -10);
        $blueprint->slots->addPartial(Slot::ReadmeSections, 'kitchen-sink/partials/section.md.tpl', ['title' => 'Kitchen Sink']);
        $blueprint->slots->addPartial(Slot::DocsSections, 'kitchen-sink/partials/section.md.tpl', ['title' => 'Kitchen Sink']);

        $blueprint->slots->addValue(ListSlot::TestKernelBundles, 'Symfony\\Bundle\\TwigBundle\\TwigBundle');
        $blueprint->slots->addValue(ListSlot::TestKernelUses, 'Symfony\\Component\\HttpKernel\\KernelEvents');
        $blueprint->slots->addPartial(Slot::TestKernelConfigureContainer, 'kitchen-sink/partials/kernel_configure_container.php.tpl');
        $blueprint->slots->addPartial(Slot::TestKernelConfigureRoutes, 'kitchen-sink/partials/kernel_configure_routes.php.tpl');

        $blueprint->slots->addValue(ListSlot::GitAttributesExportIgnore, '/kitchen-sink.dist.php');
        $blueprint->slots->addValue(ListSlot::GitIgnore, '/.kitchen-sink.cache', '/vendor/');

        $blueprint->slots->addPartial(Slot::PhpStanParameters, 'kitchen-sink/partials/phpstan_parameters.neon.tpl');

        $blueprint->addFile('kitchen-sink/NOTES.md.tpl', 'docs/notes.md', ['note' => 'Generated for <'.$naming->className.'>']);
    }
}
