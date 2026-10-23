<?php

/*
 * Gives the entity manager of the test application to PHPStan: phpstan/phpstan-doctrine reads
 * the mapping of the entities from it (doctrine.objectManagerLoader in phpstan.dist.neon).
 */

use Doctrine\Persistence\ManagerRegistry;
use Tito10047\EverythingYamlBundle\Tests\App\TestKernel;

// in debug mode, the container and the mapping are refreshed when the mapping files change
$kernel = new TestKernel('test', true);
$kernel->boot();

$doctrine = $kernel->getContainer()->get('doctrine');
if (!$doctrine instanceof ManagerRegistry) {
    throw new LogicException('The "doctrine" service must be a ManagerRegistry.');
}

return $doctrine->getManager();
