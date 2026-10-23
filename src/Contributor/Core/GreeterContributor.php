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
use Tito10047\BundleMaker\Blueprint\ListSlot;
use Tito10047\BundleMaker\Blueprint\ServiceAlias;
use Tito10047\BundleMaker\Blueprint\ServiceArgument;
use Tito10047\BundleMaker\Blueprint\ServiceDefinition;
use Tito10047\BundleMaker\Blueprint\Slot;
use Tito10047\BundleMaker\Contributor\ContributorInterface;
use Tito10047\BundleMaker\Feature\SemanticConfigFeature;
use Tito10047\BundleMaker\Naming\BundleNaming;

/**
 * The domain example every bundle has, so it is never empty and its tests are meaningful:
 * a greeter service (src/Greeter.php) behind an interface (src/GreeterInterface.php),
 * and the exceptions of the bundle (src/Exception/).
 *
 * Features build on it (a command, a controller or a listener greeting people) through
 * the "<alias>.greeter" service id, see GreeterContributor::serviceId().
 *
 * The greeting is the first argument of the greeter: AcmeBlogBundle::loadExtension() sets it from
 * the semantic configuration when the "config" feature is enabled; otherwise it is the
 * "<alias>.greeting" parameter, defined in config/services.*.
 */
final class GreeterContributor implements ContributorInterface
{
    /** The greeting used by default, e.g. "Hello" in "Hello Jane!". */
    public const DEFAULT_GREETING = 'Hello';

    public function contribute(BundleBlueprint $blueprint): void
    {
        $naming = $blueprint->naming;
        $interface = self::interfaceName($naming);

        $blueprint->addFile('core/src/GreeterInterface.php.tpl', 'src/GreeterInterface.php');
        $blueprint->addFile('core/src/Greeter.php.tpl', 'src/Greeter.php');
        $blueprint->addFile('core/src/Exception/ExceptionInterface.php.tpl', 'src/Exception/ExceptionInterface.php');
        $blueprint->addFile('core/src/Exception/InvalidArgumentException.php.tpl', 'src/Exception/InvalidArgumentException.php');
        $blueprint->addFile('core/tests/Unit/GreeterTest.php.tpl', 'tests/Unit/GreeterTest.php');

        $arguments = [];
        if (!self::isConfigurable($blueprint)) {
            $blueprint->services->setParameter(self::greetingParameter($naming), self::DEFAULT_GREETING);
            $arguments[] = ServiceArgument::parameter(self::greetingParameter($naming));
        }
        $blueprint->services->add(new ServiceDefinition(self::serviceId($naming), $naming->namespace.'\Greeter', $arguments));
        // private, like every service of the bundle: autowiring does not need a public alias
        $blueprint->services->addAlias(new ServiceAlias($interface, self::serviceId($naming)));

        $blueprint->slots->addValue(ListSlot::TestKernelUses, $interface);
        $blueprint->slots->addPartial(Slot::TestKernelConfigureContainer, 'core/tests/partials/kernel_greeter_alias.php.tpl');
        $blueprint->slots->addPartial(Slot::DocsSections, 'core/docs/partials/usage.md.tpl');
    }

    /**
     * Whether the greeting is set from the semantic configuration (the "config" feature is enabled),
     * rather than defined as a parameter in config/services.*.
     */
    public static function isConfigurable(BundleBlueprint $blueprint): bool
    {
        return $blueprint->hasFeature(SemanticConfigFeature::NAME);
    }

    /**
     * Id of the greeter service, e.g. "acme_blog.greeter".
     */
    public static function serviceId(BundleNaming $naming): string
    {
        return $naming->alias.'.greeter';
    }

    /**
     * Name of the parameter holding the greeting, e.g. "acme_blog.greeting".
     */
    public static function greetingParameter(BundleNaming $naming): string
    {
        return $naming->alias.'.greeting';
    }

    /**
     * FQCN of the greeter interface, e.g. "Acme\BlogBundle\GreeterInterface".
     */
    public static function interfaceName(BundleNaming $naming): string
    {
        return $naming->namespace.'\GreeterInterface';
    }
}
