$container->extension('twig', [
    'strict_variables' => true,
]);
$container->parameters()
    ->set('kitchen_sink.event', KernelEvents::REQUEST)
;
