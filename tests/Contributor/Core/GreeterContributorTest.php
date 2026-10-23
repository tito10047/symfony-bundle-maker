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

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Tito10047\BundleMaker\Blueprint\BundleBlueprint;
use Tito10047\BundleMaker\Blueprint\ListSlot;
use Tito10047\BundleMaker\Blueprint\Partial;
use Tito10047\BundleMaker\Blueprint\Slot;
use Tito10047\BundleMaker\Contributor\Core\GreeterContributor;
use Tito10047\BundleMaker\Tests\Support\OptionsFactory;

final class GreeterContributorTest extends TestCase
{
    public function testContributesTheGreeterAndItsTests(): void
    {
        $blueprint = new BundleBlueprint(OptionsFactory::create(features: ['config' => true]));

        (new GreeterContributor())->contribute($blueprint);

        self::assertSame([
            'src/GreeterInterface.php',
            'src/Greeter.php',
            'src/Exception/ExceptionInterface.php',
            'src/Exception/InvalidArgumentException.php',
            'tests/Unit/GreeterTest.php',
        ], array_keys($blueprint->getFiles()));

        $service = $blueprint->services->getServices()['acme_blog.greeter'] ?? null;
        self::assertNotNull($service);
        self::assertSame('Acme\BlogBundle\Greeter', $service->class);
        // set from the semantic configuration by loadExtension()
        self::assertSame([], $service->arguments);

        $alias = $blueprint->services->getAliases()['Acme\BlogBundle\GreeterInterface'] ?? null;
        self::assertNotNull($alias);
        self::assertSame('acme_blog.greeter', $alias->target);

        self::assertSame(['Acme\BlogBundle\GreeterInterface'], $blueprint->slots->getValues(ListSlot::TestKernelUses));
        self::assertEquals([new Partial('core/tests/partials/kernel_greeter_alias.php.tpl')], $blueprint->slots->getPartials(Slot::TestKernelConfigureContainer));
        self::assertEquals([new Partial('core/docs/partials/usage.md.tpl')], $blueprint->slots->getPartials(Slot::DocsSections));
    }

    public function testTheSemanticConfigurationSetsTheGreeting(): void
    {
        $blueprint = new BundleBlueprint(OptionsFactory::create(features: ['config' => true]));

        (new GreeterContributor())->contribute($blueprint);

        self::assertTrue(GreeterContributor::isConfigurable($blueprint));
        self::assertSame([], $blueprint->services->getParameters());
    }

    /**
     * @return iterable<string, array{array<string, bool>}>
     */
    public static function provideFeaturesWithoutSemanticConfiguration(): iterable
    {
        yield 'disabled' => [['config' => false]];
        yield 'not registered' => [[]];
    }

    /**
     * @param array<string, bool> $features
     */
    #[DataProvider('provideFeaturesWithoutSemanticConfiguration')]
    public function testAParameterSetsTheGreetingWithoutSemanticConfiguration(array $features): void
    {
        $blueprint = new BundleBlueprint(OptionsFactory::create(features: $features));

        (new GreeterContributor())->contribute($blueprint);

        self::assertFalse(GreeterContributor::isConfigurable($blueprint));
        self::assertSame(['acme_blog.greeting' => 'Hello'], $blueprint->services->getParameters());
        self::assertSame('acme_blog.greeting', $blueprint->services->getServices()['acme_blog.greeter']->arguments[0]->getReference());
    }

    public function testNames(): void
    {
        $naming = OptionsFactory::create('my-vendor/foo-bundle')->naming;

        self::assertSame('my_vendor_foo.greeter', GreeterContributor::serviceId($naming));
        self::assertSame('my_vendor_foo.greeting', GreeterContributor::greetingParameter($naming));
        self::assertSame('MyVendor\FooBundle\GreeterInterface', GreeterContributor::interfaceName($naming));
    }
}
