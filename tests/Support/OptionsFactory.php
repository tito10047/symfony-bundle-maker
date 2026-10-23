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

use Tito10047\BundleMaker\Feature\FeatureSelection;
use Tito10047\BundleMaker\Naming\BundleNaming;
use Tito10047\BundleMaker\Options\Author;
use Tito10047\BundleMaker\Options\BundleOptions;
use Tito10047\BundleMaker\Options\ConfigFormat;
use Tito10047\BundleMaker\Options\License;

final class OptionsFactory
{
    /**
     * @param array<string, bool> $features
     */
    public static function create(
        string $packageName = 'acme/blog-bundle',
        ConfigFormat $configFormat = ConfigFormat::Php,
        array $features = [],
        License $license = License::Mit,
        ?string $email = 'jane@example.com',
    ): BundleOptions {
        $naming = new BundleNaming($packageName);

        return new BundleOptions($naming, $naming->className.' for Symfony', new Author('Jane Doe', $email), $license, $configFormat, new FeatureSelection($features));
    }
}
