# Keeps the GitHub Actions and the Composer dependencies up to date.
# See https://docs.github.com/code-security/dependabot/working-with-dependabot/dependabot-options-reference

version: 2
updates:
  - package-ecosystem: github-actions
    directory: /
    schedule:
      interval: weekly
    groups:
      github-actions:
        patterns: ['*']

  - package-ecosystem: composer
    directory: /
    schedule:
      interval: weekly
    # a bundle has no composer.lock: only widen the constraints to allow new major versions
    versioning-strategy: widen
    groups:
      # the Symfony packages are released together
      symfony:
        patterns: ['symfony/*']
      # the development tools: tests and quality checks
      dev-dependencies:
        dependency-type: development
