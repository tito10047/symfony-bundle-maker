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

namespace Tito10047\BundleMaker\Contributor;

use Tito10047\BundleMaker\Contributor\Core\BundleClassContributor;
use Tito10047\BundleMaker\Contributor\Core\DocumentationContributor;
use Tito10047\BundleMaker\Contributor\Core\GreeterContributor;
use Tito10047\BundleMaker\Contributor\Core\LicenseContributor;
use Tito10047\BundleMaker\Contributor\Core\PackageContributor;
use Tito10047\BundleMaker\Contributor\Core\RepositoryContributor;
use Tito10047\BundleMaker\Contributor\Core\TestsContributor;

/**
 * Contributors of the files every bundle has, whatever the selected features.
 * They run before the features, in this order.
 *
 * @implements \IteratorAggregate<int, ContributorInterface>
 */
final class CoreContributors implements \IteratorAggregate
{
    /** @var list<ContributorInterface> */
    private readonly array $contributors;

    public function __construct(ContributorInterface ...$contributors)
    {
        $this->contributors = array_values($contributors);
    }

    /**
     * @param int|null $year year of the copyright notice of the LICENSE file, the current year by default
     */
    public static function createDefault(?int $year = null): self
    {
        return new self(
            new PackageContributor(),
            new BundleClassContributor(),
            new GreeterContributor(),
            new TestsContributor(),
            new DocumentationContributor(),
            new LicenseContributor($year ?? (int) date('Y')),
            new RepositoryContributor(),
        );
    }

    /**
     * @return \ArrayIterator<int, ContributorInterface>
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->contributors);
    }
}
