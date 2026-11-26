# Sirius Builder for Laravel — Phased Implementation Plan

Status: Phases 0–4 completed; Phases 5–14 not started.

Decision date: November 20, 2026.

Documentation scope revised: November 21, 2026 — use Sirius UI's palette and theme, remove documentation-app authentication, and build a public package-introduction homepage. This supersedes the earlier decision to retain docs login and require authenticated demonstrations. It does not change authentication/policy defaults in generated consumer applications.

This plan records the requirements and recommendations accepted during the design interview. The owner subsequently authorized Phases 0, 1, 2, 3, and 4, whose completed checkboxes and evidence appear below. Later phases require an instruction to proceed; completion of a phase does not authorize the next automatically.

## 1. Outcome and project boundaries

Build a Laravel package that provides `php artisan make:crud`. It discovers existing Eloquent models, inspects their database schemas, guides the developer through resource configuration, and generates action-based Livewire CRUD using Sirius UI.

| Project | Responsibility |
| --- | --- |
| `D:\Projects\sirius-laravel-builder` | Composer package, generators, publishable stubs, package tests, generated-application fixtures |
| `D:\Projects\sirius-laravel-builder-docs` | English implementation plan, public package-introduction homepage, English documentation, Sirius UI theme, anonymous isolated working demonstrations |
| `D:\Projects\sirius-ui` | Read-only local reference for the upstream UI dependency; changes are outside this plan |

Upstream UI: <https://github.com/sirius-program/sirius-ui>.

The package directory was empty when inspected. The docs project began as a Laravel/Livewire application with authentication, Flux-based screens, and Pest tests. Redesign it as a public documentation website: replace the starter homepage with a package introduction, migrate its presentation to Sirius UI, and remove authentication/account features and their navigation. Preserve unrelated documentation behavior and adapt tests to the revised public-site requirements.

### Compatibility

The owner corrected the original PHP requirement and requested compatibility aligned with Sirius UI. The agreed initial target is:

| Dependency | Target |
| --- | --- |
| PHP | `^8.3` |
| Laravel / required Illuminate components | `^12.61.1 || ^13.12.0` |
| Livewire | `^4.0` |
| UI | Required `sirius/ui` dependency, using an actual released version compatible with the matrix above |
| Databases | MySQL, MariaDB, PostgreSQL, SQLite |
| Tests | Pest, with compatible framework/Testbench/browser-plugin versions selected per supported runtime |

These bounds come from the inspected Sirius UI manifest. Recheck the installed release before implementation and do not claim support for combinations Composer cannot resolve. The docs app may remain on Laravel 13 and PHP 8.5. Its currently installed Pest 5 requires PHP 8.4+, so its development dependencies must not accidentally raise the package's PHP 8.3 runtime minimum. Resolve a PHP-8.3-compatible package test stack rather than copying the docs dependency set.

### Initial-release scope

- Index and Read/detail are always generated; Read is not an operation-picker option.
- Optional operations: Create, Update, Delete; all selected by default.
- Models using SoftDeletes additionally offer Restore and Permanent Delete, also selected by default.
- Ask once per resource whether Create, Read/detail, and Update use separate pages or dialogs on Index.
- Delete, Restore, and Permanent Delete use confirmation dialogs in either layout.
- Generated writes belong to action classes under `App\Actions`.
- `make:crud` only reads schema metadata and writes application files. It does not change records, run migrations, or alter schemas.
- Generated host-app Pest feature tests are standard output; host-app browser tests are optional. Package browser coverage is required for both layouts.
- Stubs are publishable and overridable.
- JSON configuration export/import, dry runs, and noninteractive generation are included.
- Existing models, factories, policies, and unrelated route code are preserved.

Relationship editors, many-to-many synchronization, nested forms, uploads, richtext image uploads, bulk operations, plain Blade alternatives, and composite-primary-key support are deferred. Single-value selects and scalar foreign-key columns are included. Detect unsupported composite keys before writing files.

## 2. Working rules and phase completion gate

Apply these rules to every phase, including Phase 0:

1. Do not start implementation until instructed by the owner. Work within the phase scope authorized in that instruction.
2. Read applicable `AGENTS.md`, `.ai/rules` if present, and relevant installed skills. Use installed dependency versions when checking APIs.
3. Keep architecture tests executable and passing. Update a rule deliberately when an agreed architectural change requires it; do not weaken it merely to accept a failing implementation.
4. Test observable behavior, including meaningful failure modes. Use real test databases and generated application code where appropriate; snapshots alone do not prove CRUD works.
5. Add or update English feature documentation in the docs project after every phase. Update the appropriate README files as well.
6. Keep the package README minimal: identify the package and direct readers to detailed usage in the docs. Put tutorials and command references in the docs project.
7. Run `composer test` in every project changed during the phase, including the docs project when documentation changed. A package phase normally changes both projects, so normally run both suites.
8. Run relevant real-browser tests when UI behavior changes, plus the frontend build when assets change. If browser checks are separate from `composer test`, record both commands and results.
9. Run formatting/static-analysis checks required by each project. The existing docs `composer test` already includes Pint and PHPStan checks. Follow its PHP formatting instructions when PHP files change.
10. Do not mark a phase complete when a required check fails, cannot run, or exposes unresolved behavior. Record the reason and remaining work. Do not report unexecuted checks as passing.
11. Update this plan's checkboxes and evidence log only after the corresponding work is verified. Record the changed projects, documentation pages, test outcomes, and outstanding limitations.

During implementation, add these durable rules to the package's `AGENTS.md` and carefully extend the existing docs instructions without replacing their current guidance. Installing the Sirius UI AI skill in both projects is a required implementation task, not work performed by this planning document.

## 3. Agreed architecture

### Package boundaries

Use the proposed Composer identity `sirius/laravel-builder` and namespace `Sirius\LaravelBuilder`, subject to confirming package-name availability during setup.

| Area | Responsibility | Boundary |
| --- | --- | --- |
| `src/Commands` | Command registration and wizard orchestration | No templates, schema SQL, or application-record writes |
| `src/Discovery` | Recursive model and enum discovery | Return metadata; no generated-file writes |
| `src/Schema` | Connection-aware schema inspection and normalization | Read metadata only; no schema or record mutation |
| `src/Definitions` | Typed resource, field, operation, route, and artifact definitions | No prompting, I/O, or persistence |
| `src/Inference` | Field candidates and validation recommendations | Consume normalized definitions; no prompting or I/O |
| `src/Generators` | Render artifacts and plan/apply filesystem changes | Consume validated definitions; no record writes |
| `stubs` | Shipped generation templates | No wizard logic; user overrides have documented precedence |

Introduce contracts only at real replaceable boundaries, such as database adapters or artifact writing. Do not add a repository layer over Eloquent or a speculative extension framework.

### Generated application boundaries

Example for `App\Models\Admin\Category`:

| Artifact | Default location or identity |
| --- | --- |
| Resource | `Admin\Category` |
| Livewire classes | `App\Livewire\Admin\Category` |
| Write actions | `App\Actions\Admin\Category` |
| Blade views | `resources/views/livewire/admin/category` |
| Feature tests | `tests/Feature/Crud/Admin/Category` |
| URL prefix | `/admin/categories` |
| Route-name prefix | `admin.categories.` |

Action classes represent one operation each and expose one public business entry point, `handle()`; constructors are exempt from that entry-point rule. Generate only the selected write actions. Actions authorize, validate, normalize input, explicitly assign the generated writable-field allowlist, and persist through Eloquent. Use transactions on the model's connection for operations that need atomicity.

Livewire components manage state, display errors, and call actions. They do not write records directly. Read components and the concrete Sirius Table subclass may perform scoped Eloquent reads. Blade views do not query or write the database. Reuse validation definitions where needed without allowing direct action calls to bypass validation.

Do not rely on `$fillable` changes: explicitly assign only selected, validated attributes. Exclude confirmation fields and unexpected keys. Preserve model casts, mutators, events, global scopes, and custom connection behavior. Never unguard a model globally.

