// unused private services are removed from the container: this public alias keeps the
// greeter available to the tests, the way an application autowiring it does
$container->services()
    ->alias('test.'.GreeterInterface::class, GreeterInterface::class)
    ->public()
;
