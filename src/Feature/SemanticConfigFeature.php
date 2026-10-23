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
use Tito10047\BundleMaker\Blueprint\Slot;

/**
 * Semantic configuration: the configuration tree lives in config/definition.php and is imported
 * by AcmeBlogBundle::configure(). AcmeBlogBundle::loadExtension() passes the processed
 * configuration to the services as arguments, e.g. the greeting to the greeter.
 *
 * The tree configures the greeter of the core domain example (see GreeterContributor), and the
 * features built on it: the greeting header of "event-listener".
 *
 * @see https://symfony.com/doc/current/bundles/configuration.html
 */
final class SemanticConfigFeature extends AbstractFeature
{
    public const NAME = 'config';
    protected const QUESTION = 'Add a semantic configuration (config/definition.php)?';
    protected const ENABLED_BY_DEFAULT = true;

    /** The configuration comes first in README.md and docs/index.md, before the usage sections. */
    public const DOCS_PRIORITY = 10;

    public function contribute(BundleBlueprint $blueprint): void
    {
        $blueprint->addFile('features/config/definition.php.tpl', 'config/definition.php');
        $blueprint->addFile('features/config/tests/Integration/ConfigurationTest.php.tpl', 'tests/Integration/ConfigurationTest.php');

        $blueprint->slots->addPartial(Slot::BundleConfigure, 'features/config/partials/bundle_configure.php.tpl');
        $blueprint->slots->addPartial(Slot::BundleLoadExtension, 'features/config/partials/bundle_load_extension.php.tpl');
        $blueprint->slots->addPartial(Slot::ReadmeSections, 'features/config/partials/readme.md.tpl', priority: self::DOCS_PRIORITY);
        $blueprint->slots->addPartial(Slot::DocsSections, 'features/config/partials/docs.md.tpl', priority: self::DOCS_PRIORITY);
    }
}
