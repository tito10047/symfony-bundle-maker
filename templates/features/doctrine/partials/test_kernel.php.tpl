$container->extension('doctrine', [
    'dbal' => [
        'url' => 'sqlite:///:memory:',
    ],
    'orm' => [],
]);

// DoctrineBundle 2, the only version supporting PHP 8.2 and 8.3, deprecates the defaults that DoctrineBundle 3 no longer has
if (version_compare(InstalledVersions::getVersion('doctrine/doctrine-bundle') ?? '', '3.0.0', '<')) {
    $container->extension('doctrine', [
        'orm' => [
            'enable_lazy_ghost_objects' => true,
            'enable_native_lazy_objects' => \PHP_VERSION_ID >= 80400,
            'controller_resolver' => [
                'auto_mapping' => false,
            ],
        ],
    ]);
}
