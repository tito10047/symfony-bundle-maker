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

use PHPUnit\Framework\TestCase;
use Tito10047\BundleMaker\Blueprint\BundleBlueprint;
use Tito10047\BundleMaker\Blueprint\ListSlot;
use Tito10047\BundleMaker\Contributor\Core\DocumentationContributor;
use Tito10047\BundleMaker\Tests\Support\OptionsFactory;

final class DocumentationContributorTest extends TestCase
{
    public function testContributesTheDocumentation(): void
    {
        $blueprint = new BundleBlueprint(OptionsFactory::create());

        (new DocumentationContributor())->contribute($blueprint);

        self::assertSame(['README.md', 'CHANGELOG.md', 'docs/index.md'], array_keys($blueprint->getFiles()));
        // the documentation is shipped with the package: README.md links to it
        self::assertSame([], $blueprint->slots->getValues(ListSlot::GitAttributesExportIgnore));
    }
}
