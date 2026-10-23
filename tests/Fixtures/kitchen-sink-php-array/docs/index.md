# AcmePhpArrayKitchenSinkBundle

AcmePhpArrayKitchenSinkBundle for Symfony

## Installation

Follow the [installation instructions](../README.md#installation) of the README.

## Configuration

The bundle works without configuration. These are the options of the
`acme_php_array_kitchen_sink` extension, with their default values:

```yaml
# config/packages/acme_php_array_kitchen_sink.yaml
acme_php_array_kitchen_sink:
    # The word people are greeted with, e.g. "Hello" in "Hello Jane!".
    greeting: 'Hello' # Example: 'Hi'
```

The same configuration in PHP:

```php
// config/packages/acme_php_array_kitchen_sink.php
namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return App::config([
    'acme_php_array_kitchen_sink' => [
        'greeting' => 'Hello',
    ],
]);
```

Run `php bin/console config:dump-reference acme_php_array_kitchen_sink` to display this
reference, and `php bin/console debug:config acme_php_array_kitchen_sink` to display the
configuration of your application.

## Usage

The bundle provides a greeter service. Type-hint `Acme\PhpArrayKitchenSinkBundle\GreeterInterface`
in the constructor of your services to get it injected by autowiring:

```php
namespace App\Service;

use Acme\PhpArrayKitchenSinkBundle\GreeterInterface;

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

Every exception thrown by the bundle implements `Acme\PhpArrayKitchenSinkBundle\Exception\ExceptionInterface`.

## Kitchen Sink

This section is contributed by the kitchen sink.
