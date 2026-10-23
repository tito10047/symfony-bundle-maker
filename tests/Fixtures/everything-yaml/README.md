# Tito10047EverythingYamlBundle

Tito10047EverythingYamlBundle for Symfony

[![CI](https://github.com/tito10047/everything-yaml-bundle/actions/workflows/ci.yml/badge.svg)](https://github.com/tito10047/everything-yaml-bundle/actions/workflows/ci.yml)

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
composer require tito10047/everything-yaml-bundle
```

### Applications that don't use Symfony Flex

#### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```console
composer require tito10047/everything-yaml-bundle
```

#### Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the `config/bundles.php` file of your project:

```php
// config/bundles.php

return [
    // ...
    Tito10047\EverythingYamlBundle\Tito10047EverythingYamlBundle::class => ['all' => true],
];
```

### Import the Routes

Import the routes of the bundle in your application, under the prefix of
your choice:

```yaml
# config/routes/tito10047_everything_yaml.yaml
tito10047_everything_yaml:
    resource: '@Tito10047EverythingYamlBundle/config/routes.yaml'
    prefix: /tito10047-everything-yaml
```

### Create the Database Table

The bundle stores its entities in the `tito10047_everything_yaml_greeting` table. Create it with a
[migration](https://symfony.com/bundles/DoctrineMigrationsBundle/current/index.html):

```console
php bin/console doctrine:migrations:diff
php bin/console doctrine:migrations:migrate
```

### Install the Assets

Install the web assets of the bundle in the `public/bundles/tito10047everythingyaml/`
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
        "@tito10047/everything-yaml-bundle": {
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
(`"@tito10047/everything-yaml-bundle": "file:vendor/tito10047/everything-yaml-bundle/assets"`), then
run `npm install --force` and rebuild your assets.

## Configuration

The bundle works without configuration. To change its defaults, configure the
`tito10047_everything_yaml` extension in your application:

```yaml
# config/packages/tito10047_everything_yaml.yaml
tito10047_everything_yaml:
    greeting: 'Hi'
```

Or in PHP:

```php
// config/packages/tito10047_everything_yaml.php
namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return App::config([
    'tito10047_everything_yaml' => [
        'greeting' => 'Hi',
    ],
]);
```

Run `php bin/console config:dump-reference tito10047_everything_yaml` to list every option
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
| `composer phpstan` | Runs the static analysis (PHPStan) |
| `composer cs` | Checks the coding standards (PHP-CS-Fixer) |
| `composer cs:fix` | Fixes the coding standards (PHP-CS-Fixer) |
| `composer rector` | Lists the automated refactorings (Rector) |
| `composer rector:fix` | Applies the automated refactorings (Rector) |

The changes of each version are listed in the [changelog](CHANGELOG.md).

## License

Tito10047EverythingYamlBundle is released under the MIT License, see the [LICENSE](LICENSE) file.
