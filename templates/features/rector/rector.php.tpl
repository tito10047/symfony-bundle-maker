<?php
/**
 * @var Tito10047\BundleMaker\Blueprint\BundleBlueprint $blueprint
 * @var Tito10047\BundleMaker\Template\TemplateHelper   $helper
 */
$composer = $blueprint->composer;
$packages = array_keys($composer->getRequire() + $composer->getRequireDev());
$requiresVendor = static fn (string $vendor): bool => [] !== array_filter($packages, static fn (string $package): bool => str_starts_with($package, $vendor.'/'));
$hasPhpUnit = null !== $composer->getConstraint('phpunit/phpunit');
$paths = ['config', 'src', ...($blueprint->hasDirectory('tests') ? ['tests'] : [])];
// in the order of the withComposerBased() arguments
$composerBased = array_keys(array_filter([
    'twig' => $requiresVendor('twig'),
    'doctrine' => $requiresVendor('doctrine'),
    'phpunit' => $hasPhpUnit,
    'symfony' => true,
]));
$preparedSets = ['deadCode', 'codeQuality', 'typeDeclarations', 'privatization', 'instanceOf', 'earlyReturn', ...($hasPhpUnit ? ['phpunitCodeQuality'] : [])];
?>
<?= "<?php\n" ?>

use Rector\Config\RectorConfig;
<?php if ($hasPhpUnit): ?>
use Rector\PHPUnit\CodeQuality\Rector\StmtsAwareInterface\DeclareStrictTypesTestsRector;
<?php endif ?>
use Rector\TypeDeclaration\Rector\StmtsAwareInterface\SafeDeclareStrictTypesRector;

/*
 * Rector configuration: run "composer rector" to list the suggested changes and
 * "composer rector:fix" to apply them.
 *
 * The changes target the lowest supported versions, so they work with every supported
 * version: PHP 8.2, and the lowest versions allowed by composer.json for the Composer-based
 * sets (Rector applies them this way to libraries, i.e. any package type but "project").
 *
 * See https://getrector.com/documentation
 */

return RectorConfig::configure()
    ->withPaths([
<?php foreach ($paths as $path): ?>
        __DIR__.<?= $helper->php('/'.$path) ?>,
<?php endforeach ?>
    ])
    ->withRootFiles()
    ->withPhpSets(php82: true)
    ->withComposerBased(<?= implode(', ', array_map(static fn (string $set): string => $set.': true', $composerBased)) ?>)
    ->withPreparedSets(
<?php foreach ($preparedSets as $set): ?>
        <?= $set ?>: true,
<?php endforeach ?>
    )
    ->withSkip([
        // like Symfony, the code does not declare strict types
        SafeDeclareStrictTypesRector::class,
<?php if ($hasPhpUnit): ?>
        DeclareStrictTypesTestsRector::class,
<?php endif ?>
    ])
;