### Architecture tests before feature phases

Protect meaningful structural conventions with Pest architecture tests and complement them with behavioral tests where syntax checks cannot prove a rule:

- Package definitions and inference remain independent of UI, commands, filesystem access, and database access.
- Commands delegate discovery, inspection, rendering, and writing.
- Package classes do not depend on a consumer's concrete `App` classes at compile time.
- Generated actions live in `App\Actions`, have the agreed entry point, and do not depend on Livewire or view rendering.
- Generated components extend the appropriate Livewire/Sirius base class and delegate mutations.
- Production code contains no debugging termination calls.
- Generated views contain no database queries or persistence code.
- PHP classes follow agreed type declarations, namespaces, and naming conventions.
- Behavioral tests prove that generator execution does not modify database records and that unauthorized or invalid action calls do not write records.

Run architecture assertions against both the package and representative generated applications. Avoid empty-directory assertions that pass without inspecting any generated output.

## 4. Command and generation contract

### Wizard sequence

1. Check dependency availability, app paths, route-file accessibility, and database connectivity.
2. Recursively scan the application's `Models` directory for concrete Eloquent models. Display names relative to it, such as `User`, `Admin\Category`, and `Client\Product`. Handle duplicates by their relative namespace.
3. Ask for the resource name. Default to the selected model's relative namespace and name; preserve namespace segments unless the developer changes them.
4. Ask for the table name. Default to the selected Eloquent model's `getTable()` result. This delegates conventional naming to Laravel and respects custom `$table` values and overridden table resolution. Resource renaming must not change the default; do not add the connection table prefix manually.
5. Use the model's connection for inspection. If the entered table disagrees with the model's resolved table, explain the mismatch and stop final generation until it is corrected. Do not silently rewrite the model.
6. Inspect columns and relevant constraints through normalized, connection-aware schema inspection. Prefer Laravel schema APIs; use driver-specific metadata queries where necessary.
7. Iterate over columns in database order and configure each included field as specified below.
8. Ask once for separate pages or Index dialogs.
9. Offer optional operations, defaulting all available choices on. Always retain Index and Read/detail.
10. Configure route identity, authentication/policy handling, and optional generated browser tests. Default to authenticated routes and policy enforcement; public access requires an explicit developer choice and still respects the selected policy behavior.
11. Review model, connection, table, resource, fields, labels, validation, operations, layout, policies, files, routes, detected breadcrumb integration/trails/target files, conflicts, and manual customization requirements.
12. Offer **Edit a section**, **Restart**, **Generate**, and **Cancel**. Recompute dependent defaults and validation after edits; do not retain stale derived routes, options, or rules.
13. Validate the complete definition, stage artifacts, recheck conflicts, and apply changes. Report written files, routes, test commands, and required customizations.

Cancellation and validation failures leave application files unchanged. No database record writes occur at any point in this sequence.

### Column inclusion, labels, and type precedence

Ask whether to include each column. Default to true except for `id`, names ending in `_at`, or database timestamp types, which default to false. Skipped columns receive no further field questions. Database-generated columns cannot become writable even when included for display.

For each included field, ask for its label, defaulting to the title case of the column name. Apply `_id` matching before generic integer/string mappings, while preserving genuine enum/date and database-generated metadata. Normalize driver type aliases before matching.

| Column category | Offered field types or behavior |
| --- | --- |
| Name ending in `_id` | select, number, text; validate against the actual underlying type |
| varchar / character string | text, email, password, url, phone |
| integer family | number, boolean, currency |
| text family | textarea, richtext |
| timestamp | datetime |
| datetime / date / time | corresponding type |
| native enum or reliably introspected enum equivalent | discovered PHP enum choices or select |
| boolean | boolean control |
| decimal / numeric | number or currency, preserving precision and scale |
| UUID / ULID when reliably identifiable | text with matching validation; do not infer solely from arbitrary string length |
| JSON | textarea, JSON validation, and explicit cast-aware serialization |
| unsupported / binary | explain the limitation; skip or choose a supported mapping that can round-trip safely |

No silent lossy mapping is acceptable. If metadata cannot identify a database enum or another specialized type reliably, explain the limitation and offer explicit configuration rather than inventing allowed values.

### Special field contracts

- **Password:** generate the original field and `{column}_confirmation`; ask for the confirmation label, defaulting to `Confirm {label}`. Persist the chosen label in the field plan. Validate confirmation; never persist it. Hash passwords while respecting an existing hashed cast/mutator to avoid double hashing. Never prefill, list, or display stored password hashes. Blank Update passwords preserve the existing value; nonblank passwords require confirmation.
- **Select:** ask exactly one additional field-source question: the endpoint. A blank answer generates `options=[]` and omits `search-url` (or uses `null`); an empty-string `search-url` is invalid in the inspected Sirius release. Do not invent an endpoint or relationship.
- **Remote select:** follow Sirius's same-origin GET contract: `q`/`page` for searching and `values[]` for resolving initial selections. Expected JSON is `{"options":[{"value":"1","label":"Example"}],"hasMore":false}`. Document endpoint authentication, authorization, record scoping, and initial-label resolution. The generator does not contact the endpoint during scaffolding.
- **PHP enum:** recursively discover concrete enum definitions under `App\Enums`. Map backed cases to backing values and pure cases to case names, with title-cased case labels. Generate lists of `{value, label}` records, not associative maps. Reject invalid/empty/duplicate option IDs and incompatible database values with actionable messages. Handle existing enum casts correctly.
- **Richtext:** use Sirius Richtext and a maintained server-side HTML sanitizer with an explicit allowlist. Sanitize before persistence and before rendering legacy/untrusted stored HTML. Omit image/upload controls in version one.
- **Currency:** preserve canonical decimal strings and schema precision/scale; do not convert money to floating-point values.
- **Date/time:** use Sirius canonical formats and explicitly document the application timezone conversion policy. Do not silently treat local datetime strings as UTC.
- **Phone:** use Sirius canonical phone values and applicable server validation, not client formatting alone.
- **JSON:** serialize/decode according to the existing model cast; prevent double encoding and expose invalid input as validation errors.

### Validation contract

Infer a baseline from normalized schema and selected field type: required/nullable/default behavior, string length, numeric bounds, decimal precision, booleans, temporal formats, JSON, enum membership, and usable uniqueness/foreign-key constraints.

Show the suggested pipe-separated rule string and let the developer edit it. Support shared rules plus Create and Update overrides. Internally preserve structured rules where Laravel needs objects, dynamic record identity, or enum handling; do not flatten those into unsafe strings. Detect malformed pipe input and explain escaping/delimiter limitations, particularly regex rules. Never evaluate user-entered PHP.

Unique Update validation must ignore only the trusted, server-resolved current model key, never a client-supplied ignore identifier. Use the actual table, key, and connection. Do not infer a single-column uniqueness rule from a composite index. Complex constraints need explicit configuration or a documented manual customization.

For omitted non-null columns without database defaults, identify whether the existing model supplies values. If this cannot be determined safely, report the required customization before generation rather than promising a working Create action. Do not generate arbitrary defaults to bypass constraints.

### Routes and authorization

Separate-page mode generates these GET routes as applicable:

| Route name suffix | Path suffix | Condition |
| --- | --- | --- |
| `index` | `/` | Always |
| `create` | `/create` | Create selected |
| `show` | `/{record}` | Always |
| `edit` | `/{record}/edit` | Update selected |

Dialog mode generates the Index GET route and renders Read/detail, Create, and Update inside it. The mandatory Read capability does not imply a separate show URL in dialog mode. Write operations run through Livewire actions, not destructive GET endpoints. Review displays the actual routes for the selected layout. Place static routes before dynamic record routes and honor custom route keys.

Default to `auth` middleware and policy checks. Index uses `viewAny`; detail uses `view`; mutations use the corresponding abilities, including `restore` and `forceDelete`. Missing policies are detected before writing. Offer a new deny-by-default policy scaffold with instructions; never overwrite an existing policy implicitly. If a protected resource lacks its required policy and scaffolding is declined, report the blocker before generation.

