<?php
use Tito10047\BundleMaker\Feature\DoctrineFeature;

/**
 * @var Tito10047\BundleMaker\Naming\BundleNaming     $naming
 * @var Tito10047\BundleMaker\Template\TemplateHelper $helper
 */
?>
<?= '<?xml version="1.0" encoding="UTF-8"?>' ?>

<!--
    Doctrine ORM mapping of <?= $helper->xml($naming->namespace) ?>\Entity\Greeting.
    See https://www.doctrine-project.org/projects/doctrine-orm/en/current/reference/xml-mapping.html
-->
<doctrine-mapping xmlns="http://doctrine-project.org/schemas/orm/doctrine-mapping"
                  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                  xsi:schemaLocation="http://doctrine-project.org/schemas/orm/doctrine-mapping https://www.doctrine-project.org/schemas/orm/doctrine-mapping.xsd">
    <entity name="<?= $helper->xml($naming->namespace.'\Entity\Greeting') ?>" table="<?= $helper->xml(DoctrineFeature::tableName($naming)) ?>">
        <id name="id" type="integer">
            <generator strategy="AUTO"/>
        </id>

        <field name="name" type="string" length="255"/>
        <field name="message" type="string" length="255"/>
        <field name="createdAt" column="created_at" type="datetime_immutable"/>
    </entity>
</doctrine-mapping>
