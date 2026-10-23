# AcmeEverythingPhpBundle

AcmeEverythingPhpBundle for Symfony

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
composer require acme/everything-php-bundle
```

### Applications that don't use Symfony Flex

#### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```console
composer require acme/everything-php-bundle
```

#### Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the `config/bundles.php` file of your project:

```php
// config/bundles.php

return [
    // ...
    Acme\EverythingPhpBundle\AcmeEverythingPhpBundle::class => ['all' => true],
];
```

### Import the Routes

Import the routes of the bundle in your application, under the prefix of
your choice:

```yaml
# config/routes/acme_everything_php.yaml
acme_everything_php:
    resource: '@AcmeEverythingPhpBundle/config/routes.php'
    prefix: /acme-everything-php
```

### Create the Database Table

The bundle stores its entities in the `acme_everything_php_greeting` table. Create it with a
[migration](https://symfony.com/bundles/DoctrineMigrationsBundle/current/index.html):

```console
php bin/console doctrine:migrations:diff
php bin/console doctrine:migrations:migrate
```

### Install the Assets

Install the web assets of the bundle in the `public/bundles/acmeeverythingphp/`
directory of your application (Symfony Flex runs this command after each
`composer require` and `composer update`):

```console
php bin/console assets:install
```

### Enable the Stimulus Controller

The bundle provides a [Stimulus](https://stimulus.hotwired.dev/) controller, so
your application needs [StimulusBundle](https://symfony.com/bundles/StimulusBundle/current/index.html).
Symfony Flex enables the controller for you. Without Flex, enable it in the
`assets/controllers.json` file of your application:

```json
{
    "controllers": {
        "@acme/everything-php-bundle": {
            "hello": {
                "enabled": true,
                "fetch": "lazy"
            }
        }
    },
    "entrypoints": []
}
```

With AssetMapper, also add Stimulus to the importmap of your application, if it
is not there yet:

```console
php bin/console importmap:require @hotwired/stimulus
```

With Webpack Encore, add the bundle to the dependencies of your `package.json`
(`"@acme/everything-php-bundle": "file:vendor/acme/everything-php-bundle/assets"`), then
run `npm install --force` and rebuild your assets.

## Configuration

The bundle works without configuration. To change its defaults, configure the
`acme_everything_php` extension in your application:

```yaml
# config/packages/acme_everything_php.yaml
acme_everything_php:
    greeting: 'Hi'
```

Or in PHP:

```php
// config/packages/acme_everything_php.php
namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return App::config([
    'acme_everything_php' => [
        'greeting' => 'Hi',
    ],
]);
```

Run `php bin/console config:dump-reference acme_everything_php` to list every option
and its default value, or read the [configuration reference](docs/index.md#configuration).

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

The changes of each version are listed in the [changelog](CHANGELOG.md).

## License

AcmeEverythingPhpBundle is released under the MIT License, see the [LICENSE](LICENSE) file.
