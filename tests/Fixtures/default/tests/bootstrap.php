<?php

use Acme\BlogBundle\Tests\App\TestKernel;
use Symfony\Component\Filesystem\Filesystem;

require dirname(__DIR__).'/vendor/autoload.php';

// a kernel reuses the container compiled by a previous run: the code of the bundle building it
// (the extension, the compiler passes) would not run, and would be missing from the code coverage
(new Filesystem())->remove(TestKernel::getTemporaryDir());
