<?php

namespace Acme\MinimalYamlBundle\Tests\Integration;

use Acme\MinimalYamlBundle\GreeterInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Boots the test application, which registers the bundle.
 */
final class AcmeMinimalYamlBundleTest extends KernelTestCase
{
    public function testGreeterCanBeAutowired(): void
    {
        $greeter = self::getContainer()->get('test.'.GreeterInterface::class);

        $this->assertInstanceOf(GreeterInterface::class, $greeter);
        $this->assertSame('Hello Jane!', $greeter->greet('Jane'));
    }
}
