# Sirius Laravel Builder — Phased Implementation Plan

Status: Phase 0 completed; Phases 1–14 not started.

Decision date: November 20, 2026.

Documentation scope revised: November 21, 2026 — use Sirius UI's palette and theme, remove documentation-app authentication, and build a public package-introduction homepage. This supersedes the earlier decision to retain docs login and require authenticated demonstrations. It does not change authentication/policy defaults in generated consumer applications.

This plan records the requirements and recommendations accepted during the design interview. The owner subsequently authorized Phase 0, whose completed checkboxes and evidence appear below. Later phases require an instruction to proceed; completion of Phase 0 does not authorize them automatically.

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
2. Recursively scan the application's `Models` directory for concrete Eloquent models. Display names relative to it, such as `User`, `Admin\Category`, and `Client\Object`. Handle duplicates by their relative namespace.
3. Ask for the resource name. Default to the selected model's relative namespace and name; preserve namespace segments unless the developer changes them.
4. Ask for the table name. Default to the snake case of the plural model basename, independent of a resource rename. Also display the table resolved by the model.
5. Use the model's connection for inspection. If the entered table disagrees with the model's resolved table, explain the mismatch and stop final generation until it is corrected. Do not silently rewrite the model.
6. Inspect columns and relevant constraints through normalized, connection-aware schema inspection. Prefer Laravel schema APIs; use driver-specific metadata queries where necessary.
7. Iterate over columns in database order and configure each included field as specified below.
8. Ask once for separate pages or Index dialogs.
9. Offer optional operations, defaulting all available choices on. Always retain Index and Read/detail.
10. Configure route identity, authentication/policy handling, and optional generated browser tests. Default to authenticated routes and policy enforcement; public access requires an explicit developer choice and still respects the selected policy behavior.
11. Review model, connection, table, resource, fields, labels, validation, operations, layout, policies, files, routes, conflicts, and manual customization requirements.
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

- **Password:** generate the original field and `{column}_confirmation`; use the label `Confirm {label}`. Validate confirmation; never persist it. Hash passwords while respecting an existing hashed cast/mutator to avoid double hashing. Never prefill, list, or display stored password hashes. Blank Update passwords preserve the existing value; nonblank passwords require confirmation.
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

- [ ] Register `make:crud`, package configuration, publishing hooks, and dependency discovery using supported Laravel APIs.
- [ ] Define configurable discovery paths, stub paths, and route defaults while keeping the agreed defaults intact.
- [ ] Add clear diagnostics for missing dependencies, invalid app structure, and unavailable database connections.
- [ ] Test provider discovery, command registration, configuration resolution, and preflight failures.

#### 1.2 Sirius UI and AI skills

- [ ] Install a compatible Sirius UI release in the package dependency graph and docs app.
- [ ] Install the complete Sirius UI AI Agent Skill in both projects under `.agents/skills/sirius-ui-development`.
- [ ] Select one skill-installation owner per project. Use Boost or the standalone installer, not both for the same directory.
- [ ] For the package root, use the supported installer service through its bootstrapped PHP/Testbench context if needed; do not assume an application `artisan` file exists.
- [ ] Verify the installed skill includes its referenced files and document refresh behavior on upgrades.
- [ ] Establish Sirius asset loading without duplicating Livewire/Alpine initialization.

#### 1.3 Public documentation redesign

- [ ] Replace the starter palette and theme with Sirius UI's light/dark tokens, typography, surfaces, borders, and semantic component colors; verify buttons, inputs, navigation, and code examples rather than changing only the primary color.
- [ ] Remove documentation-app authentication: login, registration, logout, password reset, email verification, two-factor authentication, and account/profile/password settings screens and routes, where present.
- [ ] Remove login/register/account links and authentication middleware or redirects from public documentation routes. Keep theme preferences available without an account.
- [ ] Audit authentication-specific providers, configuration, frontend code, and dependencies such as Fortify; remove unused integration after confirming it is no longer referenced. Do not change the generated application's authentication/policy defaults.
- [ ] Build a public homepage at `/` introducing Sirius Laravel Builder: package purpose, supported stack, action-based CRUD output, the `php artisan make:crud` workflow, page/dialog layout options, and links to Getting Started, documentation, and the source repository.
- [ ] Clearly distinguish available functionality from planned features while development is incomplete; do not present the homepage as a completed release before the generator exists.
- [ ] Add feature tests for public homepage/docs access and removal of obsolete auth/account routes; update existing authentication tests to reflect the new requirements.
- [ ] Add browser checks for homepage navigation, responsive presentation, Sirius light/dark themes, and the absence of login/account controls or login redirects.
- [ ] Add/update setup documentation and READMEs; run both projects' `composer test`, relevant browser checks, and the docs asset build.

Acceptance: a consuming fixture discovers the command, both projects have usable Sirius skills, and the docs site has a public package-introduction homepage with Sirius styling. Documentation-app authentication and account screens/routes are removed, and homepage/docs access does not require login.

### Phase 2 — Resource definitions and recursive discovery

- [ ] Implement typed definitions for resource identity, fields, operation selection, layout, policies, routes, and artifacts.
- [ ] Implement recursive concrete-model discovery with stable relative names and deterministic ordering.
- [ ] Discover enums recursively, preserving fully qualified names for duplicate basenames.
- [ ] Detect SoftDeletes through inherited/recursive trait use.
- [ ] Derive default resource identity, model-basename table suggestion, namespaces, view paths, URLs, and route names.
- [ ] Validate renamed resources and detect namespace/path collisions before rendering.
- [ ] Cover empty folders, nested models, abstract/non-model classes, duplicate names, invalid input, and discovery errors.
- [ ] Add/update discovery and naming documentation and READMEs; run `composer test` in both changed projects.

