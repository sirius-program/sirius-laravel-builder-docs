# Sirius Builder for Laravel Documentation

- [CSS formatting rules](CSS_STYLE.md)
- [Implementation plan and phase checklist](IMPLEMENTATION_PLAN.md)
- [Setup, configuration, Sirius UI, and public documentation](SETUP.md)
- [Resource definitions, model/enum discovery, and naming](DISCOVERY.md)
- [Database inspection and real-engine tests](DATABASE_INSPECTION.md)
- [Architecture and Phase 0 testing foundation](ARCHITECTURE.md)

Phases 0–3 establish the architecture baseline, `make:crud --check`, publishable configuration, Sirius UI integration, and a public documentation homepage with anonymous light/dark theme preferences. Phase 2 adds typed resource definitions, recursive model/enum discovery, SoftDeletes detection, naming defaults, and collision checks through library services. Phase 3 adds read-only schema inspection for SQLite, MySQL/MariaDB, and PostgreSQL, typed metadata, and opt-in real-engine tests. CI runs full `composer test` separately for SQLite, MySQL, MariaDB, and PostgreSQL; feature tests use the selected connection. Table suggestions now accept the selected model's Eloquent-resolved table, including custom table configuration. Automatic integration with existing consumer Diglactic breadcrumb routes is planned for the wizard/stub/generator phases. CRUD generation, the interactive wizard, publishable stubs, and working resource demos remain planned features. Documentation authentication and account screens have been removed. Pages under `/docs` use the Sirius UI documentation shell with a fixed desktop sidebar, centrally maintained breadcrumbs, centered content, a Back to top link, responsive On this page navigation, mobile slideover navigation, copyable code examples, and Livewire SPA-like internal navigation.

## Documentation application development

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
