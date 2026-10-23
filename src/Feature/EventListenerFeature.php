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
use Tito10047\BundleMaker\Blueprint\ServiceArgument;
use Tito10047\BundleMaker\Blueprint\ServiceDefinition;
use Tito10047\BundleMaker\Blueprint\ServiceTag;
use Tito10047\BundleMaker\Blueprint\Slot;
use Tito10047\BundleMaker\Contributor\Core\GreeterContributor;
use Tito10047\BundleMaker\Naming\BundleNaming;

/**
 * An event listener adding a greeting header to the responses of the application
 * (src/EventListener/GreetingHeaderListener.php), e.g. "X-Acme-Blog-Greeting: Hello World!".
 *
 * A bundle must not change the responses of an application unasked: the listener is disabled
 * by default, and enabled with the "greeting_header" option of the semantic configuration,
 * which this feature requires. AcmeBlogBundle::loadExtension() removes it when disabled.
 *
 * It listens to "kernel.response", and is registered explicitly with a "kernel.event_listener"
 * tag naming its event: the method defaults to "onKernelResponse" and the priority to 0.
 *
 * @see https://symfony.com/doc/current/event_dispatcher.html
 */
final class EventListenerFeature extends AbstractFeature
{
    public const NAME = 'event-listener';
    protected const QUESTION = 'Add an event listener (opt-in through the configuration)?';
    protected const REQUIRED_FEATURES = [SemanticConfigFeature::NAME];

    public function contribute(BundleBlueprint $blueprint): void
    {
        $naming = $blueprint->naming;

        // the listener uses the Request and Response of the event; the tests dispatch it
        $blueprint->composer->require('symfony/http-foundation', Constraint::SYMFONY);
        $blueprint->composer->requireDev('symfony/event-dispatcher', Constraint::SYMFONY);

        $blueprint->addFile('features/event-listener/src/EventListener/GreetingHeaderListener.php.tpl', 'src/EventListener/GreetingHeaderListener.php');
        $blueprint->addFile('features/event-listener/tests/Unit/EventListener/GreetingHeaderListenerTest.php.tpl', 'tests/Unit/EventListener/GreetingHeaderListenerTest.php');

        $blueprint->services->add(new ServiceDefinition(
            self::serviceId($naming),
            $naming->namespace.'\EventListener\GreetingHeaderListener',
            [ServiceArgument::service(GreeterContributor::serviceId($naming))],
            [new ServiceTag('kernel.event_listener', ['event' => 'kernel.response'])],
        ));

        $blueprint->slots->addPartial(Slot::BundleLoadExtension, 'features/event-listener/partials/bundle_load_extension.php.tpl');
        $blueprint->slots->addPartial(Slot::DocsSections, 'features/event-listener/partials/docs.md.tpl');
    }

    /**
     * Id of the listener service, e.g. "acme_blog.event_listener.greeting_header".
     */
    public static function serviceId(BundleNaming $naming): string
    {
        return $naming->alias.'.event_listener.greeting_header';
    }

    /**
     * Name of the header added to the responses, e.g. "X-Acme-Blog-Greeting" (prefixed with the
     * bundle name, so it cannot collide with the headers of the application or of other bundles).
     */
    public static function headerName(BundleNaming $naming): string
    {
        return 'X-'.implode('-', array_map(ucfirst(...), explode('-', $naming->slug))).'-Greeting';
    }
}
