<?php
use Tito10047\BundleMaker\Feature\UxFeature;

/**
 * @var Tito10047\BundleMaker\Naming\BundleNaming     $naming
 * @var Tito10047\BundleMaker\Template\TemplateHelper $helper
 */
?>
// exposes the Stimulus controllers to AssetMapper, when the application uses it,
// see https://symfony.com/doc/current/frontend/create_ux_bundle.html#specifics-for-asset-mapper
if (interface_exists(AssetMapperInterface::class) && $builder->hasExtension('framework')) {
    $builder->prependExtensionConfig('framework', [
        'asset_mapper' => [
            'paths' => [
                $this->getPath().'/assets/src' => <?= $helper->php(UxFeature::packageName($naming)) ?>,
            ],
        ],
    ]);
}
