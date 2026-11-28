# Field configuration and validation inference

Phase 4 provides library services for deterministic field suggestions, editable validation plans, and canonical values. It does not add interactive prompts, generated CRUD files, password persistence, or a richtext rendering pipeline. Those are later phases.

## Suggest and configure fields

```php
use Sirius\LaravelBuilder\Definitions\FieldType;
use Sirius\LaravelBuilder\Inference\FieldInference;
use Sirius\LaravelBuilder\Inference\FieldPlanner;
use Sirius\LaravelBuilder\Schema\SchemaInspector;

$schema = app(SchemaInspector::class)->inspect($selectedModel);
$column = $schema->columns[1]; // The wizard will select the intended column.
$suggestion = (new FieldInference)->suggest($column);
$plan = (new FieldPlanner)->plan(
    $schema,
    $column,
    FieldType::Text,
    cast: $selectedModel->getCasts()[$column->name] ?? null,
);
```

The chosen type must be compatible with the column. Use the returned `types` list instead of hard-coding Text for every column. Suggestions include an inclusion default, title-case label, ordered candidates, and a writable flag. Inclusion defaults to false for `id`, `_at` suffixes, and timestamp types. Generated and auto-increment columns remain non-writable regardless of the inclusion choice.

`_id` columns offer Select/Number/Text ahead of ordinary string/integer mappings; real enum/date metadata is preserved. UUID/ULID IDs offer Select/Text because numeric conversion cannot round-trip these identifiers. ULID mapping requires explicit reliable metadata (`type=ulid`); arbitrary 26-character strings are never assumed to be ULIDs. Decimal supports Number/Currency, boolean supports Boolean, JSON supports Textarea, and unsupported/binary types require explicit custom implementation. Float supports Number; currency never uses a floating-point conversion.

`FieldPlan` contains the original column, `FieldDefinition`, `ValidationDefinition`, optional confirmation field, enum options, supplied cast, driver, and manual-customization warnings. `ResourceDefinition.fieldPlans` can carry these plans alongside matching `fields`, preserving order and structured constraints for future generation.

## Validation defaults and edits

Non-null columns without defaults are required. Nullable columns use `nullable`. Non-null columns with defaults use `sometimes|required`, allowing omission while rejecting an explicitly empty value. Database defaults remain expressions; the planner never evaluates them. Shared rules include type/length/format checks. Exact integer bounds and decimal precision are enforced by the canonical-value rule as well as the editable Laravel rules.

```php
use Sirius\LaravelBuilder\Inference\RuleParser;

$validation = (new RuleParser)->edit(
    $plan->validation,
    shared: 'string|max:40',
    create: 'required',
    update: 'sometimes|required',
);
$plan = $plan->withValidation($validation);
$preview = implode('|', $plan->validation->rules(false));
```

A null edit preserves that section; an empty string clears it. Structured membership, uniqueness, and existence constraints survive edits. The parser rejects empty segments, control characters, PHP expressions, contradictory required/nullable sections, and attempts to flatten dynamic rules into `unique`, `exists`, `in`, or `enum` pipe strings. Regex/not_regex require a structured customization outside this pipe editor because their delimiters may contain pipes. The parser validates syntax, not the existence of every application-defined Laravel rule, and never evaluates PHP.

Use `Support\FieldValidation` to adapt the plan to Laravel:

```php
$adapter = new \Sirius\LaravelBuilder\Support\FieldValidation;
$input = $adapter->input([$plan], $input, updating: true);
$rules = $adapter->rules(
    $plan,
    timezone: config('app.timezone'),
    updating: true,
    trustedRecord: $authorizedRecord,
);
$validated = validator($input, [$plan->field->column => $rules])->validate();
```

The record must already be resolved and authorized by server code. The adapter accepts a model, never a request-provided ignore ID; it checks persisted state, key, table, and connection before applying Update uniqueness. This does not replace record authorization. Views and components must not treat this adapter as persistence authorization.

Schema inspection now adds `IndexDefinition.unconditional` evidence from the real catalog: partial/expression indexes, PostgreSQL included-column indexes, and MySQL prefix indexes are not treated as ordinary full-column uniqueness. Only proven single-column unique constraints are inferred. Composite/ambiguous constraints produce manual-configuration diagnostics. Single-column foreign keys get structured existence checks on their actual connection and referenced schema/table, respecting connection prefixes. Existence is not user/tenant authorization; scoped queries and actions remain responsible for access controls.

Read-only fields always validate as prohibited, even when editable rule sections are replaced. Confirmation fields are validation-only; future generated actions must persist only the writable column allowlist.

## Enums and selects

`Discovery\EnumCaseDiscovery` reads trusted discovered PHP enums outside the pure inference layer. It returns case names and backing values (pure enums use case names). Pass them to `Inference\EnumOptions::map($cases, $column)` to obtain `[{value, label}, ...]` records with string IDs and title-case labels. Empty/whitespace/control-character IDs, duplicate IDs, values outside native enum membership, and incompatible integer/length mappings are rejected. Subsets of native enum values are allowed; ensure existing records are compatible before adopting a narrower enum cast.

