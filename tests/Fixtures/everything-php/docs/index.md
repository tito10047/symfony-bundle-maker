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
The page is rendered from the `@AcmeEverythingPhp/hello.html.twig` template, see [Templates](#templates).

Link to the page by the name of its route, never by its path, which depends
on the prefix chosen by the application:

```twig
<a href="{{ path('acme_everything_php_hello', {name: 'Jane'}) }}">Greet Jane</a>
```

Run `php bin/console debug:router acme_everything_php_hello` to display the route.

## Templates

The templates of the bundle are in the `@AcmeEverythingPhp` Twig namespace.
`@AcmeEverythingPhp/hello.html.twig` renders the greeting page. Its only variable is
`greeting`, the greeting to display, e.g. "Hello Jane!". You can also include it
in your own templates:

```twig
{{ include('@AcmeEverythingPhp/hello.html.twig', {greeting: 'Hello Jane!'}) }}
```

The templates extend no layout: they display well anywhere in your pages.

### Overriding the templates

To change a template, create a template with the same name in the
`templates/bundles/AcmeEverythingPhpBundle/` directory of your application. For example,
`templates/bundles/AcmeEverythingPhpBundle/hello.html.twig` replaces `@AcmeEverythingPhp/hello.html.twig`:

```twig
{# templates/bundles/AcmeEverythingPhpBundle/hello.html.twig #}
<p class="greeting">{{ greeting }}</p>
```

To only change its `title` block, extend the original template: the `!` prefix
always refers to the template of the bundle.

```twig
{# templates/bundles/AcmeEverythingPhpBundle/hello.html.twig #}
{% extends '@!AcmeEverythingPhp/hello.html.twig' %}

{% block title %}Nice to meet you!{% endblock %}
```

Clear the cache after creating an overriding template for the first time:
`php bin/console cache:clear`.
See [How to Override any Part of a Bundle](https://symfony.com/doc/current/bundles/override.html#templates).