Action authorization is enforced even outside Livewire. Hidden buttons and selected IDs are not authorization. Respect application/global scopes and apply appropriate trashed-record resolution for restore/permanent delete. The trash filter supports reviewing soft-deleted records even when restoration actions were deselected.

### File-writing and stub contract

- Publish stubs with a dedicated tag, proposed as `sirius-builder-stubs`, to `stubs/sirius-builder` in the consuming app.
- Resolve each template from the app override first, then the package default. Publishing without explicit force must preserve customization.
- Document template placeholders, supported contexts, versioning, and upgrade comparison; avoid unstable internal implementation objects in the public stub contract.
- Review all target paths and conflicts. Stop on collisions by default; allow explicit per-file overwrite selection. Include policy/test/stub-related output in review.
- Constrain generated paths to intended app directories, validate PHP identifiers, and reject traversal or escaped target paths. Escape labels/rules/URLs for their output context.
- Insert identifiable resource blocks in `routes/web.php`, preserving unrelated content and avoiding duplicate imports, routes, or route names.
- Use safe structural route handling; refuse unsupported existing syntax rather than damage the file. Check route names and paths against the existing application, not just the managed block.
- Stage output before commit, keep originals for rollback, and restore affected files on recoverable failures. If recovery cannot finish, retain backups and report exact recovery steps; do not claim cross-file atomicity that the filesystem cannot guarantee.
- Recheck file state immediately before applying a reviewed plan so concurrent/manual edits are not overwritten unknowingly.

## 5. Implementation phases

Each phase depends on the preceding phases unless stated otherwise. The documentation and verification checkboxes in each phase include the full completion gate in Section 2.

### Optional consumer breadcrumb integration

When `diglactic/laravel-breadcrumbs` is installed and enabled and the consumer has existing breadcrumb route definitions, automatically plan matching CRUD breadcrumbs. This is optional integration; do not install the dependency or change consumer configuration automatically. Resolve `breadcrumbs.files` using the package configuration, including its default `routes/breadcrumbs.php`, a custom string path, or an array of paths. Account for the package's deferred provider when obtaining configuration. Disabled configuration, absent dependency, or a missing default file skips integration with an explicit review status; invalid configured files produce actionable diagnostics. Do not execute route files again merely to inspect them.

Only use existing PHP targets contained within the consumer project, with safe path/symlink checks. If multiple configured files exist, require a target choice (or explicit noninteractive configuration) instead of guessing. Never invent a parent trail such as `home`: use a verified existing parent when configured, otherwise let the resource Index be the root.

Use the exact generated named GET routes as breadcrumb names. Page mode registers Index and mandatory Show/detail, plus Create/Edit only when selected. Detail/Edit trails accept the same server-resolved record parameters as their routes and honor custom route keys; avoid exposing sensitive model attributes in labels. Dialog mode registers only Index, because dialog operations have no separate URL. Delete, Restore, and Permanent Delete never receive breadcrumb routes. Parent relationships must resolve without missing definitions or cycles.

Preview every breadcrumb registration and target before generation. Use managed, idempotent blocks and the same conflict/overwrite/atomic rollback policy as `web.php`. Existing nonmanaged definitions are conflicts, not silent overwrite candidates. Publish the breadcrumb stub alongside other stubs. Generated views reuse the consumer's existing breadcrumb rendering/layout where present; avoid duplicate trails and do not rewrite host layouts automatically. Later generated UI acceptance must verify page trails and dialog behavior with and without the integration.

### Phase 0 — Prerequisite setup and executable architecture baseline

This is the pre-feature gate requested by the owner. Bootstrap only what is necessary to run the architecture checks; complete it before Phase 1 feature work.

- [x] Record dependency versions and establish the package Composer skeleton, PSR-4 namespace, service-provider entry, Testbench, and Pest harness.
- [x] Resolve test-tool compatibility for PHP 8.3 and both supported Laravel majors; keep the docs app's existing working toolchain separate.
- [x] Define `composer test` for the package to run the required architecture, unit/feature, formatting, and static-analysis checks.
- [x] Add representative application fixtures and minimal generated-class samples so architecture assertions are non-vacuous.
- [x] Implement the architecture baseline in Section 3, including action boundaries and prohibited direct component writes; extend it to actual generated output and the Sirius Table base when those exist.
- [x] Add a test-isolation baseline: disposable app roots and databases, deterministic fixtures, and no access to production data.
- [x] Document the architectural decisions and add durable phase rules to both projects' AI instructions.
- [x] Add/update architecture documentation and both READMEs; keep the package README limited to its docs pointer.
- [x] Run `composer test` in both changed projects and record results.

Acceptance: architecture tests execute against real representative classes, fail on deliberate boundary violations during verification, and pass after the valid structure is restored.

### Phase 1 — Package integration and Sirius UI foundation

#### 1.1 Package services and configuration

- [x] Register `make:crud`, package configuration, publishing hooks, and dependency discovery using supported Laravel APIs.
- [x] Define configurable discovery paths, stub paths, and route defaults while keeping the agreed defaults intact.
- [x] Add clear diagnostics for missing dependencies, invalid app structure, and unavailable database connections.
- [x] Test provider discovery, command registration, configuration resolution, and preflight failures.

#### 1.2 Sirius UI and AI skills

- [x] Install a compatible Sirius UI release in the package dependency graph and docs app.
- [x] Install the complete Sirius UI AI Agent Skill in both projects under `.agents/skills/sirius-ui-development`.
- [x] Select one skill-installation owner per project. Use Boost or the standalone installer, not both for the same directory.
- [x] For the package root, use the supported installer service through its bootstrapped PHP/Testbench context if needed; do not assume an application `artisan` file exists.
- [x] Verify the installed skill includes its referenced files and document refresh behavior on upgrades.
- [x] Establish Sirius asset loading without duplicating Livewire/Alpine initialization.

#### 1.3 Public documentation redesign

- [x] Replace the starter palette and theme with Sirius UI's light/dark tokens, typography, surfaces, borders, and semantic component colors; verify buttons, inputs, navigation, and code examples rather than changing only the primary color.
- [x] Remove documentation-app authentication: login, registration, logout, password reset, email verification, two-factor authentication, and account/profile/password settings screens and routes, where present.
- [x] Remove login/register/account links and authentication middleware or redirects from public documentation routes. Keep theme preferences available without an account.
- [x] Audit authentication-specific providers, configuration, frontend code, and dependencies such as Fortify; remove unused integration after confirming it is no longer referenced. Do not change the generated application's authentication/policy defaults.
- [x] Build a public homepage at `/` introducing Sirius Builder for Laravel: package purpose, supported stack, action-based CRUD output, the `php artisan make:crud` workflow, page/dialog layout options, and links to Getting Started, documentation, and the source repository.
- [x] Clearly distinguish available functionality from planned features while development is incomplete; do not present the homepage as a completed release before the generator exists.
- [x] Add feature tests for public homepage/docs access and removal of obsolete auth/account routes; update existing authentication tests to reflect the new requirements.
- [x] Add browser checks for homepage navigation, responsive presentation, Sirius light/dark themes, and the absence of login/account controls or login redirects.
- [x] Add/update setup documentation and READMEs; run both projects' `composer test`, relevant browser checks, and the docs asset build.

Acceptance: a consuming fixture discovers the command, both projects have usable Sirius skills, and the docs site has a public package-introduction homepage with Sirius styling. Documentation-app authentication and account screens/routes are removed, and homepage/docs access does not require login.

### Phase 2 — Resource definitions and recursive discovery

