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
use Tito10047\BundleMaker\Contributor\Core\RepositoryContributor;
use Tito10047\BundleMaker\Tests\Support\OptionsFactory;

final class RepositoryContributorTest extends TestCase
{
    public function testContributesTheRepositoryFiles(): void
    {
        $blueprint = new BundleBlueprint(OptionsFactory::create());

        (new RepositoryContributor())->contribute($blueprint);

        self::assertSame(['.editorconfig', '.gitattributes', '.gitignore'], array_keys($blueprint->getFiles()));
        self::assertSame(['/.editorconfig', '/.gitattributes', '/.github', '/.gitignore'], $blueprint->slots->getValues(ListSlot::GitAttributesExportIgnore));
        self::assertSame(['/composer.lock', '/vendor/'], $blueprint->slots->getValues(ListSlot::GitIgnore));
    }
}
