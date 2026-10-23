# AcmeBlogBundle

AcmeBlogBundle for Symfony

## Installation

Follow the [installation instructions](../README.md#installation) of the README.

## Configuration

The bundle works without configuration. These are the options of the
`acme_blog` extension, with their default values:

```yaml
# config/packages/acme_blog.yaml
acme_blog:
    # The word people are greeted with, e.g. "Hello" in "Hello Jane!".
    greeting: 'Hello' # Example: 'Hi'
```

The same configuration in PHP:

```php
// config/packages/acme_blog.php
namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return App::config([
    'acme_blog' => [
        'greeting' => 'Hello',
    ],
]);
```

Run `php bin/console config:dump-reference acme_blog` to display this
reference, and `php bin/console debug:config acme_blog` to display the
configuration of your application.

## Usage

The bundle provides a greeter service. Type-hint `Acme\BlogBundle\GreeterInterface`
in the constructor of your services to get it injected by autowiring:

```php
namespace App\Service;

use Acme\BlogBundle\GreeterInterface;

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

Every exception thrown by the bundle implements `Acme\BlogBundle\Exception\ExceptionInterface`.