- [x] Implement typed definitions for resource identity, fields, operation selection, layout, policies, routes, and artifacts.
- [x] Implement recursive concrete-model discovery with stable relative names and deterministic ordering.
- [x] Discover enums recursively, preserving fully qualified names for duplicate basenames.
- [x] Detect SoftDeletes through inherited/recursive trait use.
- [x] Derive default resource identity, caller-supplied Eloquent table suggestion, namespaces, view paths, URLs, and route names.
- [x] Validate renamed resources and detect namespace/path collisions before rendering.
- [x] Cover empty folders, nested models, abstract/non-model classes, duplicate names, invalid input, and discovery errors.
- [x] Add/update discovery and naming documentation and READMEs; run `composer test` in both changed projects.

Acceptance: the sample models display as `User`, `Admin\Category`, and `Client\Product`, and nested names remain distinct throughout output planning.

### Phase 3 — Database inspection and normalized schema

- [x] Inspect the selected model's connection and actual table; use `getTable()` as the table prompt default and detect mismatch. Test conventional, custom `$table`, overridden `getTable()`, and renamed-resource cases.
- [x] Normalize columns, nullability, defaults, length, precision/scale, generated status, primary keys, and relevant indexes/foreign keys.
- [x] Implement and verify MySQL/MariaDB, PostgreSQL, and SQLite adapters or compatible framework-backed inspection paths.
- [x] Normalize driver aliases and inspect enum values where the engine exposes reliable metadata.
- [x] Handle table prefixes and schema-qualified names without unsafe identifier interpolation.
- [x] Reject unsupported composite keys and explain ambiguous or unsupported metadata.
- [x] Prove inspection does not alter records/schema; test missing tables, inaccessible connections, custom keys, and custom model connections.
- [x] Run real-engine inspection tests, not SQLite substitutes for all drivers.
- [x] Add/update database compatibility documentation and READMEs; run `composer test` in both changed projects.

Acceptance: equivalent schemas produce equivalent normalized field metadata, and driver differences are explicitly represented and tested.

### Phase 4 — Field configuration and validation inference

#### 4.1 Inclusion and field selection

- [x] Implement inclusion defaults, title-case labels, ordered type candidates, and `_id` precedence.
- [x] Implement password confirmation metadata with a configurable label (default `Confirm {label}`), endpoint-backed select configuration, and enum-to-option mapping.
- [x] Implement supported additional types and explicit unsupported-type handling.
- [x] Keep generated columns read-only and identify omitted required data that needs application customization.

#### 4.2 Validation and normalization

- [x] Infer schema-aware baseline validation and integrate selected-field semantics.
- [x] Support pipe-based edits, shared/Create/Update rules, and structured dynamic constraints.
- [x] Handle nullable/default values, trusted Update uniqueness, foreign keys, enum compatibility, and decimal precision.
- [x] Define canonical conversions for select IDs, booleans, currency, dates, phone values, JSON, and existing casts.
- [x] Cover precedence, boundary values, malformed rules, password confirmation, incompatible enums, and unsupported mappings with focused tests.
- [x] Add/update field mapping and validation documentation and READMEs; run `composer test` in both changed projects.

Acceptance: every supported schema/field combination has a deterministic suggestion and can be edited without losing required dynamic validation behavior.

### Phase 5 — Interactive wizard and review

- [ ] Detect optional Diglactic Laravel Breadcrumbs integration and its configured route files without evaluating those files again; capture integration metadata in the resource definition. Cover absent package, absent route file, default/custom files, multiple files, and disabled configuration.
- [ ] Select the sole safe existing breadcrumb file automatically; for multiple candidates require an explicit target in the wizard/configuration. Include planned trails, parent relationships, target file, and integration status in review; invalidate the preview after layout or route edits.
- [ ] Implement the ordered prompt flow and searchable model/enum choices where appropriate. For each Password field, ask for its confirmation label after the main label/type, defaulting to `Confirm {label}`. Preserve custom labels during review edits; recompute the default when the main label changes if it was not customized.
- [ ] Remove Read from the picker and always include Index/detail in the definition.
- [ ] Add the resource-wide page/dialog choice and SoftDeletes-aware operation options.
- [ ] Add route/authentication/policy configuration and missing-policy diagnostics.
- [ ] Build a review screen showing exact files, route methods/paths/names, rules, source choices, conflicts, and manual requirements.
- [ ] Implement Edit a section, Restart, Generate, and Cancel with dependent-answer invalidation.
- [ ] Test default acceptance, customized answers, each review transition, cancellation, empty discoveries, and invalid entries through command tests.
- [ ] Ensure the select branch asks only the agreed endpoint question and the enum branch derives its options without an endpoint prompt.
- [ ] Add/update wizard documentation and READMEs; run `composer test` in both changed projects.

Acceptance: developers can complete, inspect, revise, or cancel a resource configuration without any application-file writes before final generation.

### Phase 6 — Publishable stubs and field rendering

#### 6.1 Template infrastructure

- [ ] Implement shipped templates, deterministic rendering, and per-template app overrides. Include the optional breadcrumb registration stub in publishing, override precedence, and placeholder documentation.
- [ ] Publish stubs under the dedicated tag and document placeholders and customization boundaries.
- [ ] Test override precedence, missing placeholders, invalid templates, repeated publishing, and preservation of customized files.

#### 6.2 Sirius field templates

- [ ] Render supported Sirius inputs, textarea, richtext, select, boolean, currency, phone, and date/time controls.
- [ ] Render password confirmation with the agreed label and no stored-value disclosure.
- [ ] Use stable IDs, labels, error associations, and Livewire bindings; honor configured Sirius namespaces.
- [ ] Render enum options as record lists and omit blank select endpoints.
- [ ] Verify canonical values, reset behavior, validation feedback, and widget lifecycle in browser fixture screens.
- [ ] Add/update stub customization and field examples and READMEs; run both projects' `composer test` and relevant browser tests.

Acceptance: a published override changes generated output, and every generated field type works in a real Livewire fixture screen.

### Phase 7 — Artifact writing and route integration

- [ ] Build an artifact manifest with intended paths, content, managed route blocks, and conflicts.
- [ ] Stage and syntax-check generated artifacts before applying them.
- [ ] Insert/update managed `web.php` blocks while preserving unrelated imports, code, and routes.
- [ ] Automatically add/update breadcrumb registrations when the reviewed integration is available, following the optional breadcrumb contract below. Preserve unrelated definitions/imports, detect existing names, and include breadcrumb files in the same staging, syntax validation, conflict review, file-state recheck, and rollback as all generated artifacts.
- [ ] Test breadcrumb integration with the real Diglactic package in an isolated fixture: default/custom/multiple files, page/dialog layouts, repeated generation, existing-name conflicts, dry run/cancel, concurrent edits, unsafe paths, parse failures, and rollback. Verify registered trails and parameters resolve correctly.
- [ ] Detect existing route-name/path collisions, duplicate generation, and static/dynamic route ordering problems.
- [ ] Require explicit per-file overwrite choices; fail noninteractive conflicts unless exact overwrite targets were supplied.
- [ ] Implement path containment, safe escaping, file-state rechecks, backups, and recoverable rollback.
- [ ] Implement a write-free dry-run path shared by interactive review and later configuration import.
- [ ] Test cancellation, collision, partial-write failure, route parse failure, concurrent edits, Windows paths, and recovery diagnostics.
- [ ] Add/update route, overwrite, and recovery documentation and READMEs; run `composer test` in both changed projects.

Acceptance: valid output loads in a fixture application, repeated runs do not duplicate routes, and recoverable failures restore original files.

### Phase 8 — Generated actions, policies, and persistence

- [ ] Generate separate Create, Update, Delete, Restore, and Permanent Delete actions only when selected.
- [ ] Authorize and validate within each public action entry point; explicitly assign only the selected validated fields.
- [ ] Implement cast-aware password hashing, blank-password Update behavior, JSON conversion, decimal preservation, and richtext sanitization.
- [ ] Respect model connection, key type, global scopes, casts, events, and transaction boundaries.
- [ ] Generate missing policies only when chosen, with deny-by-default methods and clear customization notes.
- [ ] Preserve existing policies/models/factories; report incompatible assumptions and fixture requirements.
- [ ] Test direct action calls, unauthorized attempts, invalid data, unexpected payload keys, omitted operations, and each soft-delete state transition.
- [ ] Verify sanitizer behavior against unsafe HTML and verify password hashes never enter read payloads.
- [ ] Add/update actions, policies, security, and model integration documentation and READMEs; run `composer test` in both changed projects.

