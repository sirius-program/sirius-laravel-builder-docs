# Package integration and public documentation

Phase 1 provides configuration, dependency discovery, and prerequisite checks. It does **not** generate CRUD files. Phase 2 adds [recursive discovery and typed resource planning](DISCOVERY.md) as library services. Schema inspection, the interactive wizard, and publishable generation stubs follow their own phases.

## Install and check a consuming application

The package targets PHP `^8.3`, Laravel `^12.61.1 || ^13.12.0`, and Livewire 4. Both projects currently install Sirius UI 0.1.0. The documentation application's newer development tools do not raise the package runtime minimum.

Until Builder has a published release, clone it beside an existing Laravel application and run these commands in that application:

```shell
composer config repositories.sirius-builder path ../sirius-laravel-builder
composer require sirius/laravel-builder:@dev
php artisan make:crud --check
php artisan vendor:publish --tag=sirius-builder-config
```

Laravel discovers the service provider through Composer metadata. `--check` returns 0 when prerequisites are ready and 1 with actionable diagnostics otherwise. It checks dependency classes, configured paths, and connectivity to the default database. It executes no record queries, writes no records, and generates no application files. Running without `--check` reports that generation is not available and returns 1, even when prerequisites pass.

The default database check does not yet discover models or inspect model-specific connections. Read-only path checks are not the later generator's filesystem containment or atomic-write guarantees.

## Configuration

The publishing tag writes `config/sirius-builder.php`. Re-publishing preserves an existing file unless `--force` is supplied explicitly.

| Setting | Default | Current behavior |
| --- | --- | --- |
| `paths.models` | `app/Models` | Must be an existing directory |
| `paths.enums` | `app/Enums` | May be absent; must be a directory if present |
| `paths.stubs` | `stubs/sirius-builder` | Reserved for Phase 6; may be absent |
| `paths.routes` | `routes/web.php` | Must be an existing readable/writable file |
| `routes.prefix` | empty string | Reserved route default |
| `routes.name_prefix` | empty string | Reserved route-name default |
| `routes.middleware` | `['auth']` | Reserved consumer authentication default |

Paths must be nonempty and relative to the application root, without parent traversal. Route defaults do not add routes yet. Publishable **stubs** remain a Phase 6 deliverable; Phase 1 publishes configuration only.

## Sirius UI and skills

The docs app imports the installed Sirius CSS and JavaScript once through Vite, and loads Livewire assets once. Do not separately initialize Alpine. Its layout uses Sirius semantic light/dark tokens and Sirius buttons, badges, icons, links, and code components. Theme selection persists in browser local storage without an account, falling back to the operating-system preference.

Both projects contain the complete `.agents/skills/sirius-ui-development` bundle, including all referenced Markdown files and its ownership/hash manifest. The **standalone Sirius installer** owns both installations; do not also select this skill in Boost for the same directory.

After a Sirius upgrade, refresh the docs skill with:

```shell
php artisan sirius:skills:install --agent=codex --no-interaction
```

The library root has no application `artisan`. Run the following PHP through its installed Composer autoloader (for example, a temporary local PHP script executed from the package root):

```php
<?php
require 'vendor/autoload.php';

$installer = new Sirius\Ui\Support\SkillInstaller(new Illuminate\Filesystem\Filesystem);
$plan = $installer->plan(getcwd(), 'codex');
$installer->apply($plan);
```

Review skill changes after upgrading. The installer preserves customized-file conflicts instead of silently replacing them. Resolve those conflicts deliberately; do not force replacement automatically.

## Public documentation

The public routes are `/`, `/docs`, `/docs/getting-started`, and `/docs/architecture`. Documentation pages follow the Sirius UI docs layout: a fixed 16rem desktop sidebar, sticky title/breadcrumb header, and a Sirius Slideover menu below the desktop breakpoint. Sirius Menu categories/items provide navigation and active states; Breadcrumb, Button, Icon, Link, Badge, and Separator supply the remaining shell controls. The homepage retains its separate approved layout. The homepage introduces the package and distinguishes working setup checks from planned page/dialog CRUD generation.

Fortify, Flux, their integration code, authentication/account screens, and their routes have been removed. Login, registration, logout, password-reset, dashboard, and settings requests return 404. Existing user data structures remain intact. This does not remove authentication or policy requirements from future generated applications.

## Verification

Run `composer test` in both projects. It checks formatting, static analysis, Rector suggestions, and Pest tests. Package feature tests use Laravel's package manifest discovery against a disposable consumer fixture and isolate database access in SQLite memory; they cover command registration, configuration, publishing preservation, path failures, and connection diagnostics.

For docs browser checks, build assets and install Chromium first:

```shell
npm ci
npm run build
npx playwright install chromium
composer test:browser
```

Browser tests cover public navigation, theme persistence, absence of authentication controls, JavaScript errors, and mobile overflow. They use built assets independently of any developer Vite hot file. The default docs `composer test` excludes the browser group; run both gates for UI changes. Screenshots and traces are local ignored artifacts.

