# Architecture and Phase 0 foundation

Phase 0 establishes executable conventions before CRUD features are implemented. At that milestone the only production package class was `Sirius\LaravelBuilder\SiriusLaravelBuilderServiceProvider`. It extends Laravel's service provider and is listed in Composer's discovery metadata. Phase 1 now registers `make:crud` with prerequisite checks; actual generation remains unavailable.

## Package structure

| Path | Responsibility |
| --- | --- |
| `src/` | Production package classes; PSR-4 namespace `Sirius\LaravelBuilder` |
| `tests/Architecture/` | Pest architecture expectations and source/view boundary checks |
| `tests/Unit/` | Deliberate negative witnesses and allowed architectural cases |
| `tests/Feature/` | Provider boot, isolated environment, and representative action/Livewire behavior |
| `tests/Fixtures/Application/` | Handwritten consumer example under `App\Actions`, `App\Livewire`, `App\Models`, and Blade views |
| `tests/Fixtures/Package/` | Representative Definitions and Inference classes, avoiding empty architecture targets |
| `tests/Support/` | Test-only AST boundary checker |
| `.phpunit.cache/apps/` | Disposable per-test application artifacts; cleaned up after tests |

The fixture Create action is a small example used to verify the architecture. It is not a production CRUD implementation or generated output. Its simple native Blade form exercises Livewire delegation independently of the Sirius UI integration installed in Phase 1.

## Boundaries

Commands coordinate Discovery, Schema, Inference, Definitions, and Generators. Pure definitions and inference must not perform I/O or resolve framework services. The package must not import concrete consumer `App` classes. Those future production directories will be checked recursively as they appear; representative pure-layer fixtures are checked now.

Generated actions belong under `App\Actions` and expose one public business method, `handle()`. Constructors are allowed. They authorize and validate even when called directly, explicitly assign permitted attributes, and persist through Eloquent. Livewire manages form state and delegates writes to actions. Its scoped read queries are allowed. Blade does not query the database.

The package generator will only read schema metadata and write application files. Phase 0 does not yet have a generator to test; its boot test establishes that registering the provider does not mutate records. Phase 1 resolves the provider using Laravel package-manifest discovery in the isolated fixture and checks the command without record queries. Later generator phases must add direct no-record-write assertions around actual command execution.

## Executable architecture checks

Pest architecture expectations enforce strict typing, the action `handle()` entry point, action independence from Livewire, and the component base class. Test-only PHP-Parser checks inspect actual files for forbidden dependencies, missing method types, extra public action methods, debugging termination, and direct persistence calls in components. Blade is compiled before inspection so `@php` cannot conceal queries from the checker.

Negative witnesses deliberately introduce DB access into definitions, filesystem access into inference, direct writes into components, consumer coupling into package code, additional action entry points, and queries into Blade. Positive witnesses permit scoped reads and action delegation. These tests establish that the checks detect violations instead of merely passing against empty namespaces.

The checker is a conservative static guardrail, not a complete PHP effect system. It does not resolve every dynamic alias, delegated service side effect, or SQL query string. Dynamic component calls require explicit review, and a method named like an Eloquent mutation is rejected even on another object. Do not treat it as proof of complete runtime isolation. Behavioral tests and review remain necessary; extend the rules when real generation and schema adapters are introduced.

## Isolation and behavior tests

The Testbench base class disables environment-file loading, replaces all database connections with one SQLite `:memory:` connection, uses array cache/session stores, and blocks stray HTTP requests. Each feature test receives a random private application-artifact directory beneath the package cache. Cleanup checks that the resolved target is inside that cache before deleting it.

Tests exercise the representative action against real in-memory records: authorization failures and invalid input write nothing; valid calls persist only allowed data; Livewire submission delegates to the action, resets state, and surfaces validation errors. Test schema creation is confined to this isolated connection.

## Development checks

From the package directory:

```shell
composer install
php vendor/bin/pest tests/Architecture
php vendor/bin/pest tests/Unit
php vendor/bin/pest tests/Feature
composer test
```

The full command runs Pint, Rector dry-run, Larastan/PHPStan at level 6 with PHP 8.3 syntax targeting, and all Pest suites. Static analysis includes production code, the AST checker, and representative application/package classes. The portable PHPStan view configuration resolves the fixture view directory for Windows and Linux.

The package uses Pest 3 / PHPUnit 11 so tests can execute on PHP 8.3. Composer's platform target is PHP 8.3; Testbench 10 is used with Laravel 12, and Testbench 11 with Laravel 13. The package's local lock file is ignored because framework compatibility jobs must resolve independently. The docs app retains its own existing dependency lock and Pest 5 stack.

On PHP 8.5, the current Pest 3 architecture dependency can emit `ReflectionProperty::setAccessible()` deprecations. They originate in the installed test dependency, not generated application code. Run the baseline suite on PHP 8.3 for a deprecation-free result; do not edit vendor files or suppress source checks to hide them.

## Phase 2 discovery and definitions

`Definitions` now holds immutable resource/model/enum/field/policy/route/artifact data and operation/layout enums. The boundary checker rejects dependencies on Discovery, Schema, and Support from pure layers, preventing delegated I/O. `Support/ResourceNamer` handles resource-name inflection without database or filesystem access and accepts the selected model's resolved table as an explicit input; it does not independently infer table names.

`Discovery/ClassDiscovery` resolves PSR-4 classes recursively with deterministic ordering, checked source paths, and clear autoload errors. Model discovery reflects concrete Eloquent subclasses and recursively detects SoftDeletes without constructing models. Enum discovery preserves fully qualified names and backing types. Architecture checks permit exactly Eloquent Model/SoftDeletes metadata references in Discovery, while continuing to reject database access, query construction, and persistence. Positive and negative witnesses cover this deliberate refinement.

Collision checks report overlapping identities and planned/existing artifact paths/classes without writing files. Representative discovery fixtures live in `tests/Fixtures/Discovery` and are included in strict-type checks and static analysis. The constructor-guard fixture and empty query log provide behavioral evidence of metadata-only discovery. Trusted autoload code can still have arbitrary top-level effects; this is not a PHP sandbox.

See [discovery and naming](DISCOVERY.md) for the API and remaining phase boundaries.

## Phase boundaries and next work

Phase 1 adds package services/configuration, read-only prerequisite checks, Sirius UI integration, and its standalone-managed AI skill in both projects. The docs app now has public introduction/documentation pages, anonymous theme preferences, and browser checks; its authentication routes and screens have been removed. Commands orchestrate `Support/Preflight`; `Schema/ConnectionCheck` handles database connectivity. Database adapters, the interactive wizard, generated actions/views/tests, generated-resource browser tests, and publishing stubs follow their respective plan phases. See [setup documentation](SETUP.md).

Continue to update docs and READMEs and run `composer test` in every changed project after each phase. Record actual tested versions and results in the implementation plan. Full database, browser, and minimum-dependency release claims require their later acceptance checks.

References: [Pest architecture testing](https://pestphp.com/docs/arch-testing), [Orchestra Testbench](https://github.com/orchestral/testbench).
