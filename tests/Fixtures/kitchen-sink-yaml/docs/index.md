# MyVendorYamlKitchenSinkBundle

MyVendorYamlKitchenSinkBundle for Symfony

## Installation

Follow the [installation instructions](../README.md#installation) of the README.

## Configuration

The bundle works without configuration. These are the options of the
`my_vendor_yaml_kitchen_sink` extension, with their default values:

```yaml
# config/packages/my_vendor_yaml_kitchen_sink.yaml
my_vendor_yaml_kitchen_sink:
    # The word people are greeted with, e.g. "Hello" in "Hello Jane!".
    greeting: 'Hello' # Example: 'Hi'
```

The same configuration in PHP:

```php
// config/packages/my_vendor_yaml_kitchen_sink.php
namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return App::config([
    'my_vendor_yaml_kitchen_sink' => [
        'greeting' => 'Hello',
    ],
]);
```

Run `php bin/console config:dump-reference my_vendor_yaml_kitchen_sink` to display this
reference, and `php bin/console debug:config my_vendor_yaml_kitchen_sink` to display the
configuration of your application.

## Usage

The bundle provides a greeter service. Type-hint `MyVendor\YamlKitchenSinkBundle\GreeterInterface`
in the constructor of your services to get it injected by autowiring:

```php
namespace App\Service;

use MyVendor\YamlKitchenSinkBundle\GreeterInterface;

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

Every exception thrown by the bundle implements `MyVendor\YamlKitchenSinkBundle\Exception\ExceptionInterface`.

## Kitchen Sink

This section is contributed by the kitchen sink.
