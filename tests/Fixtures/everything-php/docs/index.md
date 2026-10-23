# AcmeEverythingPhpBundle

AcmeEverythingPhpBundle for Symfony

## Installation

Follow the [installation instructions](../README.md#installation) of the README.

## Configuration

The bundle works without configuration. These are the options of the
`acme_everything_php` extension, with their default values:

```yaml
# config/packages/acme_everything_php.yaml
acme_everything_php:
    # The word people are greeted with, e.g. "Hello" in "Hello Jane!".
    greeting: 'Hello' # Example: 'Hi'
```

The same configuration in PHP:

```php
// config/packages/acme_everything_php.php
namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return App::config([
    'acme_everything_php' => [
        'greeting' => 'Hello',
    ],
]);
```

Run `php bin/console config:dump-reference acme_everything_php` to display this
reference, and `php bin/console debug:config acme_everything_php` to display the
configuration of your application.

## Usage

The bundle provides a greeter service. Type-hint `Acme\EverythingPhpBundle\GreeterInterface`
in the constructor of your services to get it injected by autowiring:

```php
namespace App\Service;

use Acme\EverythingPhpBundle\GreeterInterface;

final class Welcome
{
    public function __construct(
        private readonly GreeterInterface $greeter,
    ) {
    }

    public function welcome(string $name): string
    {
        return $this->greeter->greet($name); // "Hello Jane!" for "Jane"
    }
}
```

Every exception thrown by the bundle implements `Acme\EverythingPhpBundle\Exception\ExceptionInterface`.

## Routes

The bundle provides a page greeting people, once its routes are imported
(see the [installation instructions](../README.md#installation)):

| Route | Path | Controller |
|---|---|---|
| `acme_everything_php_hello` | `/hello/{name}` | `Acme\EverythingPhpBundle\Controller\HelloController` |

With the `/acme-everything-php` prefix, `/acme-everything-php/hello/Jane` greets "Hello Jane!", and
`/acme-everything-php/hello` greets "Hello World!".
The page is plain text.

Link to the page by the name of its route, never by its path, which depends
on the prefix chosen by the application:

```twig
<a href="{{ path('acme_everything_php_hello', {name: 'Jane'}) }}">Greet Jane</a>
```

Run `php bin/console debug:router acme_everything_php_hello` to display the route.