Acceptance: selected actions persist correct values on the intended connection; invalid or unauthorized invocations leave records unchanged.

### Phase 9 — Mandatory Index and Read output

- [ ] Generate Index and detail behavior for every resource, even when all write operations are deselected.
- [ ] Generate a concrete `Sirius\Ui\Livewire\Table` subclass with the installed release's supported query/column APIs.
- [ ] Implement pagination, searchable/sortable column allowlists, deterministic ordering, and a stable key tie-breaker.
- [ ] Exclude passwords/sensitive hidden attributes from automatic display; format enums, dates, booleans, and decimals appropriately.
- [ ] Add SoftDeletes trash filtering and policy-aware row actions.
- [ ] Generate a separate detail page in page mode and a detail dialog in dialog mode.
- [ ] Test listing, sorting/searching, pagination, scoped access, custom route keys, empty data, unauthorized detail access, and hidden fields.
- [ ] Browser-test table interaction and detail presentation in both layouts.
- [ ] Add/update listing/detail documentation and READMEs; run both projects' `composer test` and relevant browser tests.

Acceptance: Index and Read work in both layouts with no selected mutations and do not expose unauthorized or sensitive data.

### Phase 10 — Create/Update pages and Index dialogs

- [ ] Generate separate Create/Edit components and views in page mode for selected operations.
- [ ] Generate Create/Edit dialogs hosted by Index in dialog mode, reusing agreed form definitions and actions.
- [ ] Connect native `wire:submit` forms, field errors, loading states, success feedback, and navigation/table refresh.
- [ ] Implement confirmation dialogs for Delete, Restore, and Permanent Delete in both layouts.
- [ ] Preserve entered data after validation errors and reset fields/errors/selected IDs when dialogs close or switch records.
- [ ] Verify remote-select initial labels, enum selections, richtext, phone, currency, and datetime behavior after Livewire updates.
- [ ] Test permissions on every direct method invocation as well as visible controls.
- [ ] Browser-test keyboard/focus behavior, submission, cancellation, retry after validation errors, and repeated open/close cycles.
- [ ] Add/update layout and interaction documentation and READMEs; run both projects' `composer test`, relevant browser tests, and asset builds if changed.

Acceptance: both layouts support all selected operations without stale state, duplicated submissions, or policy bypasses.

### Phase 11 — Saved configurations and noninteractive generation

- [ ] Define a versioned JSON schema for the complete agreed resource definition.
- [ ] Implement configuration export/import with documented command options, alongside `--dry-run` and `--no-interaction`.
- [ ] Export definitions only, never credentials, live record values, or password input.
- [ ] Revalidate imported definitions against the current model/schema/dependencies and report schema drift.
- [ ] Fail clearly on missing information, unknown config versions, unresolved policies, and conflicts in noninteractive mode.
- [ ] Allow explicit overwrite-target lists without silently turning off conflict checks.
- [ ] Guarantee equivalent interactive and imported definitions generate equivalent content.
- [ ] Test round trips, malformed JSON, stale schemas, deterministic output, and dry runs that leave all application files unchanged.
- [ ] Add/update automation/CI usage documentation and READMEs; run `composer test` in both changed projects.

Acceptance: a saved configuration reproduces reviewed output without prompts, and missing required decisions fail with actionable errors.

### Phase 12 — Generated Pest suites and complete acceptance fixtures

- [ ] Generate host-app Pest feature tests for mandatory reads and each selected mutation.
- [ ] Cover validation, authentication, policy denial, trusted record resolution, password handling, and SoftDeletes where applicable.
- [ ] Reuse suitable existing factories and generate isolated fixture helpers otherwise; do not edit existing factories.
- [ ] Emit explicit customization guidance for required relationships/domain rules that cannot be inferred; do not silently skip tests or claim full passability for arbitrary models.
- [ ] Keep generated deny-by-default policy assertions honest: use explicit fixture permissions for positive behavior tests and separate denial coverage.
- [ ] Offer optional host-app browser tests with dependency/setup checks; do not silently install a browser plugin in the consuming app.
- [ ] Exercise the complete command-to-files-to-running-CRUD path in disposable applications for both layouts.
- [ ] Cover representative operation subsets, nested namespaces, custom keys/connections, enum casts, remote selects, and soft deletes.
- [ ] Run generated tests as code; supplement snapshots with syntax checks, boot checks, route checks, and persistence assertions.
- [ ] Add/update generated-testing and fixture-customization documentation and READMEs; run both projects' `composer test` and complete package browser acceptance tests.

Acceptance: generated suites execute successfully for supported reference fixtures, and unsupported application-specific fixture needs are clearly reported.

### Phase 13 — Documentation and working demonstrations

Documentation already grows in every phase. Phase 1 delivers the public homepage, Sirius theme, and authentication removal; this phase completes navigation, examples, and end-to-end demonstrations without reintroducing authentication.

- [ ] Complete English guides for installation, compatibility, quick start, architecture, discovery, databases, fields, validation, operations, layouts, routes, policies, stubs, configuration files, testing, and troubleshooting.
- [ ] Use Sirius UI throughout documentation navigation/forms/demonstrations and align light/dark semantic tokens with the upstream palette.
- [ ] Refresh the homepage introduction, supported-feature descriptions, quick-start links, and page/dialog previews to reflect the implemented package rather than planned capabilities.
- [ ] Remove obsolete Flux dependencies only after remaining usages are migrated and tested; keep the documentation site free of authentication/account features.
- [ ] Keep documentation and demonstrations accessible without login. Scope writable demo data to each anonymous session with synthetic fixtures, CSRF protection, and server-side validation; do not expose shared application data for unrestricted writes.
- [ ] Provide working page-mode and dialog-mode examples with synthetic data, scoped demo policies, enum fields, a same-origin select endpoint, and soft-delete examples.
- [ ] Isolate demonstration state from real application data and make resetting demo fixtures explicit and limited to the demo environment.
- [ ] Show demo-specific guest/session authorization explicitly; reject cross-session access and keep the generator's authenticated routes and deny-by-default policy scaffold unchanged.
- [ ] Test anonymous homepage/documentation/demo access, session-isolated mutations, removal of auth/account routes, navigation, responsive layouts, dark mode, and both full CRUD journeys in a browser.
- [ ] Finalize the docs README and minimal package README docs link without inventing an unconfigured deployment URL.
- [ ] Run both projects' `composer test`, docs/browser acceptance tests, and the docs production asset build.

Acceptance: a developer can install the package, generate either layout, customize stubs, and understand required policy/fixture changes using the docs alone.

### Phase 14 — Compatibility verification and release readiness

- [ ] Resolve and run supported PHP/Laravel/Livewire combinations, including minimum and current compatible dependencies.
- [ ] Verify inspection and CRUD behavior against real MySQL, MariaDB, PostgreSQL, and SQLite services.
- [ ] Separate valid Composer combinations from unsupported combinations; publish the tested compatibility matrix.
- [ ] Validate installation from a distributable package without local path-repository assumptions.
- [ ] Verify generated apps work on PHP 8.3 without depending on newer syntax or the docs app's development toolchain.
- [ ] Verify publication and override behavior for stubs, configuration, and Sirius skills from the release artifact.
- [ ] Check route/config caching, generated PHP syntax, dependency availability, and the default command experience in a clean consuming app.
- [ ] Review failure/recovery behavior and document remaining limitations and upgrade/custom-stub responsibilities.
- [ ] Finish changelog, release checklist, docs, and minimal package README; run `composer test` in every changed project and all required integration/browser/build checks.
- [ ] Record readiness evidence. Publishing a package or deploying the docs is a separate owner-instructed action, not an automatic phase side effect.

Acceptance: release artifacts install cleanly, the documented matrix is backed by executed checks, and no required phase gate remains unresolved.

