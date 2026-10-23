<?php
/**
 * @var Tito10047\BundleMaker\Naming\BundleNaming   $naming
 * @var Tito10047\BundleMaker\Options\BundleOptions $options
 */
$resource = '@'.$naming->className.'/config/'.$options->configFormat->getFileName('routes');
?>
Import the Routes

Import the routes of the bundle in your application, under the prefix of
your choice:

```yaml
# config/routes/<?= $naming->alias ?>.yaml
<?= $naming->alias ?>:
    resource: '<?= $resource ?>'
    prefix: /<?= $naming->slug ?>

```
