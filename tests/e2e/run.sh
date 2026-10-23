#!/usr/bin/env bash
#
# End-to-end test of the plugin: generates a bundle with "composer create-project"
# from a bundle-skeleton checkout and this working copy of the plugin, checks it,
# optionally reinstalls its dependencies for a given Symfony version, and runs its
# own Composer scripts (tests and quality checks).
#
# Usage: tests/e2e/run.sh <target-dir>
#
# The answers of the assistant are taken from the BUNDLE_MAKER_* environment
# variables (see CONTRIBUTING.md). The run is configured with:
#
#   E2E_SKELETON_DIR         bundle-skeleton checkout; when empty, E2E_SKELETON_REPOSITORY
#                            is cloned at E2E_SKELETON_REF
#   E2E_SKELETON_REPOSITORY  default: https://github.com/tito10047/symfony-bundle-skeleton.git
#   E2E_SKELETON_REF         default: main
#   E2E_PLUGIN_VERSION       version given to this working copy, it must satisfy the
#                            skeleton's "tito10047/symfony-bundle-maker" constraint (default: 1.0.0)
#   E2E_SYMFONY              Symfony versions to install, e.g. "7.4.*" (SYMFONY_REQUIRE with
#                            Symfony Flex, as in the generated CI); default: none
#   E2E_DEPENDENCIES         "highest" (default) or "lowest" (composer update --prefer-lowest, with
#                            the blocking of versions affected by security advisories disabled, so
#                            the lower bounds of the constraints are installed; needs Composer 2.10+)
#   E2E_SCRIPTS              Composer scripts of the bundle to run, in order (default: "qa rector": "qa"
#                            runs the tests first, "rector" checks that the generated code needs no
#                            refactoring, as "qa" does not run Rector); a missing script fails the run,
#                            so use "qa" for a bundle without Rector, e.g. BUNDLE_MAKER_ALL=0
#
# Without E2E_SYMFONY and with the highest dependencies, the bundle is tested as created:
# with the dependencies installed by the assistant, nothing is reinstalled.
#
# Both checkouts are copied without their ignored files (tracked and untracked files,
# .gitignore applies); the skeleton is then exported with "git archive" from a temporary
# repository, so its export-ignore rules apply as for the dist archive. Nothing is written
# to the checkouts. Neither composer.json is modified: the plugin repository is declared in
# the global configuration of a temporary COMPOSER_HOME, so nothing test-specific can leak
# into the generated composer.json.

set -euo pipefail

