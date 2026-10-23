<?php
use Tito10047\BundleMaker\Feature\UxFeature;
use Tito10047\BundleMaker\Options\License;

/**
 * @var Tito10047\BundleMaker\Naming\BundleNaming   $naming
 * @var Tito10047\BundleMaker\Options\BundleOptions $options
 */
$stimulus = ['@hotwired/stimulus' => UxFeature::STIMULUS_CONSTRAINT];
$package = [
    'name' => UxFeature::packageName($naming),
    'description' => $options->description,
    // npm has no "proprietary" license identifier
    'license' => License::Proprietary === $options->license ? 'UNLICENSED' : $options->license->value,
    'type' => 'module',
    'symfony' => [
        'controllers' => [
            UxFeature::CONTROLLER => [
                'main' => 'src/'.UxFeature::CONTROLLER.'_controller.js',
                'fetch' => 'lazy',
                'enabled' => true,
            ],
        ],
        // installed in the importmap of applications using AssetMapper, must match "peerDependencies"
        'importmap' => $stimulus,
    ],
    'peerDependencies' => $stimulus,
];
?>
<?= json_encode($package, \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE | \JSON_THROW_ON_ERROR) ?>

