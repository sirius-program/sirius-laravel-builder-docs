# Resource definitions, discovery, and naming

Phase 2 provides library services for discovering models/enums and planning resource identities. The interactive wizard is a later phase: `make:crud --check` still checks prerequisites, while `make:crud` does not generate files yet.

## Model discovery

Resolve `Sirius\LaravelBuilder\Discovery\ModelDiscovery` through Laravel's container. Pass an absolute directory and its Composer-autoloadable PSR-4 namespace:

```php
use Sirius\LaravelBuilder\Discovery\ModelDiscovery;

$models = app(ModelDiscovery::class)->discover(
    base_path(config('sirius-builder.paths.models')),
    'App\\Models',
);
```

The result is a deterministically sorted list of immutable `ModelDefinition` objects:

| Property | Example |
| --- | --- |
| `className` | `App\Models\Admin\Category` |
| `relativeName` | `Admin\Category` |
| `softDeletes` | `true` or `false` |

Discovery recursively inspects PHP files and includes concrete Eloquent subclasses only. Abstract models, interfaces, traits, and other classes are excluded. Non-PHP files are ignored. Duplicate basenames remain distinct through relative names and fully qualified classes, such as `Admin\Category` and `Client\Category`.

SoftDeletes detection includes parent classes and traits used by other traits. Discovery never constructs or boots models, resolves their tables/connections, or queries records. A fixture whose constructor throws verifies that model construction is unnecessary. Phase 3 now resolves the selected model table through [SchemaInspector](DATABASE_INSPECTION.md); discovery remains separate.

The discovery fixture uses `User`, `Admin\Category`, and `Client\Product` as concrete model examples, including nested namespaces.

## Enum discovery

```php
use Sirius\LaravelBuilder\Discovery\EnumDiscovery;

$enums = app(EnumDiscovery::class)->discover(
    base_path(config('sirius-builder.paths.enums')),
    'App\\Enums',
);
```

`EnumDefinition` contains the fully qualified `className`, relative name, and `backingType` (`string`, `int`, or `null` for a pure enum). Duplicate names in different namespaces remain selectable independently. Phase 4 now provides `EnumCaseDiscovery` and pure enum option mapping/compatibility checks; see [field configuration](FIELD_CONFIGURATION.md).

## Discovery requirements and errors

- Each PHP file must follow the supplied namespace/path mapping and declare its corresponding class, interface, trait, or enum. For a custom discovery directory, pass the matching namespace and maintain Composer's autoload mapping.
- A missing models directory is an error. An existing empty directory returns an empty list. A missing optional enums directory also returns an empty list; an existing non-directory does not.
- Invalid namespaces, malformed PHP, unresolved parent classes, incorrect class casing/aliases, and mismatched autoload source files produce `DiscoveryException` diagnostics. Refresh Composer autoload configuration when appropriate.
- Source files resolving outside the configured directory are rejected. The underlying filesystem traversal does not follow symlink directories by default.
- Discovery uses the application's trusted autoloader. PHP files should contain declarations without top-level side effects. Reflection avoids model construction but cannot sandbox arbitrary application/autoloader code.

## Resource identity and defaults

```php
use Sirius\LaravelBuilder\Support\ResourceNamer;

// $model is discovery metadata; $selectedModel is the selected Eloquent instance.
$table = $selectedModel->getTable();
$identity = app(ResourceNamer::class)->identify($model, $table);
$renamed = app(ResourceNamer::class)->identify($model, $table, 'Catalog\\ProductGroup');
```

For a selected `App\Models\Admin\Category`:

| Identity | Default |
| --- | --- |
| Resource name | `Admin\Category` |
| Suggested table | `categories` |
| Livewire namespace | `App\Livewire\Admin\Category` |
| Action namespace | `App\Actions\Admin\Category` |
| View directory | `resources/views/livewire/admin/category` |
| View name | `livewire.admin.category` |
| Feature test file | `tests/Feature/Crud/Admin/CategoryTest.php` |
| URL prefix | `/admin/categories` |
| Route name prefix | `admin.categories.` |
| Middleware | `['auth']` |

