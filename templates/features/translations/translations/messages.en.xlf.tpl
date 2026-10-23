<?php
/**
 * @var Tito10047\BundleMaker\Template\TemplateHelper $helper
 * @var list<string>                               $messages
 */
?>
<?= '<?xml version="1.0" encoding="UTF-8"?>' ?>

<xliff xmlns="urn:oasis:names:tc:xliff:document:1.2" version="1.2">
    <file source-language="en" target-language="en" datatype="plaintext" original="file.ext">
        <body>
<?php foreach ($messages as $i => $message): ?>
            <trans-unit id="<?= $i + 1 ?>">
                <source><?= $helper->xml($message) ?></source>
                <target><?= $helper->xml($message) ?></target>
            </trans-unit>
<?php endforeach ?>
        </body>
    </file>
</xliff>
