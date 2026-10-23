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
 * A console command greeting someone with the greeter (src/Command/HelloCommand.php).
 *
 * The command is invokable (#[AsCommand] and __invoke() with #[Argument] and #[Option], Symfony 7.3+):
 * it does not extend Command. Its service has a bare "console.command" tag: the console reads the
 * name and the description of #[AsCommand], and loads the command lazily.
 *
 * @see https://symfony.com/doc/current/console.html
 */
final class CommandFeature extends AbstractFeature
{
    public const NAME = 'command';
    protected const QUESTION = 'Add a console command?';

    public function contribute(BundleBlueprint $blueprint): void
    {
        $naming = $blueprint->naming;

        $blueprint->composer->require('symfony/console', Constraint::SYMFONY);

        $blueprint->addFile('features/command/src/Command/HelloCommand.php.tpl', 'src/Command/HelloCommand.php');
        $blueprint->addFile('features/command/tests/Integration/Command/HelloCommandTest.php.tpl', 'tests/Integration/Command/HelloCommandTest.php');

        $blueprint->services->add(new ServiceDefinition(
            $naming->alias.'.command.hello',
            $naming->namespace.'\Command\HelloCommand',
            [ServiceArgument::service(GreeterContributor::serviceId($naming))],
            [new ServiceTag('console.command')],
        ));

        $blueprint->slots->addPartial(Slot::DocsSections, 'features/command/partials/docs.md.tpl');
    }

    /**
     * Name of the command, e.g. "acme-blog:hello".
     *
     * The commands of a bundle share one namespace, its slug (like "doctrine:" or "make:"):
     * "php bin/console list acme-blog" lists them, and they cannot collide with the commands
     * of other bundles or of the application.
     */
    public static function commandName(BundleNaming $naming): string
    {
        return $naming->slug.':hello';
    }
}
