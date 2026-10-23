<?php
use Tito10047\BundleMaker\Feature\DoctrineFeature;

/**
 * @var Tito10047\BundleMaker\Naming\BundleNaming     $naming
 * @var Tito10047\BundleMaker\Template\TemplateHelper $helper
 */
$imports = $helper->imports([
    $naming->namespace.'\Entity\Greeting',
    'Doctrine\ORM\EntityManagerInterface',
    'Doctrine\ORM\Tools\SchemaTool',
    'Symfony\Bundle\FrameworkBundle\Test\KernelTestCase',
], $naming->testsNamespace.'\Integration\Doctrine');
?>
<?= "<?php\n" ?>

namespace <?= $naming->testsNamespace ?>\Integration\Doctrine;

<?= $imports->render() ?>

final class GreetingMappingTest extends KernelTestCase
{
    /**
     * The table and column names come from config/doctrine/Greeting.orm.xml, validated against
     * the XML schema of Doctrine ORM.
     */
    public function testEntityIsMappedWithTheXmlFile(): void
    {
        $metadata = $this->getEntityManager()->getClassMetadata(Greeting::class);

        $this->assertSame(<?= $helper->php(DoctrineFeature::tableName($naming)) ?>, $metadata->getTableName());
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
