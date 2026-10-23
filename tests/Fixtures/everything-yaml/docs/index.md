# MyVendorEverythingYamlBundle

MyVendorEverythingYamlBundle for Symfony

## Installation

Follow the [installation instructions](../README.md#installation) of the README.

## Configuration

The bundle works without configuration. These are the options of the
`my_vendor_everything_yaml` extension, with their default values:

```yaml
# config/packages/my_vendor_everything_yaml.yaml
my_vendor_everything_yaml:
    # The word people are greeted with, e.g. "Hello" in "Hello Jane!".
    greeting: 'Hello' # Example: 'Hi'
```

The same configuration in PHP:

```php
// config/packages/my_vendor_everything_yaml.php
namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return App::config([
    'my_vendor_everything_yaml' => [
        'greeting' => 'Hello',
    ],
]);
```

Run `php bin/console config:dump-reference my_vendor_everything_yaml` to display this
reference, and `php bin/console debug:config my_vendor_everything_yaml` to display the
configuration of your application.

## Usage

The bundle provides a greeter service. Type-hint `MyVendor\EverythingYamlBundle\GreeterInterface`
in the constructor of your services to get it injected by autowiring:

```php
namespace App\Service;

use MyVendor\EverythingYamlBundle\GreeterInterface;

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

Every exception thrown by the bundle implements `MyVendor\EverythingYamlBundle\Exception\ExceptionInterface`.
