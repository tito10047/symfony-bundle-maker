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

namespace Tito10047\BundleMaker\Maker;

class BundleOptions
{
    /**
     * Composer package name.
     */
    public string $name = 'vendor/name-bundle';

    /**
     * Composer package description.
     */
    public string $description = 'Acme bundle description';

    /**
     * Will the bundle contain a config definition?
     */
    public bool $hasConfig = false;

    /**
     * Will the bundle contain Web assets?
     */
    public bool $hasWebAssets = false;

    /**
     * Will the bundle contain Twig templates?
     */
    public bool $hasTwigTemplates = false;

    /**
     * Will the bundle contain translations?
     */
    public bool $hasTranslations = false;

    /**
     * Will the bundle contain controllers?
     */
    public bool $hasControllers = false;
}
