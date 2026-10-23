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
The page is rendered from the `@AcmeEverythingPhpArray/hello.html.twig` template, see [Templates](#templates).

Link to the page by the name of its route, never by its path, which depends
on the prefix chosen by the application:

```twig
<a href="{{ path('acme_everything_php_array_hello', {name: 'Jane'}) }}">Greet Jane</a>
```

Run `php bin/console debug:router acme_everything_php_array_hello` to display the route.

## Templates

The templates of the bundle are in the `@AcmeEverythingPhpArray` Twig namespace.
`@AcmeEverythingPhpArray/hello.html.twig` renders the greeting page. Its only variable is
`greeting`, the greeting to display, e.g. "Hello Jane!". You can also include it
in your own templates:

```twig
{{ include('@AcmeEverythingPhpArray/hello.html.twig', {greeting: 'Hello Jane!'}) }}
```

The templates extend no layout: they display well anywhere in your pages.

### Overriding the templates

To change a template, create a template with the same name in the
`templates/bundles/AcmeEverythingPhpArrayBundle/` directory of your application. For example,
`templates/bundles/AcmeEverythingPhpArrayBundle/hello.html.twig` replaces `@AcmeEverythingPhpArray/hello.html.twig`:

```twig
{# templates/bundles/AcmeEverythingPhpArrayBundle/hello.html.twig #}
<p class="greeting">{{ greeting }}</p>
```

To only change its `title` block, extend the original template: the `!` prefix
always refers to the template of the bundle.

```twig
{# templates/bundles/AcmeEverythingPhpArrayBundle/hello.html.twig #}
{% extends '@!AcmeEverythingPhpArray/hello.html.twig' %}

{% block title %}Nice to meet you!{% endblock %}
```

Clear the cache after creating an overriding template for the first time:
`php bin/console cache:clear`.
See [How to Override any Part of a Bundle](https://symfony.com/doc/current/bundles/override.html#templates).

## Translations

The messages of the bundle are in the `AcmeEverythingPhpArray` translation domain. They
are written in English, and their ids are the English messages: they display
well even when your application has no translator.
The [templates](#templates) translate them with the `trans` filter:

```twig
{{ 'Welcome!'|trans({}, 'AcmeEverythingPhpArray') }}
```

To translate them into another language, or to change them, create a file
for the `AcmeEverythingPhpArray` domain in the `translations/` directory of your
application, e.g. `translations/AcmeEverythingPhpArray.fr.xlf`. Its messages take precedence over the
messages of the bundle:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<xliff xmlns="urn:oasis:names:tc:xliff:document:1.2" version="1.2">
    <file source-language="en" target-language="fr" datatype="plaintext" original="file.ext">
        <body>
            <trans-unit id="1">
                <source>Welcome!</source>
                <target>Bienvenue !</target>
            </trans-unit>
        </body>
    </file>
</xliff>
```

Run `php bin/console debug:translation en --domain=AcmeEverythingPhpArray` to list the
messages of the bundle, and `php bin/console lint:xliff translations` to check
your files. See [How to Override any Part of a Bundle](https://symfony.com/doc/current/bundles/override.html#translations).

## Console Command

The `acme-everything-php-array:hello` command greets someone with the greeter:

```console
$ php bin/console acme-everything-php-array:hello Jane

 [OK] Hello Jane!

$ php bin/console acme-everything-php-array:hello Jane --yell

 [OK] HELLO JANE!
```

Run `php bin/console acme-everything-php-array:hello --help` to display its usage, and
`php bin/console list acme-everything-php-array` to list the commands of the bundle.
