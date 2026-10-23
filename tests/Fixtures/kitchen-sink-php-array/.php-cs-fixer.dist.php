<?php

/*
 * PHP-CS-Fixer configuration: the coding standards of Symfony.
 *
 * Run "composer cs" to check the code and "composer cs:fix" to fix it. To change it
 * locally, create a .php-cs-fixer.php file (ignored by Git).
 *
 * See https://symfony.com/doc/current/contributing/code/standards.html
 */

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@Symfony' => true,
        '@Symfony:risky' => true,
    ])
    ->setFinder(
        // every PHP file but vendor/ and dot files
        (new PhpCsFixer\Finder())
            ->in(__DIR__)
            ->append([__FILE__]),
    )
    ->setParallelConfig(PhpCsFixer\Runner\Parallel\ParallelConfigFactory::detect())
;
