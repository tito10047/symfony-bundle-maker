<?php

namespace MyVendor\EverythingYamlBundle\Tests\Integration;

use MyVendor\EverythingYamlBundle\GreeterInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Boots the test application, which registers the bundle.
 */
final class MyVendorEverythingYamlBundleTest extends KernelTestCase
{
    public function testGreeterCanBeAutowired(): void
    {
        $greeter = self::getContainer()->get('test.'.GreeterInterface::class);

        $this->assertInstanceOf(GreeterInterface::class, $greeter);
        $this->assertSame('Hello Jane!', $greeter->greet('Jane'));
    }
}