## 6. Testing strategy

| Layer | Main responsibility | Required examples |
| --- | --- | --- |
| Architecture | Preserve agreed boundaries | Actions vs components, pure definitions, no app coupling |
| Unit | Pure transformations and decisions | Naming, type precedence, rule inference, config normalization |
| Command/feature | Wizard and package integration | Prompt defaults, edits, cancellation, discovery, publish behavior |
| Filesystem/route integration | Safe artifact application | Collision, repeat run, managed blocks, rollback, path containment |
| Real-database integration | Actual schema and persistence behavior | Driver metadata, keys, constraints, enums, custom connections |
| Generated-app feature | Actual generated code | Routes, policies, validation, writes, soft deletes, passwords |
| Browser | Livewire/Sirius behavior | Both layouts, canonical values, dialog lifecycle, focus, errors |
| Documentation app | Docs/demo application behavior | Public package homepage/docs, no auth/account routes, anonymous session-isolated demo writes, navigation, Sirius themes |

Use behavior-focused feature tests for framework interactions and unit tests for framework-independent logic. Follow existing project test conventions. Use factories where appropriate, create only necessary records, prevent unintended network calls, and keep time/randomness deterministic where they affect assertions. Do not mock the query builder to claim real database behavior.

No universal coverage percentage replaces the acceptance criteria. Each inferred rule branch and each meaningful failure mode needs appropriate evidence, without duplicating the same full matrix at every test layer.

## 7. Definition of done

- [ ] The agreed command workflow is complete, including review/edit/restart, conflict checks, and noninteractive use.
- [ ] Index and Read always exist; optional operations and SoftDeletes behavior match the selected model and configuration.
- [ ] Both UI layouts work with Sirius components and the documented field contracts.
- [ ] Generated actions own authorized, validated persistence; the generator itself performs no record/schema writes.
- [ ] Publishable stubs and saved configurations are documented and verified.
- [ ] Architecture, package, generated-app, browser, and real-database checks pass for the documented support matrix.
- [ ] Each completed phase has new/updated docs, appropriate README updates, and recorded `composer test` results for all changed projects.
- [ ] Both projects contain the complete Sirius UI AI skill and durable execution rules.
- [ ] The docs palette and light/dark theme match Sirius UI, the public homepage introduces the package, authentication/account features are removed, and both demo layouts work without login using isolated session data.
- [ ] Known limitations and required application-specific customizations are explicit; no unchecked required item is reported complete.

## 8. Execution evidence log

Phases 0–2 are complete. Append one entry per completed phase using this structure:

| Phase | Date | Changed projects | Documentation/README updates | Commands and results | Remaining limitations |
| --- | --- | --- | --- | --- | --- |
| Phase 0 | November 20, 2026 | Package and docs | `ARCHITECTURE.md`, both READMEs, both `AGENTS.md` files, this plan | Package `composer test`: 33 tests / 60 assertions on PHP 8.3 with Laravel 12.73.0 / Testbench 10.12.0 and Laravel 13.43.0 / Testbench 11.3.0; Pint and PHPStan pass. Docs `composer test`: 23 tests / 58 assertions, Pint and PHPStan pass. | Handwritten fixtures only; generator, Sirius integration, browser tests, real database adapters, and the complete release matrix belong to later phases. |

| Phase 1 | November 21, 2026 | Package and docs | `SETUP.md`, `ARCHITECTURE.md`, public docs pages, both READMEs and AI rules, this plan | Package `composer test`: 45 passed / 95 assertions on PHP 8.3, Laravel 13.43.0 / Testbench 11.3.0. Docs `composer test`: 29 passed / 48 assertions. Pint, PHPStan, and Rector pass in both. Docs `composer test:browser`: 3 passed / 24 assertions in Chromium; `npm run build` passed; both Composer manifests validate. | Prerequisite checks only; no CRUD generation or stub publishing yet. Full framework/database/generated-resource browser matrix remains future work. |

| Phase 2 | November 21, 2026 | Package and docs | `DISCOVERY.md`, `ARCHITECTURE.md`, `SETUP.md`, both READMEs, this plan | Package `composer test`: 95 passed / 206 assertions on PHP 8.3, Laravel 13.43.0 / Testbench 11.3.0. Docs `composer test`: 29 passed / 36 assertions. Pint, PHPStan, and Rector pass in both; whitespace checks pass. | Library discovery/planning services only; command wizard, schema inspection, enum options, and generation remain later phases. No UI changes or new browser run in this phase; no fresh multi-framework/database matrix claim. |
| Phase 3 | November 22, 2026 | Package and docs | `DATABASE_INSPECTION.md`, setup/discovery/architecture docs, both READMEs, this plan | Package `composer test`: 133 passed / 366 assertions; `composer test:database`: 10 passed / 89 assertions; docs `composer test`: 29 passed / 36 assertions. Pint/PHPStan/Rector passed. | Real local SQLite/MySQL/MariaDB/PostgreSQL tested; hosted CI versions remain unverified. Schema library only; wizard/generation remain later phases. |
| Phase 4 | November 26, 2026 | Package and docs | `FIELD_CONFIGURATION.md`, schema/discovery/setup/architecture docs, both READMEs, this plan | Full `composer test`: 198 passed / 532 assertions per SQLite/MySQL/MariaDB/PostgreSQL selection, plus selected-engine schema suites; docs 29 / 36. Pint/PHPStan/Rector pass. | Library field/validation services only. Confirmation-label prompt belongs to Phase 5; generated persistence, hashing, sanitization and UI remain later phases. No environment files edited. |

### Phase 0 verification details

- Package dependency resolution uses PHP 8.3 with Pest 3.8.7 / PHPUnit 11.5.56 and Livewire 4.4.8. The docs dependency set was not changed.
- Verified the architecture gate rejects a deliberate direct `Category::query()->delete()` inside the fixture Livewire component. Restored the original fixture and reran the complete passing gate.
- Tests use a disposable application base path and SQLite in-memory connection. The fixture action proves authorization and validation failures write no records and successful calls persist only permitted data.
- The source checker and compiled-Blade checks have documented limits; they supplement behavioral tests and do not prove all possible dynamic PHP side effects. Actual generator no-write checks must be added once the command exists.
- Local package dependencies were returned to Laravel 13 after checking Laravel 12. Minimum-patch dependency and full database/browser matrices are not claimed by this phase.
- PHP 8.5 can emit three upstream `ReflectionProperty::setAccessible()` deprecations from the Pest 3 architecture test stack. The PHP 8.3 baseline passes without those notices; no vendor code or deprecation reporting was altered.

### Phase 1 verification details

- Installed released Sirius UI 0.1.0 in both projects and verified all 11 skill bundle files against each standalone installation manifest. The installer reports no customization conflicts. Refresh instructions are in `SETUP.md`.
- Consumer fixtures load provider metadata through Laravel's `PackageManifest`; tests cover the discovered command, defaults/overrides, config publishing preservation, invalid paths, missing dependency diagnostics, and unavailable connections. The preflight executes no record queries and leaves routes untouched.
- Docs routes are public. Fortify/Flux integrations and auth/account routes/views have been removed; negative endpoint tests verify 404 responses. Existing user models/migrations were preserved. Generated consumer authentication defaults remain unchanged.
- Chromium checks cover navigation, persisted theme selection across reload/navigation, mobile overflow, and JavaScript errors. Desktop light/dark and mobile screenshots were visually reviewed. Browser tests use built assets without changing a running developer's Vite hot file.
- The asset build succeeds with non-fatal upstream bundle-size and optional fontaine warnings. Asset optimization is not claimed complete.
- An initial simultaneous `composer test` run encountered a shared Rector temporary-cache collision. Rector caches are now project-local under ignored `.phpunit.cache/rector`; both complete gates subsequently passed concurrently. An intermediate container-cache configuration failed validation and was corrected before the passing run.
- The final package gate used PHP 8.3 and had no deprecations. The known upstream Pest 3 deprecations on PHP 8.5 remain documented under Phase 0; no vendor changes or suppression were introduced.
- This phase verified the currently installed Laravel 13 dependency set; it does not claim a fresh Laravel 12 or complete minimum-version matrix run.

