# AcmePhpArrayKitchenSinkBundle

AcmePhpArrayKitchenSinkBundle for Symfony

[![Kitchen sink](https://img.shields.io/badge/kitchen-sink-blue)](https://example.com)
[![PHP](https://img.shields.io/badge/php-8.2-blue)](https://php.net)

## Requirements

- PHP 8.2 or higher (8.4 or higher with Symfony 8)
- Symfony 7.4 LTS or 8.1 and higher

## Installation

Make sure Composer is installed globally, as explained in the
[installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

### Applications that use Symfony Flex

Open a command console, enter your project directory and execute:

```console
composer require acme/php-array-kitchen-sink-bundle
```

### Applications that don't use Symfony Flex

#### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```console
composer require acme/php-array-kitchen-sink-bundle
```

#### Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the `config/bundles.php` file of your project:

```php
// config/bundles.php

return [
    // ...
    Acme\PhpArrayKitchenSinkBundle\AcmePhpArrayKitchenSinkBundle::class => ['all' => true],
];
```

### Import the Routes

Follow the "Import the Routes" instructions of AcmePhpArrayKitchenSinkBundle.

### Install the Assets

Follow the "Install the Assets" instructions of AcmePhpArrayKitchenSinkBundle.

## Configuration

The bundle works without configuration. To change its defaults, configure the
`acme_php_array_kitchen_sink` extension in your application:

```yaml
# config/packages/acme_php_array_kitchen_sink.yaml
acme_php_array_kitchen_sink:
    greeting: 'Hi'
```

Or in PHP:

```php
// config/packages/acme_php_array_kitchen_sink.php
namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return App::config([
    'acme_php_array_kitchen_sink' => [
        'greeting' => 'Hi',
    ],
]);
```

Run `php bin/console config:dump-reference acme_php_array_kitchen_sink` to list every option
and its default value, or read the [configuration reference](docs/index.md#configuration).

## Kitchen Sink

This section is contributed by the kitchen sink.

## Documentation

Read the [documentation](docs/index.md) to learn how to use the bundle.

## Development

Install the dependencies and run the tests:

```console
composer install
composer test
```

| Command | Description |
|---|---|
| `composer qa` | Runs every quality check |
| `composer test` | Runs the tests (PHPUnit) |
| `composer lint` |  |
| `composer kitchen-sink` | Checks the kitchen sink |
| `composer phpstan` | Runs the static analysis (PHPStan) |
| `composer cs` | Checks the coding standards (PHP-CS-Fixer) |
| `composer cs:fix` | Fixes the coding standards (PHP-CS-Fixer) |
| `composer rector` | Lists the automated refactorings (Rector) |
| `composer rector:fix` | Applies the automated refactorings (Rector) |

The changes of each version are listed in the [changelog](CHANGELOG.md).

## License

AcmePhpArrayKitchenSinkBundle is released under the MIT License, see the [LICENSE](LICENSE) file.
