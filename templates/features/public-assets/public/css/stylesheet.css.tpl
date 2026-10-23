<?php
use Tito10047\BundleMaker\Feature\PublicAssetsFeature;

/**
 * @var Tito10047\BundleMaker\Naming\BundleNaming $naming
 */
$css = $naming->slug.'-hello';
?>
/*
 * Styles of <?= $naming->className ?>.
 *
 * Installed in public/<?= PublicAssetsFeature::stylesheetPublicPath($naming) ?> by "php bin/console assets:install".
 */

.<?= $css ?> {
    margin: 1rem 0;
    padding: 1rem 1.5rem;
    border-radius: .5rem;
    background: #f5f7fa;
    color: #1f2933;
}

.<?= $css ?>__title {
    margin: 0 0 .5rem;
    font-size: 1.5rem;
}

.<?= $css ?>__greeting {
    margin: 0;
}

.<?= $css ?>__close {
    margin-top: 1rem;
    cursor: pointer;
}
