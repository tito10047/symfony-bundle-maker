<?php
use Tito10047\BundleMaker\Blueprint\Slot;

/**
 * @var Tito10047\BundleMaker\Blueprint\BundleBlueprint $blueprint
 * @var Tito10047\BundleMaker\Template\SlotRenderer     $slots
 */
$paths = ['config', 'src', ...($blueprint->hasDirectory('tests') ? ['tests'] : [])];
?>
# PHPStan configuration, run it with "composer phpstan".
# Extensions (Symfony, PHPUnit) are registered by phpstan/extension-installer.
# To change it locally, create a phpstan.neon file (ignored by Git) that includes this one.
#
# See https://phpstan.org/config-reference

parameters:
    level: 8
    paths:
<?php foreach ($paths as $path): ?>
        - <?= $path ?>

<?php endforeach ?>
<?php if ($slots->has(Slot::PhpStanParameters)): ?>

<?= $slots->render(Slot::PhpStanParameters, 4) ?>
<?php endif ?>
