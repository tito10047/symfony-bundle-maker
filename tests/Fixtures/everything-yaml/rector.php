<?php

use Rector\Config\RectorConfig;
use Rector\PHPUnit\CodeQuality\Rector\StmtsAwareInterface\DeclareStrictTypesTestsRector;
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
        __DIR__.'/config',
        __DIR__.'/src',
        __DIR__.'/tests',
    ])
    ->withRootFiles()
    ->withPhpSets(php82: true)
    ->withComposerBased(twig: true, doctrine: true, phpunit: true, symfony: true)
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        typeDeclarations: true,
        privatization: true,
        instanceOf: true,
        earlyReturn: true,
        phpunitCodeQuality: true,
    )
    ->withSkip([
        // like Symfony, the code does not declare strict types
        SafeDeclareStrictTypesRector::class,
        DeclareStrictTypesTestsRector::class,
    ])
;