Planning-only verification belongs in a separate note below; it does not complete Phase 0 or any feature phase.

### Planning-only verification — November 20, 2026

- Created this English plan after the owner accepted the design recommendations. No application code, dependencies, or skills were changed or installed.
- Ran `composer test` in `sirius-laravel-builder-docs`: passed. Pint passed; PHPStan reported zero errors; Pest passed 23 tests with 58 assertions.
- Did not run package tests: the package directory is empty and has no Composer test harness yet. Creating that harness belongs to Phase 0.
- All implementation phases remain unstarted. Browser tests, cross-database tests, and generated-code checks are planned work, not checks completed during planning.


### Phase 1 documentation layout refinement — November 21, 2026

- At the owner's request, aligned `/docs` and its child pages with the local Sirius UI documentation layout: fixed desktop sidebar, compact sticky header with breadcrumbs, and a mobile Slideover menu. The approved homepage retains its original presentation.
- Used installed Sirius Menu/Menu Category/Menu Item, Breadcrumb, Slideover, Button, Icon, Link, Badge, and Separator components. Application CSS controls shell geometry and typography; Sirius owns navigation and overlay behavior.
- Updated `SETUP.md` and the docs README. No package source or later-phase features changed.
- Docs `composer test` passed: 29 tests / 48 assertions, with Pint, PHPStan, and Rector passing. `composer test:browser` passed: 4 tests / 33 assertions. `npm run build` passed with the existing optional-fontaine and bundle-size warnings. `git diff --check` passed.
- Visually reviewed desktop light/dark, mobile navigation, and mobile content screenshots. Browser coverage checks sidebar navigation, mobile Slideover navigation, theme persistence, viewport overflow, and no JavaScript errors.


### Phase 1 documentation typography refinement — November 21, 2026

- Matched the Sirius UI documentation font stack and size tokens on `/docs`: loaded Instrument Sans through `@fonts`, with 16px body, 30px page headings, 20px section headings, and 18px subheadings at the default root size. Kept the landing-page typography separate.
- Updated `SETUP.md`. The browser run exposed an early-click race in the docs theme control; it now remains disabled until theme initialization completes, with native and ARIA disabled states synchronized.
- Docs `composer test` passed (29 tests / 48 assertions; Pint, PHPStan, and Rector passed). Final `composer test:browser` passed (4 tests / 33 assertions) after resolving the theme-control readiness failure. Asset build passed with the previously recorded non-fatal warnings. Reviewed the updated typography screenshot.


### Phase 1 breadcrumb and content alignment refinement — November 21, 2026

- Installed `diglactic/laravel-breadcrumbs` 10.1.0 and centralized the Home/Documentation/Getting started/Architecture trails in `routes/breadcrumbs.php`. The shared template renders trails with Sirius Breadcrumb components.
- Centered the main container and footer within the workspace (80rem maximum), with a centered 56rem article. Added a Sirius Back to top link targeting a focusable anchor with clearance for the sticky header.
- Updated `SETUP.md` and the docs README. Package implementation and homepage remain unchanged.
- Docs `composer test` passed: 29 tests / 48 assertions, including passing Pint, PHPStan, and Rector gates. Browser tests passed: 5 tests / 39 assertions, including center alignment, parent breadcrumb navigation, and Back to top visibility below the sticky header. Reviewed the desktop screenshot. Asset build, Composer validation, and diff whitespace checks passed; existing non-fatal build warnings remain.


### Phase 1 copyable code examples — November 21, 2026

- Added a reusable `docs-code` component using Sirius Code and Button, with a language header and per-block Copy/Copied feedback. All docs code blocks use it; inline code remains inline.
- Clipboard writes preserve exact text and line breaks. Denied or unavailable clipboard access selects the code for manual copying and announces the fallback. Buttons wait for JavaScript initialization; feedback resets after 2.5 seconds.
- Updated `SETUP.md` and README. Final docs `composer test` passed: 29 tests / 48 assertions, including Pint, PHPStan, and Rector. Browser tests passed: 8 tests / 51 assertions. Clipboard success/denial/unavailability were controlled browser doubles; system clipboard permissions across browsers are not claimed tested.
- An initial browser assertion ambiguously matched visible button and accessibility status text; it now targets the button label. The complete browser suite passed after correction. Asset build and whitespace checks passed; existing build warnings remain.


### Phase 1 Livewire navigation refinement — November 21, 2026

- Added `wire:navigate` to internal homepage, sidebar/mobile menu, brand, breadcrumb, and article links. Static page content remains server-rendered Blade; Livewire handles page transitions and browser history without full reloads. External and same-page anchor links remain native.
- Reinitialize theme/copy controls on `livewire:navigated`, reset stale copied labels on history restoration, and clear feedback timers during navigation. Delegated event handlers stay registered once; Sirius manages its own navigation/overlay lifecycle.
- Updated `SETUP.md` and README. Browser tests passed: 9 tests / 68 assertions, including a retained window marker proving no full reload, back/forward history, copy after transitions without duplicate handling, themes, and mobile navigation. Docs `composer test` passed: 29 tests / 48 assertions; Pint, PHPStan, and Rector passed. Asset build and whitespace checks passed, with existing non-fatal asset warnings unchanged.


### CSS formatting maintenance — November 21, 2026

- The Builder package currently has no authored CSS. Reformatted the docs application's authored `resources/css/app.css`: multiline blocks/declarations, four-space indentation, one blank line between rules, and descriptive English section comments. Vendor and generated assets were excluded.
- Added durable CSS rules to both projects' `AGENTS.md` and the committed docs `CSS_STYLE.md`, linked from the docs README.
- PostCSS parsing verified unchanged selectors, declaration values/order, and rule order. Checked all 180 rules for multiline formatting, spacing, indentation, and absence of tabs. The compiled CSS asset hash remained unchanged after rebuilding.
- Package `composer test` passed on PHP 8.3: 45 tests / 95 assertions. Docs `composer test` passed: 29 tests / 48 assertions. Pint, PHPStan, and Rector passed in both projects. Docs browser checks passed: 9 tests / 68 assertions. Asset build and diff checks passed; existing non-fatal build warnings remain.


### Phase 1 in-page documentation navigation — November 21, 2026

- Added reusable On this page navigation using Sirius Link components and explicit per-page heading maps. Wide screens show a sticky right column matching Sirius UI docs; smaller screens place navigation above the article.
- Added unique, focusable heading anchors with sticky-header scroll clearance on all three docs pages. Navigation updates with Livewire page transitions; native fragment links require no additional JavaScript.
- Updated `SETUP.md` and README. Browser tests passed: 10 tests / 78 assertions, covering valid targets, anchor scrolling, updated links after transitions, mobile ordering, and viewport overflow. Reviewed the desktop screenshot.
- Docs `composer test` passed: 29 tests / 48 assertions, with Pint, PHPStan, and Rector passing. Asset build and whitespace checks passed; existing non-fatal build warnings remain. A later attempt to reopen the mobile screenshot could not find the local artifact; mobile browser assertions had passed.


### Phase 2 verification details — November 21, 2026

