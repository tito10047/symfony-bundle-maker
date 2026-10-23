# Contributing to Bundle Maker

Bundle Maker is the Composer plugin behind
[`tito10047/symfony-bundle-skeleton`](https://github.com/tito10047/symfony-bundle-skeleton).
This guide explains how it works and how to extend it. The docblocks are the
reference: start with `BundleBlueprint`, `Slot`, `ListSlot` and `FeatureInterface`.

The generated bundle must follow the
[bundle best practices](https://symfony.com/doc/current/bundles/best_practices.html)
and support every version of `Constraint`: Symfony `^7.4.17 || ^8.1.5` (7.4 LTS on
PHP 8.2+, 8.1 on PHP 8.4+), so generated code must run on PHP 8.2. The patch floors
are the first releases whose ErrorHandler no longer leaks its exception handler
into the tests of the bundle; the generated `require-dev` enforces them with
`symfony/error-handler`.

## Quick start

```console
$ composer install
$ composer qa          # coding standards, static analysis and tests
$ composer cs:fix      # fix coding standards
```

## How it works

`composer create-project tito10047/symfony-bundle-skeleton` installs this plugin
in the new project. On `post-create-project-cmd`, `Plugin` runs `BundleCreator`, which:

1. asks the questions and shows a summary (`Options\BundleOptionsAsker`). Every
   `BUNDLE_MAKER_*` preset is validated before the first question;
2. generates the bundle (`Generator\BundleGenerator`) in two phases:
   - **contribution**: the core contributors, then the enabled features, describe
     the bundle in a `BundleBlueprint`. Nothing is written;
   - **rendering**: every file of the blueprint is rendered in memory. Only then are
     the skeleton files removed, the files written, and `composer.json` last;
3. runs one `composer update` in-process (`Composer\CommandRunner`), with the
   plugins of the bundle enabled (e.g. `phpstan/extension-installer`). The plugin
   is no longer required, so this also uninstalls it;
4. prints the next steps.

A failure before the update leaves the skeleton untouched: `Plugin` prints the
error and how to run the assistant again (`composer run-script post-create-project-cmd`).
Once the update has started, the plugin files are gone from `vendor/`: code that
runs after it may only use classes that are already loaded.

```
src/
├── Plugin.php                   Composer plugin entry point, error reporting
├── BundleCreator.php            orchestration
├── Naming/                      BundleNaming: every name derived from the package name
├── Options/                     answers (BundleOptions), the assistant, env presets, validation
├── Feature/                     FeatureInterface, FeatureRegistry, the optional features
├── Contributor/                 ContributorInterface, CoreContributors: what every bundle has
├── Blueprint/                   the contract: BundleBlueprint and its value objects
├── Template/                    TemplateRenderer, TemplateHelper, SlotRenderer, PhpImports
├── Generator/BundleGenerator.php
└── Composer/                    ComposerJsonWriter, CommandRunner
templates/
├── core/                        templates of the core contributors, and the shared ("master") templates
└── features/<feature-name>/     templates of a feature; its partials go in partials/
tests/
├── Fixtures/<scenario>/         snapshots: the expected bundle of each scenario
├── Snapshot/                    scenarios, snapshot test, "the generated bundle works" test
└── Support/                     test doubles, and KitchenSinkFeature, which uses the whole blueprint API
```

## Names

`BundleNaming` derives every name from the package name (`acme/blog-bundle` gives
`Acme\BlogBundle`, `AcmeBlogBundle`, the alias `acme_blog`, the slug `acme-blog`...).
Templates and contributors use it and never compute a name themselves.

## Templates

Templates are native PHP files: `templates/<path>.tpl` renders `<path>`. The plugin
runs inside Composer, so it cannot bring a template engine; the `.tpl` extension
keeps templates out of PHP-CS-Fixer, PHPStan and `php -l`.

- Every template gets `$options`, `$naming`, `$blueprint`, `$slots` and `$helper`,
  plus the parameters given to `addFile()` or `addPartial()`, which cannot override
  them. Start each template with a `/** @var ... */` block listing its variables.
- A reported notice or warning, e.g. an undefined variable, aborts the rendering.
- **PHP removes the newline that directly follows `?>`.** One convention, checked
  by `TemplateConventionsTest`: control tags (`<?php if (...): ?>`, `<?php endif ?>`)
  are alone on their line, at column 0; an echo tag that ends a line is followed by
  an empty line (`class: <?= $x ?>` then an empty line outputs `class: X` and a
  newline), unless it echoes whole lines, which end with a newline themselves:
  `$slots->render()` or a string literal ending with `\n`.
- A template generating PHP outputs the opening tag itself: `<?= "<?php\n" ?>`.
- Encode every value you did not write for its context: `$helper->php()`,
  `$helper->yaml()`, `$helper->xml()`. Build `use` statements with `$helper->imports()`.
- Generated code must be clean for the generated quality tools (PHPStan).

## The contract: `BundleBlueprint`

Contributors only describe the bundle, they never write files. Invalid
contributions throw as early as possible.

- `addFile($template, $destination, $parameters)`: a file rendered from a template.
- `$composer`: dependencies (`Constraint::PHP`, `Constraint::SYMFONY`), `conflict` and
  `suggest` entries, keywords, scripts (`qa: true` adds a script to `composer qa`) and
  allowed plugins.
- `$services`, `$routes`: explicit service and route definitions. They are written to
  `config/services.*` and `config/routes.*` in the configuration format the user chose
  (`ConfigFormat`): never write these files yourself.
- `$slots`: partials and values for the master templates (`Slot`, `ListSlot`). A slot
  that nothing renders is a bug: `SlotsTest` checks that every slot is rendered. E.g.
  `Slot::ReadmeInstallationSteps` adds a `### <title>` section after the standard
  Flex / non-Flex installation instructions.
- `hasFeature($name)`: whether another feature is enabled.

## Adding a feature

1. Extend `AbstractFeature` in `src/Feature/`, e.g. like `CommandFeature`: it describes
   the feature with constants (`NAME`, `QUESTION`, `GROUP`, `ENABLED_BY_DEFAULT`,
   `REQUIRED_FEATURES`); override `isEnabledByDefault()` when the default depends on
   previous answers. The name defines the environment variable presetting the answer
   (`event-listener` gives `BUNDLE_MAKER_EVENT_LISTENER`), so never rename one.
   `contribute()` only runs when the feature is enabled.
   A feature that builds on another one requires it (`REQUIRED_FEATURES`, e.g. the
   event listener requires `config`): it is not asked, and stays disabled, when a
   required feature is disabled, and presets enabling it anyway are rejected without
   interaction (in interactive mode, presets are only default answers).
2. Put its templates in `templates/features/<name>/`, mirroring the destination
   paths, and its partials in `partials/`.
3. Register it in `FeatureRegistry::createDefault()`, at the right place in its
   group: the order is both the order of the questions and of the contributions.
   `isEnabledByDefault()` may only read the answers of the features registered before,
   and required features must be registered before (`FeatureRegistry` checks it).
   The quality assurance features come last, as they adapt to everything else.
4. Generate the tests and the documentation of what it generates (`Slot::DocsSections`):
   the generated code stays fully covered.
5. Test it in `tests/Feature/`: render a `Scenario` with the feature and assert what
   matters (`Scenario::render()`). The everything scenarios of the snapshots include it.

A file every bundle has belongs to a core contributor
(`CoreContributors::createDefault()`) instead.

## Tests

| What | Where |
|---|---|
| Unit tests | one test class per class, in the mirrored namespace |
| Snapshots | `tests/Fixtures/<scenario>/`: the full expected output of each scenario of `tests/Snapshot/Scenarios.php` (minimal, everything and kitchen sink, in every format) |
| Working output | `GeneratedBundleTest` lints the snapshots, validates `composer.json`, checks the coding standards and loads the services and routes in a real container in every format |

Snapshots are regenerated, then reviewed, with:

```console
$ UPDATE_SNAPSHOTS=1 composer test   # regenerate tests/Fixtures (orphan fixtures are deleted)
$ git diff tests/Fixtures
```

The plugin supports PHP 8.2 and Composer 2.6 (the lowest dependencies). To run
its tests on PHP 8.2, copy the working copy (`git archive` would omit the
export-ignored tests) and use a container. For the lowest dependencies, add
`--prefer-lowest`, and disable the blocking of the versions affected by security
advisories, or Composer installs newer versions than the lower bounds:

```console
$ git clone . /tmp/maker82
$ docker run --rm -v /tmp/maker82:/app -w /app composer:2 sh -c \
  'composer config --global policy.advisories.block false && composer config platform.php 8.2.0 && composer update --prefer-lowest'
$ docker run --rm -v /tmp/maker82:/app -w /app php:8.2-cli vendor/bin/phpunit
```

