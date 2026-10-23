# Bundle Maker

Composer plugin for the [Symfony Bundle Skeleton](https://github.com/tito10047/symfony-bundle-skeleton).

Right after `composer create-project tito10047/symfony-bundle-skeleton`, it asks a few questions and
generates a bundle that follows the [bundle best practices](https://symfony.com/doc/current/bundles/best_practices.html),
for Symfony 7.4 LTS and 8.1+ (`^7.4.17 || ^8.1.5`). It then installs the bundle dependencies with a single
`composer update` and removes itself.

## Features

- [x] Derives every name from the package name: `acme/blog-bundle` gives the namespace `Acme\BlogBundle`,
      the class `AcmeBlogBundle`, the extension alias `acme_blog`, the translation domain and Twig namespace `AcmeBlog`.
- [x] Generates the bundle class (`AbstractBundle`) and explicit service definitions, in PHP, PHP array or YAML format.
- [x] Generates every file the best practices ask for: `README.md` with the standard installation instructions,
      `docs/index.md`, `LICENSE` (full text of the chosen license), `CHANGELOG.md`, `.gitattributes`, `.gitignore`
      and `.editorconfig`.
- [x] Generates a working example (a greeter service, its interface and the exceptions of the bundle) and a
      PHPUnit test suite with a test kernel, fully covered and failing on deprecations caused by the bundle.
- [x] Optional features, each with its tests and documentation, working alone or together: a controller with its
      Twig template and translations, an invokable console command loaded lazily, and
      a `kernel.response` event listener enabled through the configuration.
- [x] Writes a complete `composer.json` and installs the dependencies in one `composer update`.
- [x] Runs without interaction, every answer can be preset with an environment variable.

### Optional features

Each feature is a question of the assistant, and a `BUNDLE_MAKER_<FEATURE>` environment variable:

| Feature | Variable | Default | Generates |
|---|---|---|---|
| Semantic configuration | `BUNDLE_MAKER_CONFIG` | yes | `config/definition.php`, its documentation and tests |
| Controller | `BUNDLE_MAKER_CONTROLLERS` | no | an invokable controller, its route (`config/routes.*`, imported by applications) and its functional test |
| Twig template | `BUNDLE_MAKER_TWIG` | yes with a controller | `templates/hello.html.twig` (`@AcmeBlog` namespace, no layout), rendered by the controller |
| Translations | `BUNDLE_MAKER_TRANSLATIONS` | no | `translations/AcmeBlog.en.xlf`, in the domain of the bundle, used by the template |
| Console command | `BUNDLE_MAKER_COMMAND` | no | an invokable command (`acme-blog:hello`), loaded lazily, and its tests |
| Event listener | `BUNDLE_MAKER_EVENT_LISTENER` | no | a `kernel.response` listener adding a greeting header, enabled by the `greeting_header` option (requires the semantic configuration), and its tests |

## Requirements

PHP 8.2+ and Composer 2.6+. Composer 2.6 is the first version compatible with Symfony 7,
which the test suite of the plugin loads together with Composer: older versions are not tested.

## Usage without interaction

Every answer can be preset with a `BUNDLE_MAKER_*` environment variable, which is especially useful
in CI or scripts:

```console
$ BUNDLE_MAKER_NAME=acme/blog-bundle BUNDLE_MAKER_AUTHOR_NAME="Jane Doe" BUNDLE_MAKER_CONFIG_FORMAT=yaml BUNDLE_MAKER_ALL=1 \
  composer create-project tito10047/symfony-bundle-skeleton blog-bundle --no-interaction
```

| Variable | Value |
|---|---|
| `BUNDLE_MAKER_NAME` | Composer package name, e.g. `acme/blog-bundle` |
| `BUNDLE_MAKER_DESCRIPTION` | package description (asked without default in interactive mode) |
| `BUNDLE_MAKER_AUTHOR_NAME`, `BUNDLE_MAKER_AUTHOR_EMAIL` | author (defaults to your Git configuration; `none` omits the email) |
| `BUNDLE_MAKER_LICENSE` | `MIT` (default), `Apache-2.0`, `BSD-2-Clause`, `BSD-3-Clause`, `GPL-3.0-or-later`, `LGPL-3.0-or-later`, `MPL-2.0`, `proprietary` |
| `BUNDLE_MAKER_CONFIG_FORMAT` | `php` (default), `php-array` or `yaml` |
| `BUNDLE_MAKER_ALL` | `1` enables every feature, `0` disables them all |
| `BUNDLE_MAKER_<FEATURE>` | `1` or `0` for a single feature, e.g. `BUNDLE_MAKER_CONFIG=0` |

In interactive mode, presets become the proposed default answers. Every preset is
validated before the first question: an unknown `BUNDLE_MAKER_*` variable is an error. A
feature is not asked when a feature it requires is disabled. Without interaction, enabling a
feature whose required feature is disabled is an error too (e.g. `BUNDLE_MAKER_EVENT_LISTENER=1`
with `BUNDLE_MAKER_CONFIG=0`); in interactive mode, the required feature is asked first.

## Running the assistant again

The assistant runs on `post-create-project-cmd`, also with `--no-dev`: the skeleton
requires the plugin in `require`. It does not run when `create-project` is given
`--no-plugins`, nor with `--no-install`, as the plugin is not installed then. If
something fails before the dependencies are installed, the skeleton is left as is. In these cases, install the
dependencies of the skeleton if needed and run the assistant again:

```console
$ composer install
$ composer run-script post-create-project-cmd
```

## Installing the dependencies of the bundle

The assistant installs the dependencies of the bundle with one `composer update`, in the
same process, with the plugins of the bundle (e.g. `phpstan/extension-installer`). The
verbosity of `create-project` applies to it, but not its other options: use the environment
variables Composer reads instead, e.g. `COMPOSER_IGNORE_PLATFORM_REQ=ext-intl`,
`COMPOSER_IGNORE_PLATFORM_REQS=1` or `COMPOSER_NO_AUDIT=1`. If the update fails, fix the
issue and run `composer update` in the bundle directory.

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md) for the architecture, how to add a feature, and the test workflow.

## Credits

Based on [yceruto/bundle-skeleton](https://github.com/yceruto/bundle-skeleton) and
[yceruto/bundle-flex](https://github.com/yceruto/bundle-flex) by [Yonel Ceruto](https://github.com/yceruto).

## License

This software is published under the [MIT License](LICENSE)