Acceptance: the sample models display as `User`, `Admin\Category`, and `Client\Object`, and nested names remain distinct throughout output planning.

### Phase 3 — Database inspection and normalized schema

- [ ] Inspect the selected model's connection and actual table; preserve the requested default table prompt and detect mismatch.
- [ ] Normalize columns, nullability, defaults, length, precision/scale, generated status, primary keys, and relevant indexes/foreign keys.
- [ ] Implement and verify MySQL/MariaDB, PostgreSQL, and SQLite adapters or compatible framework-backed inspection paths.
- [ ] Normalize driver aliases and inspect enum values where the engine exposes reliable metadata.
- [ ] Handle table prefixes and schema-qualified names without unsafe identifier interpolation.
- [ ] Reject unsupported composite keys and explain ambiguous or unsupported metadata.
- [ ] Prove inspection does not alter records/schema; test missing tables, inaccessible connections, custom keys, and custom model connections.
- [ ] Run real-engine inspection tests, not SQLite substitutes for all drivers.
- [ ] Add/update database compatibility documentation and READMEs; run `composer test` in both changed projects.

Acceptance: equivalent schemas produce equivalent normalized field metadata, and driver differences are explicitly represented and tested.

### Phase 4 — Field configuration and validation inference

#### 4.1 Inclusion and field selection

- [ ] Implement inclusion defaults, title-case labels, ordered type candidates, and `_id` precedence.
- [ ] Implement password confirmation metadata, endpoint-backed select configuration, and enum-to-option mapping.
- [ ] Implement supported additional types and explicit unsupported-type handling.
- [ ] Keep generated columns read-only and identify omitted required data that needs application customization.

#### 4.2 Validation and normalization

- [ ] Infer schema-aware baseline validation and integrate selected-field semantics.
- [ ] Support pipe-based edits, shared/Create/Update rules, and structured dynamic constraints.
- [ ] Handle nullable/default values, trusted Update uniqueness, foreign keys, enum compatibility, and decimal precision.
- [ ] Define canonical conversions for select IDs, booleans, currency, dates, phone values, JSON, and existing casts.
- [ ] Cover precedence, boundary values, malformed rules, password confirmation, incompatible enums, and unsupported mappings with focused tests.
- [ ] Add/update field mapping and validation documentation and READMEs; run `composer test` in both changed projects.

Acceptance: every supported schema/field combination has a deterministic suggestion and can be edited without losing required dynamic validation behavior.

### Phase 5 — Interactive wizard and review

- [ ] Implement the ordered prompt flow and searchable model/enum choices where appropriate.
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

- [ ] Implement shipped templates, deterministic rendering, and per-template app overrides.
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

Phase 0 is complete. Append one entry per completed phase using this structure:

| Phase | Date | Changed projects | Documentation/README updates | Commands and results | Remaining limitations |
| --- | --- | --- | --- | --- | --- |
| Phase 0 | November 20, 2026 | Package and docs | `ARCHITECTURE.md`, both READMEs, both `AGENTS.md` files, this plan | Package `composer test`: 33 tests / 60 assertions on PHP 8.3 with Laravel 12.73.0 / Testbench 10.12.0 and Laravel 13.43.0 / Testbench 11.3.0; Pint and PHPStan pass. Docs `composer test`: 23 tests / 58 assertions, Pint and PHPStan pass. | Handwritten fixtures only; generator, Sirius integration, browser tests, real database adapters, and the complete release matrix belong to later phases. |

### Phase 0 verification details

- Package dependency resolution uses PHP 8.3 with Pest 3.8.7 / PHPUnit 11.5.56 and Livewire 4.4.8. The docs dependency set was not changed.
- Verified the architecture gate rejects a deliberate direct `Category::query()->delete()` inside the fixture Livewire component. Restored the original fixture and reran the complete passing gate.
- Tests use a disposable application base path and SQLite in-memory connection. The fixture action proves authorization and validation failures write no records and successful calls persist only permitted data.
- The source checker and compiled-Blade checks have documented limits; they supplement behavioral tests and do not prove all possible dynamic PHP side effects. Actual generator no-write checks must be added once the command exists.
- Local package dependencies were returned to Laravel 13 after checking Laravel 12. Minimum-patch dependency and full database/browser matrices are not claimed by this phase.
- PHP 8.5 can emit three upstream `ReflectionProperty::setAccessible()` deprecations from the Pest 3 architecture test stack. The PHP 8.3 baseline passes without those notices; no vendor code or deprecation reporting was altered.

Planning-only verification belongs in a separate note below; it does not complete Phase 0 or any feature phase.

### Planning-only verification — November 20, 2026

- Created this English plan after the owner accepted the design recommendations. No application code, dependencies, or skills were changed or installed.
- Ran `composer test` in `sirius-laravel-builder-docs`: passed. Pint passed; PHPStan reported zero errors; Pest passed 23 tests with 58 assertions.
- Did not run package tests: the package directory is empty and has no Composer test harness yet. Creating that harness belongs to Phase 0.
- All implementation phases remain unstarted. Browser tests, cross-database tests, and generated-code checks are planned work, not checks completed during planning.
