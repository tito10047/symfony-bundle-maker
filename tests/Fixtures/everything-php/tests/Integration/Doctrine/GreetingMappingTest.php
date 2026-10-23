<?php

namespace Zeta\EverythingPhpBundle\Tests\Integration\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zeta\EverythingPhpBundle\Entity\Greeting;

final class GreetingMappingTest extends KernelTestCase
{
    /**
     * The table and column names come from config/doctrine/Greeting.orm.xml, validated against
     * the XML schema of Doctrine ORM.
     */
    public function testEntityIsMappedWithTheXmlFile(): void
    {
        $metadata = $this->getEntityManager()->getClassMetadata(Greeting::class);

        $this->assertSame('zeta_everything_php_greeting', $metadata->getTableName());
        $this->assertSame(['id'], $metadata->getIdentifierFieldNames());
        $this->assertEqualsCanonicalizing(['id', 'name', 'message', 'createdAt'], $metadata->getFieldNames());
        $this->assertSame('created_at', $metadata->getColumnName('createdAt'));
    }

    public function testGreetingIsPersisted(): void
    {
        $entityManager = $this->getEntityManager();
        (new SchemaTool($entityManager))->createSchema([$entityManager->getClassMetadata(Greeting::class)]);

        $greeting = new Greeting('Jane', 'Hello Jane!', new \DateTimeImmutable('2026-01-01 10:00:00'));
        $entityManager->persist($greeting);
        $entityManager->flush();
        $entityManager->clear();

        $found = $entityManager->find(Greeting::class, $greeting->getId());

        $this->assertInstanceOf(Greeting::class, $found);
        $this->assertNotSame($greeting, $found);
        $this->assertSame('Jane', $found->getName());
        $this->assertSame('Hello Jane!', $found->getMessage());
        $this->assertEquals(new \DateTimeImmutable('2026-01-01 10:00:00'), $found->getCreatedAt());
    }

    private function getEntityManager(): EntityManagerInterface
    {
        $entityManager = self::getContainer()->get('doctrine.orm.entity_manager');
        $this->assertInstanceOf(EntityManagerInterface::class, $entityManager);

        return $entityManager;
    }
}
