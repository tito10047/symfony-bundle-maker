<?php

namespace Acme\EverythingPhpArrayBundle\Tests\Integration;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\AssetMapper\AssetMapperInterface;
use Symfony\Component\AssetMapper\MappedAsset;

/**
 * The bundle is a Symfony UX package: assets/package.json declares its Stimulus controllers,
 * and AssetMapper serves them.
 */
final class AssetMapperTest extends KernelTestCase
{
    private const PACKAGE_NAME = '@acme/everything-php-array-bundle';

    private const ASSETS_DIR = __DIR__.'/../../assets';

    public function testControllersAreExposedToAssetMapper(): void
    {
        $assetMapper = self::getContainer()->get('asset_mapper');
        $this->assertInstanceOf(AssetMapperInterface::class, $assetMapper);

        $asset = $assetMapper->getAsset(self::PACKAGE_NAME.'/hello_controller.js');

        $this->assertInstanceOf(MappedAsset::class, $asset);
        $this->assertSame(realpath(self::ASSETS_DIR.'/src/hello_controller.js'), $asset->sourcePath);
    }

    public function testPackageJsonDeclaresTheControllers(): void
    {
        $package = json_decode((string) file_get_contents(self::ASSETS_DIR.'/package.json'), true, flags: \JSON_THROW_ON_ERROR);

        // StimulusBundle finds the Composer package from this name
        $this->assertSame(self::PACKAGE_NAME, $package['name']);
        $this->assertSame($package['peerDependencies'], $package['symfony']['importmap']);
        foreach ($package['symfony']['controllers'] as $controller) {
            $this->assertFileExists(self::ASSETS_DIR.'/'.$controller['main']);
        }
    }
}
