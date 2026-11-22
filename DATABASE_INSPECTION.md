# Database inspection

Phase 3 provides `Sirius\LaravelBuilder\Schema\SchemaInspector`, a read-only library service that inspects the selected Eloquent model using its own connection. The interactive wizard and generation remain later phases.

## Inspect a selected model

```php
use App\Models\Client\Product;
use Sirius\LaravelBuilder\Schema\SchemaInspector;
use Sirius\LaravelBuilder\Support\ResourceNamer;

$selectedModel = new Product;
$table = $selectedModel->getTable();
$schema = app(SchemaInspector::class)->inspect($selectedModel, $table);
// $modelDefinition comes from recursive model discovery.
$identity = app(ResourceNamer::class)->identify($modelDefinition, $schema->table);
```

`getTable()` is the authoritative default: conventional names, custom `$table`, `setTable()`, and overridden resolution are supported. A different explicit table is rejected before connection access. Naming a resource differently does not change the table. Recursive discovery itself still does not instantiate models; the caller constructs only the selected model. Application model constructors/boot hooks are trusted application code, not sandboxed by the inspector.

The inspector uses the selected connection's Laravel schema builder (`getColumns`, `getIndexes`, `getForeignKeys`). It accepts `table` or `schema.table`; the framework applies the connection prefix exactly once and quotes identifiers. PostgreSQL enum/identity metadata is read from its catalog with a bound, grammar-quoted relation name. No table names are concatenated into raw inspection SQL.

## Metadata contract

All returned definitions are immutable and independent of Laravel/database services:

- `TableDefinition`: connection name, canonical Laravel driver, logical table reference, primary key, columns, indexes, foreign keys, and diagnostic warnings.
- `ColumnDefinition`: column name, normalized type, original native type, nullable flag, raw default, optional length/decimal precision/scale, unsigned flag, auto-increment status, generated status/expression, and optional enum values.
- `IndexDefinition`: name, ordered columns, unique/primary flags, and optional index type.
- `ForeignKeyDefinition`: optional name, ordered local/referenced columns, referenced schema/table, and update/delete actions.

Columns retain database order. Type aliases normalize to `string`, `integer`, `decimal`, `float`, `boolean`, `text`, `timestamp`, `datetime`, `time`, `date`, `json`, `uuid`, `binary`, or `enum`; unsupported types become `unknown` with a warning. For example, PostgreSQL `int8` and MySQL `bigint` both normalize to `integer`. `tinyint(1)` remains integer because it does not prove a boolean constraint. The native type is retained for subsequent decisions.

Generation expressions and auto-increment flags are separate metadata. PostgreSQL identity columns are both auto-increment and generated. Later field inference must keep all database-generated or auto-increment values out of writable fields.

## Engine differences and limitations

| Engine | Inspection behavior |
| --- | --- |
| MySQL / MariaDB | Framework-backed catalog inspection, native enum values (including quotes, commas, and empty values), unsigned types, generated columns, indexes, foreign keys |
| PostgreSQL | Framework metadata plus ordered native enum labels and identity metadata; schema-qualified tables and connection prefixes supported |
| SQLite | Actual in-memory schema inspection; generated columns, keys, indexes, and foreign keys supported; declared affinity may omit lengths/precision |

Defaults retain the database's original literal/expression representation; they are not evaluated or silently coerced into application values. A null metadata default does not distinguish no default from explicit `DEFAULT NULL`.

SQLite CHECK constraints are not reverse-engineered into enums. Laravel may represent SQLite string/decimal declarations without their original length/precision. PostgreSQL domains, arrays, and other unsupported types require explicit follow-up configuration rather than guessed field mappings. Native PostgreSQL enum types are supported; a domain over an enum is not treated as a native enum.

Index column lists alone do not describe partial predicates, expression indexes, included columns, or MySQL prefix lengths. Every result includes a diagnostic prohibiting unconditional uniqueness inference from those lists alone. Phase 4 must obtain sufficient evidence or require explicit configuration before generating such validation.

Missing/composite primary keys and a model key that disagrees with the database key are rejected. Missing tables, invalid table references, unsupported drivers, and unavailable connections produce `SchemaInspectionException`. Connection exceptions are sanitized rather than including credentials, raw DSNs, or their original messages.

## Tests and local configuration

Without a process-level `BUILDER_TEST_DRIVER`, ordinary `composer test` remains isolated in SQLite memory and does not load `.env.database`. Set that selector to run the full gate on a disposable external engine.

Run the opt-in suite separately:

```shell
composer test:database
```

The standalone schema suite and explicitly selected external-engine full runs load the owner-managed package-root `.env.database`; process/CI environment values take precedence. Without a selector, the standalone schema suite requires `BUILDER_TEST_MYSQL_*`, `BUILDER_TEST_MARIADB_*`, and `BUILDER_TEST_PGSQL_*` variables. With a selector, only the chosen external engine credentials are required. Missing/invalid setup fails instead of silently skipping an engine. See [SETUP.md](SETUP.md#phase-3-local-database-test-configuration) for the variables. The owner must use disposable local test databases. Loopback hosts are enforced and system databases are rejected. Names may differ from the documentation's `sirius_builder_test` example.

Tests use unique per-run table/schema names, track their created objects, and clean only those objects. They never drop a database. The suite runs serially, exercises all three configured engines plus SQLite, and compares rows, columns, indexes, and foreign keys before/after inspection. Query-log assertions reject writes during the inspection itself. Coverage includes prefixes, custom keys, enum punctuation, and PostgreSQL schema/identity behavior. Ordinary tests cover mismatches, missing tables/keys, unsupported metadata, invalid identifiers, and sanitized connection errors.

The GitHub Actions `database-matrix` has SQLite, MySQL, MariaDB, and PostgreSQL entries. Every entry runs `composer test`: formatting, static analysis, Rector, architecture/unit/feature suites, and selected-engine schema tests. Feature actions and Livewire actually persist through the selected driver. Each test receives a unique table prefix and cleanup removes only that prefix. Cases explicitly testing SQLite behavior retain their own SQLite connection. CI provisions MySQL 8.4, MariaDB 11.4, and PostgreSQL 17 with disposable service credentials. This job's execution must be verified in GitHub; local tests alone do not establish those versions or the complete framework matrix.

API reference: [Laravel database inspection](https://laravel.com/framework/docs/database#inspecting-your-databases). Actual tested versions/results are recorded in the [implementation evidence](IMPLEMENTATION_PLAN.md#8-execution-evidence-log).

## Run the full gate for one connection

Set the selector in the shell, not in an environment file; the test bootstrap chooses whether to load the owner's database configuration from this selector.

```powershell
$env:BUILDER_TEST_DRIVER = 'pgsql' # sqlite, mysql, mariadb, or pgsql
composer test
Remove-Item Env:BUILDER_TEST_DRIVER
```

On Bash, use `BUILDER_TEST_DRIVER=pgsql composer test`. This executes the full ordinary suite on the selected connection, then runs the matching schema acceptance cases. No silent fallback to SQLite occurs for a requested external engine. The independent framework/PHP compatibility matrix remains separate from this database matrix.