if [[ $# -ne 1 ]]; then
    echo "Usage: $0 <target-dir>" >&2
    exit 64
fi

plugin_dir=$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)
case $1 in
    /*) target_dir=$1 ;;
    *) target_dir=$PWD/$1 ;;
esac
skeleton_dir=${E2E_SKELETON_DIR:-}
skeleton_repository=${E2E_SKELETON_REPOSITORY:-https://github.com/tito10047/symfony-bundle-skeleton.git}
skeleton_ref=${E2E_SKELETON_REF:-main}
plugin_version=${E2E_PLUGIN_VERSION:-1.0.0}
symfony=${E2E_SYMFONY:-}
dependencies=${E2E_DEPENDENCIES:-highest}
read -r -a scripts <<< "${E2E_SCRIPTS-qa rector}"

fail() {
    if [[ -n ${GITHUB_ACTIONS:-} ]]; then echo "::error::e2e: $*"; else echo "e2e: error: $*" >&2; fi
    exit 1
}

group() {
    if [[ -n ${GITHUB_ACTIONS:-} ]]; then echo "::group::$*"; else echo "==> $*"; fi
}

endgroup() {
    if [[ -n ${GITHUB_ACTIONS:-} ]]; then echo "::endgroup::"; fi
}

# Copies the files of a Git working copy that are not ignored (tracked and untracked ones).
copy_worktree() {
    mkdir -p "$2"
    (
        cd "$1"
        git ls-files -z --cached --others --exclude-standard | while IFS= read -r -d '' file; do
            # tracked files deleted from the working copy are listed too
            if [[ -e $file || -L $file ]]; then printf '%s\0' "$file"; fi
        done | tar --null -T - -cf -
    ) | tar -xf - -C "$2"
}

# A Composer path repository giving a version to the package of a directory.
repository() {
    # shellcheck disable=SC2016 # PHP code, not shell expansions
    php -r '
        echo json_encode(
            ["type" => "path", "url" => $argv[1], "options" => ["symlink" => false, "versions" => [$argv[2] => $argv[3]]]],
            JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES,
        );
    ' -- "$1" "$2" "$3"
}

[[ $dependencies == highest || $dependencies == lowest ]] || fail "E2E_DEPENDENCIES must be \"highest\" or \"lowest\", got \"$dependencies\""
[[ -e $target_dir ]] && fail "$target_dir already exists"

work_dir=$(mktemp -d)
trap 'rm -rf "$work_dir"' EXIT

# A temporary Composer home: the cache and the credentials of the real one are kept.
COMPOSER_CACHE_DIR=${COMPOSER_CACHE_DIR:-$(composer config --global cache-dir)}
real_home=$(composer config --global home)
export COMPOSER_CACHE_DIR
export COMPOSER_HOME=$work_dir/composer-home
mkdir -p "$COMPOSER_HOME"
if [[ -f $real_home/auth.json ]]; then cp "$real_home/auth.json" "$COMPOSER_HOME/"; fi

group "Copy the plugin"
copy_worktree "$plugin_dir" "$work_dir/bundle-maker"
endgroup

group "Export the skeleton"
if [[ -z $skeleton_dir ]]; then
    skeleton_dir=$work_dir/skeleton-checkout
    git clone --quiet --depth 1 --branch "$skeleton_ref" "$skeleton_repository" "$skeleton_dir"
fi
[[ -f $skeleton_dir/composer.json ]] || fail "$skeleton_dir is not a bundle-skeleton checkout"
copy_worktree "$skeleton_dir" "$work_dir/skeleton-worktree"
git -C "$work_dir/skeleton-worktree" init --quiet
git -C "$work_dir/skeleton-worktree" add --all
mkdir "$work_dir/skeleton"
git -C "$work_dir/skeleton-worktree" archive "$(git -C "$work_dir/skeleton-worktree" write-tree)" | tar -xf - -C "$work_dir/skeleton"
ls -A "$work_dir/skeleton"
# the files of the skeleton repository itself must not be shipped
for file in .gitattributes .github CLAUDE.md; do
    if [[ -e $work_dir/skeleton/$file ]]; then
        fail "the skeleton ships $file in its dist archive, export-ignore it in .gitattributes"
    fi
done
endgroup

composer config --global repositories.bundle-maker "$(repository "$work_dir/bundle-maker" tito10047/symfony-bundle-maker "$plugin_version")"

group "composer create-project"
composer create-project tito10047/symfony-bundle-skeleton "$target_dir" --no-interaction --no-progress \
    --repository="$(repository "$work_dir/skeleton" tito10047/symfony-bundle-skeleton 1.0.0)"
endgroup

cd "$target_dir"

group "Check the generated bundle"
# shellcheck disable=SC2016 # PHP code, not shell expansions
php -r '
    $json = json_decode(file_get_contents("composer.json"), true, flags: JSON_THROW_ON_ERROR);
    $errors = [];
    foreach (["repositories", "minimum-stability", "prefer-stable"] as $key) {
        if (array_key_exists($key, $json)) {
            $errors[] = "the skeleton leaked \"$key\"";
        }
    }
    if (str_contains(file_get_contents("composer.json"), "tito10047/symfony-bundle-maker")) {
        $errors[] = "tito10047/symfony-bundle-maker is still referenced";
    }
    if ("symfony-bundle" !== ($json["type"] ?? null)) {
        $errors[] = "the type is not \"symfony-bundle\"";
    }
    $name = getenv("BUNDLE_MAKER_NAME");
    if (false !== $name && $name !== ($json["name"] ?? null)) {
        $errors[] = "the name is not \"$name\"";
    }
    foreach ($errors as $error) {
        fwrite(STDERR, "e2e: error: composer.json: $error\n");
    }
    exit([] === $errors ? 0 : 1);
'
[[ -d vendor/tito10047/symfony-bundle-maker ]] && fail "the plugin is still installed"

# The Composer plugins of the bundle must have run during the update done by the assistant:
# phpstan/extension-installer registers every installed PHPStan extension.
if [[ -d vendor/phpstan/extension-installer ]]; then
    # shellcheck disable=SC2016 # PHP code, not shell expansions
    php -r '
        require "vendor/phpstan/extension-installer/src/GeneratedConfig.php";
        $installed = json_decode(file_get_contents("vendor/composer/installed.json"), true, flags: JSON_THROW_ON_ERROR);
        $extensions = [];
        foreach ($installed["packages"] as $package) {
            if ("phpstan-extension" === ($package["type"] ?? null)) {
                $extensions[] = $package["name"];
            }
        }
        $registered = array_keys(PHPStan\ExtensionInstaller\GeneratedConfig::EXTENSIONS);
        echo "PHPStan extensions: ", implode(", ", $registered) ?: "none", "\n";
        if ([] === $extensions) {
            fwrite(STDERR, "e2e: error: phpstan/extension-installer is installed without any PHPStan extension\n");
            exit(1);
        }
        if ([] !== $missing = array_diff($extensions, $registered)) {
            fwrite(STDERR, "e2e: error: phpstan/extension-installer did not register ".implode(", ", $missing).", did the Composer plugins run?\n");
            exit(1);
        }
    '
fi

composer validate --strict
composer dump-autoload --optimize --strict-psr
find . -path ./vendor -prune -o -name '*.php' -type f -print0 \
    | xargs -0 -n 1 -P "$(getconf _NPROCESSORS_ONLN 2> /dev/null || echo 2)" php -l > /dev/null
endgroup

if [[ -n $symfony || $dependencies == lowest ]]; then
    group "Install the ${dependencies} dependencies${symfony:+ with Symfony $symfony}"
    # exactly as the generated CI workflow, see https://symfony.com/doc/current/bundles/best_practices.html
    composer global config --no-plugins allow-plugins.symfony/flex true
    composer global require --no-progress --no-scripts --no-plugins symfony/flex
    options=(--prefer-dist --no-progress --no-interaction)
    if [[ $dependencies == lowest ]]; then
        # Composer refuses the versions affected by security advisories, so --prefer-lowest would install
        # the lowest versions without advisory instead of the lower bounds of the constraints. Set in the
        # temporary COMPOSER_HOME, so for this run only.
        composer config --global policy.advisories.block false
        options+=(--prefer-lowest --prefer-stable)
    fi
    if [[ -n $symfony ]]; then export SYMFONY_REQUIRE=$symfony; fi
    composer update "${options[@]}"
    composer show --direct
    endgroup
fi

version=$(php -r 'require "vendor/autoload.php"; echo Symfony\Component\HttpKernel\Kernel::VERSION;')
echo "Symfony $version, PHP $(php -r 'echo PHP_VERSION;'), $dependencies dependencies"
# shellcheck disable=SC2053 # glob matching on purpose: "7.4.*" matches "7.4.3"
if [[ -n $symfony && $version != $symfony ]]; then
    fail "Symfony $symfony was expected, $version is installed"
fi

defined_scripts=$(php -r 'echo implode("\n", array_keys(json_decode(file_get_contents("composer.json"), true)["scripts"] ?? []));')
# "${scripts[@]}" fails on an empty array with "set -u" before Bash 4.4
for script in ${scripts[@]+"${scripts[@]}"}; do
    grep -qxF "$script" <<< "$defined_scripts" || fail "the generated bundle has no \"$script\" script"

    group "composer $script"
    composer "$script"
    endgroup
done
