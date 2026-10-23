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

namespace Tito10047\BundleMaker\Tests\Feature;

use PHPUnit\Framework\TestCase;
use Tito10047\BundleMaker\Blueprint\ServiceTag;
use Tito10047\BundleMaker\Feature\EventListenerFeature;
use Tito10047\BundleMaker\Feature\SemanticConfigFeature;
use Tito10047\BundleMaker\Naming\BundleNaming;
use Tito10047\BundleMaker\Tests\Snapshot\Scenario;

final class EventListenerFeatureTest extends TestCase
{
    public function testIsEnabledInTheSemanticConfiguration(): void
    {
        self::assertSame([SemanticConfigFeature::NAME], (new EventListenerFeature())->getRequiredFeatures());

        $files = (new Scenario('listener', features: ['config', 'event-listener']))->render();

        self::assertStringContainsString("->arrayNode('greeting_header')", $files['config/definition.php']);
        self::assertStringContainsString("if (!\$config['greeting_header']['enabled']) {\n            \$builder->removeDefinition('acme_blog.event_listener.greeting_header');", $files['src/AcmeBlogBundle.php']);
    }

    public function testTheTagOnlyNamesTheEvent(): void
    {
        $services = (new Scenario('listener', features: ['config', 'event-listener']))->createBlueprint()->services->getServices();

        self::assertEquals([new ServiceTag('kernel.event_listener', ['event' => 'kernel.response'])], $services['acme_blog.event_listener.greeting_header']->tags);
    }

    public function testNamesTheHeaderAfterTheBundle(): void
    {
        self::assertSame('X-Acme-Blog-Greeting', EventListenerFeature::headerName(new BundleNaming('acme/blog-bundle')));
        self::assertSame('X-My-Vendor-Foo-Bar-Greeting', EventListenerFeature::headerName(new BundleNaming('my-vendor/foo-bar-bundle')));
    }
}
