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

use Tito10047\BundleMaker\Contributor\ContributorInterface;

/**
 * An optional part of the generated bundle, enabled by answering "yes" to its question.
 *
 * contribute() is only called when the feature is enabled.
 */
interface FeatureInterface extends ContributorInterface
{
    /**
     * Unique kebab-case name, e.g. "event-listener". It also defines the environment variable
     * presetting the answer: "BUNDLE_MAKER_EVENT_LISTENER".
     */
    public function getName(): string;

    public function getGroup(): FeatureGroup;

    /**
     * The question, e.g. "Add a console command?". The default answer hint is appended by the assistant.
     */
    public function getQuestion(): string;

    /**
     * Default answer when no preset applies. It may depend on the answers of the features
     * registered before this one, e.g. Twig defaults to "yes" when controllers are enabled.
     */
    public function isEnabledByDefault(FeatureSelection $previousAnswers): bool;

    /**
     * Names of the features this one builds on, registered before it. The feature is only
     * asked, and can only be enabled, when all of them are enabled.
     *
     * @return list<string>
     */
    public function getRequiredFeatures(): array;
}
