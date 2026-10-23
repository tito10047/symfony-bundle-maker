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

    # Adds the greeting to the responses of the application, in the "X-My-Vendor-Everything-Yaml-Greeting" header.
    greeting_header:
        enabled: false

    # The Doctrine entity manager mapping the entities of the bundle, the default one when null.
    model_manager_name: null
```

The same configuration in PHP:

```php
// config/packages/my_vendor_everything_yaml.php
namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return App::config([
    'my_vendor_everything_yaml' => [
        'greeting' => 'Hello',
        'greeting_header' => [
            'enabled' => false,
        ],
        'model_manager_name' => null,
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

## Greeting Header

The bundle can add a greeting header to the responses of your application:

```http
HTTP/1.1 200 OK
X-My-Vendor-Everything-Yaml-Greeting: Hello World!
```

It is disabled by default. Enable it in the configuration of the bundle:

```yaml
# config/packages/my_vendor_everything_yaml.yaml
my_vendor_everything_yaml:
    greeting_header: true
```

The header is only added to the response of the main request, and a header with
the same name set by your application is kept as is. Run
`php bin/console debug:event-dispatcher kernel.response` to display the listener
among the other listeners of this event.

## Doctrine Entity

The bundle provides the `MyVendor\EverythingYamlBundle\Entity\Greeting` entity, stored in the
`my_vendor_everything_yaml_greeting` table. It is mapped when your application uses
[Doctrine ORM with DoctrineBundle](https://symfony.com/doc/current/doctrine.html):

```console
composer require symfony/orm-pack
```

The bundle maps its entities in the default entity manager of your application, whatever its
`auto_mapping` setting. Run `php bin/console doctrine:mapping:info` to check it, and create
the table as explained in the [installation instructions](../README.md#installation).

Use the entity like the ones of your application:

```php
namespace App\Service;

use MyVendor\EverythingYamlBundle\Entity\Greeting;
use MyVendor\EverythingYamlBundle\GreeterInterface;
use Doctrine\ORM\EntityManagerInterface;

final class GreetingRecorder
{
    public function __construct(
        private readonly GreeterInterface $greeter,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function greet(string $name): Greeting
    {
        $greeting = new Greeting($name, $this->greeter->greet($name));
        $this->entityManager->persist($greeting);
        $this->entityManager->flush();

        return $greeting;
    }
}
```

### Using Another Entity Manager

When your application has several entity managers, choose the one mapping the entities of the bundle:

```yaml
# config/packages/my_vendor_everything_yaml.yaml
my_vendor_everything_yaml:
    model_manager_name: customer
```

If another entity manager has `auto_mapping` enabled, it maps the entities of the bundle too.
Exclude them from it:

```yaml
# config/packages/doctrine.yaml
doctrine:
    orm:
        entity_managers:
            default:
                auto_mapping: true
                mappings:
                    MyVendorEverythingYamlBundle: false
```

## Web Assets

The bundle provides a stylesheet, `public/css/my-vendor-everything-yaml.css`. The
`assets:install` command installs it in the `public/bundles/myvendoreverythingyaml/` directory
of your application (see the [installation instructions](../README.md#installation)).
When your application uses AssetMapper, AssetMapper serves it too, with a versioned URL.
It styles the [templates](#templates) of the bundle.

Link it in the `stylesheets` block of your layout, with the `asset()` function of the
[Asset component](https://symfony.com/doc/current/components/asset.html), which gives
the right URL in both cases:

```twig
{# templates/base.html.twig #}
{% block stylesheets %}
    <link rel="stylesheet" href="{{ asset('bundles/myvendoreverythingyaml/css/my-vendor-everything-yaml.css') }}">
{% endblock %}
```
