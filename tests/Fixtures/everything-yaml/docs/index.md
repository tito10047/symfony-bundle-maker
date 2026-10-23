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

## Routes

The bundle provides a page greeting people, once its routes are imported
(see the [installation instructions](../README.md#installation)):

| Route | Path | Controller |
|---|---|---|
| `my_vendor_everything_yaml_hello` | `/hello/{name}` | `MyVendor\EverythingYamlBundle\Controller\HelloController` |

With the `/my-vendor-everything-yaml` prefix, `/my-vendor-everything-yaml/hello/Jane` greets "Hello Jane!", and
`/my-vendor-everything-yaml/hello` greets "Hello World!".
The page is rendered from the `@MyVendorEverythingYaml/hello.html.twig` template, see [Templates](#templates).

Link to the page by the name of its route, never by its path, which depends
on the prefix chosen by the application:

```twig
<a href="{{ path('my_vendor_everything_yaml_hello', {name: 'Jane'}) }}">Greet Jane</a>
```

Run `php bin/console debug:router my_vendor_everything_yaml_hello` to display the route.

## Templates

The templates of the bundle are in the `@MyVendorEverythingYaml` Twig namespace.
`@MyVendorEverythingYaml/hello.html.twig` renders the greeting page. Its only variable is
`greeting`, the greeting to display, e.g. "Hello Jane!". You can also include it
in your own templates:

```twig
{{ include('@MyVendorEverythingYaml/hello.html.twig', {greeting: 'Hello Jane!'}) }}
```

The templates extend no layout: they display well anywhere in your pages.

### Overriding the templates

To change a template, create a template with the same name in the
`templates/bundles/MyVendorEverythingYamlBundle/` directory of your application. For example,
`templates/bundles/MyVendorEverythingYamlBundle/hello.html.twig` replaces `@MyVendorEverythingYaml/hello.html.twig`:

```twig
{# templates/bundles/MyVendorEverythingYamlBundle/hello.html.twig #}
<p class="greeting">{{ greeting }}</p>
```

To only change its `title` block, extend the original template: the `!` prefix
always refers to the template of the bundle.

```twig
{# templates/bundles/MyVendorEverythingYamlBundle/hello.html.twig #}
{% extends '@!MyVendorEverythingYaml/hello.html.twig' %}

{% block title %}Nice to meet you!{% endblock %}
```

Clear the cache after creating an overriding template for the first time:
`php bin/console cache:clear`.
See [How to Override any Part of a Bundle](https://symfony.com/doc/current/bundles/override.html#templates).

## Translations

The messages of the bundle are in the `MyVendorEverythingYaml` translation domain. They
are written in English, and their ids are the English messages: they display
well even when your application has no translator.
The [templates](#templates) translate them with the `trans` filter:

```twig
{{ 'Welcome!'|trans({}, 'MyVendorEverythingYaml') }}
```

To translate them into another language, or to change them, create a file
for the `MyVendorEverythingYaml` domain in the `translations/` directory of your
application, e.g. `translations/MyVendorEverythingYaml.fr.xlf`. Its messages take precedence over the
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

Run `php bin/console debug:translation en --domain=MyVendorEverythingYaml` to list the
messages of the bundle, and `php bin/console lint:xliff translations` to check
your files. See [How to Override any Part of a Bundle](https://symfony.com/doc/current/bundles/override.html#translations).

## Console Command

The `my-vendor-everything-yaml:hello` command greets someone with the greeter:

```console
$ php bin/console my-vendor-everything-yaml:hello Jane

 [OK] Hello Jane!

$ php bin/console my-vendor-everything-yaml:hello Jane --yell

 [OK] HELLO JANE!
```

Run `php bin/console my-vendor-everything-yaml:hello --help` to display its usage, and
`php bin/console list my-vendor-everything-yaml` to list the commands of the bundle.