Pass the selected `EnumDefinition` and mapped options into `FieldPlanner::plan()`. Existing enum casts on string/integer columns may explicitly select this enum-backed Select. A selected enum that disagrees with an existing cast is rejected. Cast inspection belongs to the selected-model orchestration layer; inference never resolves a model or database itself.

A plain select accepts one optional same-origin endpoint path. Blank input becomes null and leaves options empty. Absolute/cross-origin paths and control characters are rejected. The planner never calls an endpoint. Generated controls will use Sirius's GET `q`/`page` search and `values[]` initial-resolution contract; the endpoint supplies `{"options":[{"value":"1","label":"Example"}],"hasMore":false}` and owns authentication/scoping.

## Special fields and canonical values

`Inference\ValueNormalizer::normalize($plan, $value, $timezone)` validates/converts a validated input for later assignment; `forEditing($plan, $value, $timezone)` prepares an existing cast value for a form. It does not save records, hash passwords, or sanitize HTML. `FieldValidation` uses normalization as a validation check without replacing the original input; call normalization explicitly after validation when assigning writable fields.

- **Password:** the plan includes `{column}_confirmation` and rejects conflicts with existing columns. Pass `confirmationLabel: 'Repeat your password'` to choose its label; null defaults to `Confirm {label}` using the selected main label. The future wizard will ask this question for Password fields. Empty/whitespace labels and confirmation labels on other field types are rejected. Custom labels survive validation edits. `confirmed` requires a matching value. `FieldValidation::input()` omits null/empty Update passwords and their confirmation; call it before validation. `forEditing()` always returns an empty password. Hashing belongs to generated actions in Phase 8, with an existing hashed cast/verified mutator respected to prevent double hashing. No stored password should appear in lists, detail views, or form defaults.
- **Numbers/currency:** integer/decimal values normalize to strings without float conversion. Integer limits follow native type and driver (SQLite uses 64-bit); unsigned BIGINT limits are compared lexically. Currency rejects grouping, exponent notation, floating-point input, overflow, and excess decimal places; missing fractional places are padded without rounding. Negative or greater-than-precision scales and mismatched decimal casts require custom mapping. A string column using Number retains string-length validation, not numeric max semantics.
- **Boolean:** only true/false, 1/0, and their string equivalents normalize. Truthy strings such as `false` or `yes` are rejected.
- **Date/time:** Sirius uses local `Y-m-d`, `H:i`, and `Y-m-d H:i:s` values. An explicit application/widget timezone is required for validation; input is not silently reinterpreted as UTC. Time persistence appends `:00`. Invalid dates and nonexistent DST wall-clock times are rejected. DateTime cast objects require an explicit editing timezone. Fractional seconds and nonzero time seconds that the chosen widget cannot preserve require customization. Ambiguous repeated DST wall-clock times remain wall-clock values; an application needing an absolute instant must provide its own offset/disambiguation policy.
- **Phone:** the generated `<x-sirius::phone>` control binds the canonical international string. Both Livewire and direct action calls validate it with `Sirius\Ui\Rules\PhoneNumber`. The default rule accepts a leading plus followed by 8–15 digits starting with a nonzero digit. Errors use Sirius UI translations. This verifies structure, not allocation or reachability; add application-specific phone verification as necessary.
- **JSON:** uncast JSON stays a validated string. Array/json/object/collection casts decode once and form editing encodes once; object/array shape must match the cast. Integers that would become floating-point values and non-finite JSON numbers are rejected for decoded casts; quote those values or provide an adapter. Decimal JSON values follow PHP's existing JSON/cast numeric semantics. Custom JSON casts require an adapter.
- **Richtext:** the plan records a mandatory maintained server-side sanitizer/allowlist requirement. Installing/wiring sanitization before persistence and before rendering legacy HTML belongs to generated actions/views. This phase does not produce sanitized HTML or upload endpoints.

Unknown casts produce explicit customization warnings. In particular, do not rely on a PHP integer cast to preserve unsigned BIGINT values larger than PHP's integer range. `omittedRequired()` reports skipped non-null/default-free/non-generated columns; the caller may provide a list of verified model-supplied values. It never guesses that a mutator or event fills a missing column.

## Verification and phase boundary

Unit tests cover mapping precedence, unsupported mappings, rule parsing, exact numeric boundaries, enum compatibility, and canonical formats. Feature tests exercise Laravel validation, read-only fields, password confirmation, unique Update identity, foreign keys, and partial/expression indexes. Run full `composer test` for each process-level `BUILDER_TEST_DRIVER` (`sqlite`, `mysql`, `mariadb`, `pgsql`); the local engine configuration remains owner-managed. The existing CI matrix uses these same full gates. No UI changed, so no new browser acceptance is claimed.

The wizard remains Phase 5; rendering and generation remain Phases 6 onward. Source contracts: [Laravel validation](https://laravel.com/framework/docs/validation/latest) and the installed Sirius UI 0.1.0 skill's canonical field references.
