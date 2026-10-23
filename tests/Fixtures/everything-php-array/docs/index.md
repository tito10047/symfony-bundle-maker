# AcmeEverythingPhpArrayBundle

AcmeEverythingPhpArrayBundle for Symfony

## Installation

Follow the [installation instructions](../README.md#installation) of the README.

## Configuration

The bundle works without configuration. These are the options of the
`acme_everything_php_array` extension, with their default values:

```yaml
# config/packages/acme_everything_php_array.yaml
acme_everything_php_array:
    # The word people are greeted with, e.g. "Hello" in "Hello Jane!".
    greeting: 'Hello' # Example: 'Hi'
```

The same configuration in PHP:

```php
// config/packages/acme_everything_php_array.php
namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return App::config([
    'acme_everything_php_array' => [
        'greeting' => 'Hello',
    ],
]);
```

Run `php bin/console config:dump-reference acme_everything_php_array` to display this
reference, and `php bin/console debug:config acme_everything_php_array` to display the
configuration of your application.

## Usage

The bundle provides a greeter service. Type-hint `Acme\EverythingPhpArrayBundle\GreeterInterface`
in the constructor of your services to get it injected by autowiring:

```php
namespace App\Service;

use Acme\EverythingPhpArrayBundle\GreeterInterface;

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

Every exception thrown by the bundle implements `Acme\EverythingPhpArrayBundle\Exception\ExceptionInterface`.

## Routes

The bundle provides a page greeting people, once its routes are imported
(see the [installation instructions](../README.md#installation)):

| Route | Path | Controller |
|---|---|---|
| `acme_everything_php_array_hello` | `/hello/{name}` | `Acme\EverythingPhpArrayBundle\Controller\HelloController` |

With the `/acme-everything-php-array` prefix, `/acme-everything-php-array/hello/Jane` greets "Hello Jane!", and
`/acme-everything-php-array/hello` greets "Hello World!".
The page is plain text.

Link to the page by the name of its route, never by its path, which depends
on the prefix chosen by the application:

```twig
<a href="{{ path('acme_everything_php_array_hello', {name: 'Jane'}) }}">Greet Jane</a>
```

Run `php bin/console debug:router acme_everything_php_array_hello` to display the route.
