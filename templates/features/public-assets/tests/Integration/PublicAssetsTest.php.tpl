<?php
use Tito10047\BundleMaker\Feature\PublicAssetsFeature;

/**
 * @var Tito10047\BundleMaker\Naming\BundleNaming     $naming
 * @var Tito10047\BundleMaker\Template\TemplateHelper $helper
 */
?>
<?= "<?php\n" ?>

namespace <?= $naming->testsNamespace ?>\Integration;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;

final class PublicAssetsTest extends KernelTestCase
{
    public function testAssetsAreInstalledInThePublicDirectoryOfTheApplication(): void
    {
        $filesystem = new Filesystem();
        $publicDir = sys_get_temp_dir().'/'.uniqid(<?= $helper->php($naming->slug.'-public-') ?>);
        $filesystem->mkdir($publicDir);
        $tester = new CommandTester((new Application(self::bootKernel()))->find('assets:install'));

        try {
            $tester->execute(['target' => $publicDir]);

            $tester->assertCommandIsSuccessful();
            $this->assertFileEquals(
                \dirname(__DIR__, 2).'/public/<?= PublicAssetsFeature::stylesheetPath($naming) ?>',
                $publicDir.'/<?= PublicAssetsFeature::stylesheetPublicPath($naming) ?>',
            );
        } finally {
            $filesystem->remove($publicDir);
        }
    }
}
