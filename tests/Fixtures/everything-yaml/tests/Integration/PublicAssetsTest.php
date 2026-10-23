<?php

namespace MyVendor\EverythingYamlBundle\Tests\Integration;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;

final class PublicAssetsTest extends KernelTestCase
{
    public function testAssetsAreInstalledInThePublicDirectoryOfTheApplication(): void
    {
        $filesystem = new Filesystem();
        $publicDir = sys_get_temp_dir().'/'.uniqid('my-vendor-everything-yaml-public-');
        $filesystem->mkdir($publicDir);
        $tester = new CommandTester((new Application(self::bootKernel()))->find('assets:install'));

        try {
            $tester->execute(['target' => $publicDir]);

            $tester->assertCommandIsSuccessful();
            $this->assertFileEquals(
                \dirname(__DIR__, 2).'/public/css/my-vendor-everything-yaml.css',
                $publicDir.'/bundles/myvendoreverythingyaml/css/my-vendor-everything-yaml.css',
            );
        } finally {
            $filesystem->remove($publicDir);
        }
    }
}