Renaming the resource changes its output identity but not the table suggestion. The required second argument is the selected model's `getTable()` result: Laravel supplies its conventional default and respects custom `$table`, `setTable()`, and overridden resolution. The namer preserves that value and rejects an empty table; it never constructs the model or queries a connection. Phase 3 provides selected-model resolution through `SchemaInspector`; the interactive prompt remains Phase 5. Do not prepend a connection table prefix to this value.

The optional fourth `identify()` argument supplies the application namespace. Resource and namespace segments must be valid PHP identifiers separated by backslashes. Whitespace, forward-slash paths, empty segments, reserved PHP/type names, and Windows device names are rejected. View/URL segments normalize with Studly then kebab casing; generated PHP namespace spelling is retained. This makes normalized-path collision detection necessary even for differently spelled PHP names.

Route-prefix configuration and actual route generation remain part of later orchestration/generation phases. These definitions describe the default identity; no routes are registered by naming.

## Typed definitions

The `Definitions` layer contains immutable data objects and backed enums. It has no command, UI, filesystem, database, or service-container dependencies.

- `ResourceDefinition` groups model, identity, operations, layout, policy, fields, and artifacts.
- `Layout` chooses `Pages` or `Dialogs`.
- `OperationSelection` defaults to Create, Update, and Delete; SoftDeletes adds Restore and PermanentDelete. Explicit empty selections are supported. Invalid soft-delete operations are rejected and duplicates are normalized into a stable order. Index/detail remain mandatory resource capabilities and are not optional mutation enum cases.
- `PolicyDefinition` defaults to enforcement. Policy existence and authorization behavior are verified in later generation/application phases.
- `FieldDefinition` captures column, label, `FieldType`, rules, writability, and an optional select endpoint or enum. A select cannot use both sources; absent endpoints use `null`. Phase 4 now groups this field with structured validation and normalization metadata in `FieldPlan`; see [field configuration](FIELD_CONFIGURATION.md).
- `RouteDefinition` stores URL/name prefixes and middleware.
- `ArtifactDefinition` stores `ArtifactKind`, a canonical relative file path, and an optional fully qualified class name. It does not render or write files.

These are the initial internal planning APIs, not the eventual versioned JSON import/export format. Later phases will extend the definitions deliberately.

## Collision checks

`Discovery\ResourceCollisionDetector::identities($candidate, $reserved)` compares planned resources for namespace, normalized view/test path, URL, and route-name collisions. Comparisons are case-insensitive for portability.

`artifacts($applicationRoot, $artifacts)` reports existing targets, file-blocked parent directories, symlink parents, case-insensitive path collisions, overlapping planned paths, duplicate planned classes, and existing autoloadable classes/interfaces/traits. Supply the intended artifacts before rendering; collision checks never create directories or write files.

This phase does not enumerate all existing Laravel routes or implement overwrite selection, route-file parsing, atomic writes, or time-of-check/time-of-use protection. The later generator must recheck its full artifact plan immediately before applying changes.

## Verification

The package's isolated tests cover nested model/enum discovery, duplicate basenames, constructor avoidance, inherited/recursive SoftDeletes, absent/empty folders, invalid source/autoload diagnostics, renamed/default identities, invalid names, operation selection, and filesystem/class collisions. Architecture checks permit only Eloquent Model/SoftDeletes metadata references in Discovery and reject database/query/persistence dependencies there. Definitions and Inference cannot delegate I/O to Discovery, Schema, or Support.

Run `composer test` in the package and docs projects after changes. See the [implementation evidence](IMPLEMENTATION_PLAN.md#8-execution-evidence-log) for versions and actual outcomes. No new browser behavior is introduced in Phase 2.

## Planned consumer breadcrumbs

The wizard will detect existing Diglactic Laravel Breadcrumbs route files and show planned registrations in review. Automatic writes belong to Phase 7; they are not implemented by the discovery/naming services. Page resources will register matching Index, Show, and selected Create/Edit trails; dialog resources only Index. Custom/multiple configured files, existing-name conflicts, safe writes, rollback, and publishable stubs follow the [implementation plan](IMPLEMENTATION_PLAN.md#optional-consumer-breadcrumb-integration).