See [the implementation evidence](IMPLEMENTATION_PLAN.md#8-execution-evidence-log) for actual versions and results. These checks do not establish the future generator, database-driver matrix, or minimum-dependency release matrix.

Documentation typography follows Sirius UI documentation tokens and loads Instrument Sans through `@fonts`: 16px body text, 30px page headings, 20px section headings, 18px subheadings, and the same monospace stack for code (at the default root size). The landing page keeps its separate typography.

Breadcrumb trails are maintained in `routes/breadcrumbs.php` using `diglactic/laravel-breadcrumbs` 10.1; `resources/views/partials/breadcrumbs.blade.php` renders them with Sirius Breadcrumb components. Add a route-named trail when adding a docs page. The main container is centered within the workspace at a maximum 80rem, with a centered 56rem article. The footer provides a native Back to top link with a sticky-header-aware target.

Code examples use the shared `docs-code` component with Sirius Code and Button components, a language header, and a Copy button. Copy preserves the exact snippet text and line breaks. Each block announces success independently; if clipboard access is denied or unavailable, the snippet is selected for manual copying.

Internal homepage, sidebar, breadcrumb, and article links use Livewire `wire:navigate` for SPA-like transitions. Pages remain server-rendered Blade views; Livewire manages navigation and browser history. External and same-page anchor links retain native navigation. Theme and copy controls reinitialize on `livewire:navigated`, while pending copy-feedback timers clear on navigation; delegated handlers are registered once. Sirius handles its own overlay cleanup and initialization.

Each docs page passes an explicit `navigation` map (heading ID to label) to `docs-layout`. The shared Sirius Link-based On this page navigation sits in a sticky right column on wide screens and above the article on smaller screens. Add matching unique heading IDs when extending an article. Native fragment links work with Livewire transitions and leave room for the sticky header.

## Phase 3 local database test configuration

Only the owner creates/edits environment files. Create `D:\Projects\sirius-laravel-builder\.env.database` manually in the package root, not in the docs application. This ignored file is loaded by `composer test:database` and full `composer test` runs explicitly selecting an external engine through the process variable `BUILDER_TEST_DRIVER`. Without that selector, `composer test` remains SQLite-only. The runner loads only this dedicated file (or CI environment variables), never the application's `.env`.

Create a disposable database (for example, `sirius_builder_test`) on each local engine and a dedicated test user allowed to create/drop test tables and indexes, insert test records, and inspect metadata in that database. PostgreSQL tests also need permission to create a test schema. Use distinct server ports when MySQL and MariaDB coexist. Set the actual installed ports and credentials yourself:

```dotenv
BUILDER_TEST_PGSQL_HOST=127.0.0.1
BUILDER_TEST_PGSQL_PORT=5432
BUILDER_TEST_PGSQL_DATABASE=sirius_builder_test
BUILDER_TEST_PGSQL_USERNAME=sirius_test
BUILDER_TEST_PGSQL_PASSWORD="your-local-test-password"

BUILDER_TEST_MARIADB_HOST=127.0.0.1
BUILDER_TEST_MARIADB_PORT=3307
BUILDER_TEST_MARIADB_DATABASE=sirius_builder_test
BUILDER_TEST_MARIADB_USERNAME=sirius_test
BUILDER_TEST_MARIADB_PASSWORD="your-local-test-password"

BUILDER_TEST_MYSQL_HOST=127.0.0.1
BUILDER_TEST_MYSQL_PORT=3306
BUILDER_TEST_MYSQL_DATABASE=sirius_builder_test
BUILDER_TEST_MYSQL_USERNAME=sirius_test
BUILDER_TEST_MYSQL_PASSWORD="your-local-test-password"
```

These port numbers are examples, not detected server settings. Do not reuse application/production databases or credentials. Ensure the PHP CLI used for these tests has `pdo_pgsql`, `pdo_mysql`, and `pdo_sqlite` enabled. Do not share passwords in chat; confirm when configuration is ready. Run `composer test:database` after setup. Without a selector, all three configured engines are required for `test:database`. With `BUILDER_TEST_DRIVER`, only the selected engine is required; missing configuration fails instead of silently falling back. Loopback hosts are required and system databases are rejected. See [database inspection](DATABASE_INSPECTION.md) for metadata and test isolation details.

The package GitHub Actions `database-matrix` job provisions MySQL 8.4, PostgreSQL 17, and MariaDB 11.4 containers with health checks and disposable CI-only credentials, then runs full `composer test` in separate SQLite/MySQL/MariaDB/PostgreSQL matrix entries. Each entry uses its selected engine for feature tests and schema acceptance tests. See the full-gate shell example in [database inspection](DATABASE_INSPECTION.md#run-the-full-gate-for-one-connection). It requires no local `.env` or repository secrets. Service configuration follows the [GitHub PostgreSQL service guide](https://docs.github.com/en/actions/tutorials/use-containerized-services/create-postgresql-service-containers) and [MariaDB healthcheck documentation](https://mariadb.com/docs/server/server-management/automated-mariadb-deployment-and-administration/docker-and-mariadb/using-healthcheck-sh).

## Phase 4 field services

Field inference and validation are available as library services; see [field configuration](FIELD_CONFIGURATION.md) for APIs, supported mappings, canonical values, and customization requirements. The interactive wizard remains Phase 5. Full database-matrix runs cover validation queries on the selected engine. External test connections now enable `prefix_indexes` as well as per-test table prefixes so foreign-key names cannot collide with other tests or existing objects.