- Added immutable typed resource/model/enum/field/operation/layout/policy/route/artifact definitions. Optional operations exclude Read; unsupported soft-delete operations and duplicate field columns are rejected.
- Added deterministic PSR-4 recursive discovery, enum backing-type metadata, inherited/recursive SoftDeletes detection, namespace/source validation, and diagnostics for malformed PHP and unresolved autoload mappings. Discovery does not instantiate models; constructor-guard fixtures and an empty query log verify this behavior on an isolated Testbench connection.
- Added default and renamed resource identities with original-model table suggestions, portable PHP-name validation, normalized view/URL segments, and pre-render identity/artifact collision checks. Case-insensitive duplicates, existing files/classes, blocked parents, and overlapping planned paths are covered without file writes.
- Intentionally refined architecture checks to allow Model/SoftDeletes metadata references in Discovery, while rejecting DB/query/persistence access. Pure layers additionally reject delegated I/O through Discovery, Schema, or Support. Negative and positive witnesses pass against actual production definitions and discovery services.
- The initial architecture run on PHP 8.5 showed the already documented three upstream Pest deprecations. Final full package verification used PHP 8.3 and passed without deprecations. The docs suite's current result is 29 tests / 36 assertions; no docs test files were changed in Phase 2.
- `Client\Product` is the nested client model used in the acceptance fixture. Discovery relies on trusted PSR-4 autoload code, not arbitrary PHP sandboxing. Directory symlinks are not followed by the underlying traversal; complete writer containment/atomicity remains later work.
- Both required Composer gates passed. Documentation-only changes in the docs app did not require new asset/browser runs. No dependency upgrade or production database access was needed.

### Table default and breadcrumb contract revision — November 21, 2026

- Replaced the earlier independent model-basename table suggestion with Eloquent's resolved `getTable()` value. Phase 2 naming now requires the resolved table as input; discovery still never constructs models. Selected-model resolution and prompt wiring remain Phase 3/5 responsibilities.
- Added optional consumer Diglactic breadcrumb detection/review to Phase 5, a publishable registration stub to Phase 6, and automatic safe registration updates to Phase 7. Generator and wizard checkboxes remain unchecked; the existing docs application's breadcrumbs are separate from this planned consumer integration.
- Verification: architecture prerequisite passed (5 tests / 39 assertions). Package `composer test` passed on PHP 8.3 (97 tests / 211 assertions); docs `composer test` passed (29 tests / 36 assertions). Pint, PHPStan, and Rector passed in both projects. Diff whitespace checks passed. No UI changes; browser/build checks were not rerun. Naming regression coverage verifies conventional and explicitly resolved custom tables, resource renaming, empty-table rejection, and no database queries.

### Nested model example revision — November 21, 2026

- Standardized the model example as `Client\Product` in the workflow, acceptance criteria, discovery documentation, and handwritten fixture; updated the discovery expectation accordingly. Reserved-name rejection remains covered separately with `Invalid\Object`.
- Architecture prerequisite: 5 tests / 39 assertions passed using Pest directly (`composer test:arch` is unavailable in the current scripts). Full `composer test` passed in both projects: package 97 tests / 211 assertions on PHP 8.3; docs 29 tests / 36 assertions. Pint, PHPStan, Rector, and whitespace checks passed. No UI changes or browser/build rerun.

### Phase 3 preparation — November 22, 2026

- Architecture prerequisite passed: 5 tests / 39 assertions. Schema implementation is pending the owner's local test-database setup.
- Added owner-only environment-file rules in both projects, ignored package `.env.*` variants, and documented `.env.database` variables in `SETUP.md`. No environment files were created or edited.
- Added a GitHub Actions PostgreSQL/MariaDB service job and real PDO connection checks. Full schema tests and MySQL service coverage remain Phase 3 work. No GitHub-hosted execution has been observed.
- Local quality gates passed: package `composer test` 97 tests / 211 assertions (PHP 8.3), docs `composer test` 29 tests / 36 assertions; Pint/PHPStan/Rector pass. Workflow YAML parses successfully and whitespace checks pass. Database connections were not attempted locally while waiting for owner configuration; CI service execution remains unverified.

### Phase 3 implementation — November 22, 2026

- Added read-only selected-model schema inspection, typed metadata, alias normalization, diagnostics, custom table/key/connection support, and PostgreSQL native enum/identity catalog reads. Discovery remains construction-free. No command wizard or generator was added.
- Added an opt-in real-engine runner loading only the owner-managed `.env.database`; no environment file was written. Ordinary tests still use SQLite memory. Dedicated local test database names may differ from the sample; loopback/system-database guards apply. Test cleanup targets only unique test-owned objects.
- Real-engine suite passed: 10 tests / 89 assertions on SQLite 3.53.4, MySQL 9.7.0, MariaDB 13.0.2, PostgreSQL 19.0. Cases include no inspection writes or row/schema changes, prefixes, custom keys, native enums, PostgreSQL identity and schema qualification.
- Updated GitHub Actions to run this suite against MySQL 8.4, MariaDB 11.4, PostgreSQL 17. Those hosted versions have not been executed locally or verified in GitHub during this task.
- Added `DATABASE_INSPECTION.md`; updated setup, discovery, architecture, and both READMEs. Final `composer test` passed in the package (133 tests / 366 assertions, PHP 8.3) and docs (29 tests / 36 assertions). Pint, PHPStan, and Rector passed in both projects. Workflow YAML and whitespace checks passed. No UI changes; browser/build checks were not rerun. Actual local engines above do not establish the whole version/framework matrix.

### Phase 3 database matrix correction — November 22, 2026

- Replaced the schema-only CI entry with SQLite/MySQL/MariaDB/PostgreSQL matrix entries, each running full `composer test`, including the selected engine's schema cases.
- Added an explicit process selector, selected-connection feature tests, unique per-test external table prefixes/cleanup, and a regression assertion that the active driver matches the request. SQLite-specific tests keep explicit SQLite connections; unselected local runs remain SQLite memory only.
- Updated both project rules and documentation. No environment files were edited. Local PHP 8.3 full gates passed for all four selectors: 133 ordinary tests / 367 assertions per selector, plus SQLite 1 schema test / 17 assertions, MySQL 3 / 23, MariaDB 3 / 23, PostgreSQL 3 / 26. Docs `composer test` passed: 29 / 36. Pint/PHPStan/Rector, matrix YAML validation, and whitespace checks passed. An additional unselected PHP 8.5 run exited successfully with the previously known 3 upstream deprecations (130 passed / 367 assertions). Hosted GitHub Actions execution remains unverified.

### Phase 4 implementation — November 22, 2026

- Architecture prerequisite passed (5 tests / 46 assertions). Added pure field suggestions/plans, editable shared/Create/Update rules, retained structured constraints, enum option mapping, and canonical values. Resource definitions can retain the full plans without losing dynamic validation metadata.
- Added enum reflection outside inference, a Laravel validation adapter requiring trusted model identity for unique Update checks, and read-only catalog evidence that prevents partial/expression/prefix/composite indexes from becoming unsafe single-column unique validation.
- Added regression coverage for canonical numeric bounds, JSON cast behavior, date/time precision, password omission/confirmation, readonly fields, and validation on real selected connections. Enabled prefixed test index/constraint names to avoid collisions on shared disposable databases.
- Added `FIELD_CONFIGURATION.md`; updated setup, schema/discovery/architecture docs, both READMEs, and this plan. No environment files or UI were changed. Wizard, generated hashing/sanitization, and rendering remain later phases.
- Completed on November 26, 2026 after the confirmation-label revision below. Full PHP 8.3 matrix gates passed: 198 tests / 532 assertions for each SQLite/MySQL/MariaDB/PostgreSQL selection; additional schema acceptance checks passed (SQLite 1 / 19, MySQL 3 / 24, MariaDB 3 / 24, PostgreSQL 3 / 27). Docs `composer test` passed: 29 tests / 36 assertions. Pint, PHPStan, Rector, and whitespace checks passed. No UI changes or browser/build rerun. Hosted CI and unexecuted dependency combinations are not claimed.

### Phase 4 confirmation-label revision — November 26, 2026

- Added `confirmationLabel` to field planning; null uses `Confirm {label}`, while a nonempty custom label is retained in confirmation metadata and subsequent validation edits. Empty/whitespace labels and labels supplied for non-password fields are rejected.
- Updated the Phase 5 wizard contract to ask this question explicitly and preserve customized answers. No wizard UI is implemented in Phase 4.
- Added explicit ULID metadata mapping and validation without inferring ULIDs from string length. Architecture was rechecked before this revision (5 tests / 59 assertions). Final matrix and docs results are recorded above; no environment files were edited.
