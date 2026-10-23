<?php
/**
 * @var Tito10047\BundleMaker\Naming\BundleNaming $naming
 */
?>
Install the Assets

Install the web assets of the bundle in the `public/<?= $naming->publicAssetsDirectory ?>/`
directory of your application (Symfony Flex runs this command after each
`composer require` and `composer update`):

```console
php bin/console assets:install
```
