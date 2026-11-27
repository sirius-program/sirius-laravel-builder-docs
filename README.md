# Sirius Builder for Laravel Documentation

- [Call generated actions and configure policies](ACTIONS.md)
- [Generate files, resolve conflicts, and recover](GENERATION.md)
- [Publish and customize generation stubs](STUBS.md)
- [CSS formatting rules](CSS_STYLE.md)
- [Implementation plan and phase checklist](IMPLEMENTATION_PLAN.md)
- [Setup, configuration, Sirius UI, and public documentation](SETUP.md)
- [Resource definitions, model/enum discovery, and naming](DISCOVERY.md)
- [Field configuration, validation, and canonical values](FIELD_CONFIGURATION.md)
- [Database inspection and real-engine tests](DATABASE_INSPECTION.md)
- [Architecture and Phase 0 testing foundation](ARCHITECTURE.md)

Phases 0–4 establish the architecture baseline, `make:crud --check`, publishable configuration, Sirius UI integration, and a public documentation homepage with anonymous light/dark theme preferences. Phase 2 adds typed resource definitions, recursive model/enum discovery, SoftDeletes detection, naming defaults, and collision checks through library services. Phase 3 adds read-only schema inspection for SQLite, MySQL/MariaDB, and PostgreSQL, typed metadata, and opt-in real-engine tests. CI runs full `composer test` separately for SQLite, MySQL, MariaDB, and PostgreSQL; feature tests use the selected connection. Table suggestions now accept the selected model's Eloquent-resolved table, including custom table configuration. Phase 4 adds field suggestions, structured Create/Update validation, enum/select/password metadata, and canonical-value normalization. The wizard detects existing Diglactic breadcrumb files and previews their trails. The wizard writes reviewed scaffolds and manages web routes and breadcrumbs. Working CRUD demos follow in later phases. Documentation authentication and account screens have been removed. Pages under `/docs` use the Sirius UI documentation shell with a fixed desktop sidebar, centrally maintained breadcrumbs, centered content, a Back to top link, responsive On this page navigation, mobile slideover navigation, copyable code examples, and Livewire SPA-like internal navigation.

## Documentation application development

Console messages now use Laravel's Termwind-backed components. The [interactive wizard](WIZARD.md) uses Laravel Prompts with Laravel's built-in native Windows fallback; see [console messages and Windows support](SETUP.md#console-messages-and-windows-support). CI includes a native Windows SQLite full-gate job alongside the Linux database matrix.

Use the existing Laravel application setup and installed dependency requirements. The current local application runs on PHP 8.5; its Pest 5 development stack requires PHP 8.4 or later. This is separate from the package's PHP 8.3 runtime target.

```shell
composer install
npm ci
npm run build
composer test
npx playwright install chromium
composer test:browser
```

`composer test` clears the configuration cache, checks formatting, runs static analysis and Rector checks, and executes non-browser tests. `composer test:browser` separately checks navigation, themes, and responsive behavior using built assets. After every implementation phase, update the relevant documentation and README files and run the required gates in every changed project.
