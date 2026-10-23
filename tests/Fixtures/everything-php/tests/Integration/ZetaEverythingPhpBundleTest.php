<?php

namespace Zeta\EverythingPhpBundle\Tests\Integration;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zeta\EverythingPhpBundle\GreeterInterface;

/**
 * Boots the test application, which registers the bundle.
 */
final class ZetaEverythingPhpBundleTest extends KernelTestCase
{
    public function testGreeterCanBeAutowired(): void
    {
        $greeter = self::getContainer()->get('test.'.GreeterInterface::class);

        $this->assertInstanceOf(GreeterInterface::class, $greeter);
        $this->assertSame('Hello Jane!', $greeter->greet('Jane'));
    }
}
