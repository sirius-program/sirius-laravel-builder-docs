# Sirius Laravel Builder Documentation

English documentation for the [Sirius Laravel Builder package](https://github.com/sirius-program/sirius-laravel-builder).

- [Implementation plan and phase checklist](IMPLEMENTATION_PLAN.md)
- [Architecture and Phase 0 testing foundation](ARCHITECTURE.md)

The current milestone establishes the package skeleton and executable architecture baseline. The CRUD command, publishable stubs, Sirius UI integration, and working documentation demos are scheduled in later phases; they are not available yet.

## Documentation application development

Use the existing Laravel application setup and installed dependency requirements. The current local application runs on PHP 8.5; its Pest 5 development stack requires PHP 8.4 or later. This is separate from the package's PHP 8.3 runtime target.

```shell
composer install
npm ci
npm run build
composer test
```

`composer test` clears the configuration cache, checks formatting, runs static analysis, and executes the application's tests. After every implementation phase, update the relevant documentation and README files and run this command in every changed project.
