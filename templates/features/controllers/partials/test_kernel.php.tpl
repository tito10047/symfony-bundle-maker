// the default logger prints the errors, e.g. the "404 Not Found" responses of the functional tests,
// which PHPUnit reports as risky: the test application logs nothing
$container->services()
    ->set('logger', NullLogger::class)
;
