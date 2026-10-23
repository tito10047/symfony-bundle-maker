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
use Tito10047\BundleMaker\Blueprint\RouteDefinition;
use Tito10047\BundleMaker\Blueprint\ServiceArgument;
use Tito10047\BundleMaker\Blueprint\ServiceDefinition;
use Tito10047\BundleMaker\Blueprint\ServiceTag;
use Tito10047\BundleMaker\Blueprint\Slot;
use Tito10047\BundleMaker\Contributor\Core\GreeterContributor;
use Tito10047\BundleMaker\Naming\BundleNaming;

/**
 * A controller greeting people (src/Controller/HelloController.php) and its route (config/routes.*),
 * which applications import.
 *
 * The controller is a final invokable class, without AbstractController: its dependencies are
 * injected explicitly. It returns plain text. Its functional test only checks the responses.
 *
 * The bundle requires FrameworkBundle: it registers the controllers of the bundles
 * ("controller.service_arguments" tag) and imports their routes.
 *
 * @see https://symfony.com/doc/current/bundles/best_practices.html#controllers
 * @see https://symfony.com/doc/current/bundles/best_practices.html#routing
 */
final class ControllersFeature extends AbstractFeature
{
    public const NAME = 'controllers';
    protected const QUESTION = 'Add a controller and its route (config/routes.*)?';

    /** Name greeted when the URL has none, e.g. "Hello World!" for "/hello". */
    public const DEFAULT_NAME = 'World';

    public function contribute(BundleBlueprint $blueprint): void
    {
        $naming = $blueprint->naming;
        $serviceId = $naming->alias.'.controller.hello';

        $arguments = [ServiceArgument::service(GreeterContributor::serviceId($naming))];

        // "symfony/routing" is required as soon as a route exists
        $blueprint->composer->require('symfony/framework-bundle', Constraint::SYMFONY);
        $blueprint->composer->require('symfony/http-foundation', Constraint::SYMFONY);
        $blueprint->composer->requireDev('symfony/browser-kit', Constraint::SYMFONY);

        $blueprint->addFile('features/controllers/src/Controller/HelloController.php.tpl', 'src/Controller/HelloController.php');
        $blueprint->addFile('features/controllers/tests/Functional/HelloControllerTest.php.tpl', 'tests/Functional/HelloControllerTest.php');

        // the tag makes the service public, as the controller resolver requires:
        // the definition stays private, like every service of the bundle
        $blueprint->services->add(new ServiceDefinition($serviceId, self::className($naming), $arguments, [
            new ServiceTag('controller.service_arguments'),
        ]));
        // the placeholder has the default requirement: anything but a slash, UTF-8 included
        $blueprint->routes->add(new RouteDefinition(self::routeName($naming), '/hello/{name}', $serviceId, ['GET'], ['name' => self::DEFAULT_NAME]));

        $blueprint->slots->addValue(ListSlot::TestKernelUses, 'Psr\Log\NullLogger');
        $blueprint->slots->addPartial(Slot::TestKernelConfigureContainer, 'features/controllers/partials/test_kernel.php.tpl');
        $blueprint->slots->addPartial(Slot::TestKernelConfigureRoutes, 'features/controllers/partials/test_kernel_routes.php.tpl');
        $blueprint->slots->addPartial(Slot::ReadmeInstallationSteps, 'features/controllers/partials/readme_step.md.tpl');
        $blueprint->slots->addPartial(Slot::DocsSections, 'features/controllers/partials/docs.md.tpl');
    }

    /**
     * FQCN of the controller, e.g. "Acme\BlogBundle\Controller\HelloController".
     */
    public static function className(BundleNaming $naming): string
    {
        return $naming->namespace.'\Controller\HelloController';
    }

    /**
     * Name of the route, e.g. "acme_blog_hello".
     */
    public static function routeName(BundleNaming $naming): string
    {
        return $naming->alias.'_hello';
    }
}
