# Generated actions and policies

Select the operations in `php artisan make:crud` and review their targets under `app/Actions/{Resource}`. Generation reads schema metadata. Database mutations happen when the application invokes an action.

For a resource named Product with a writable name, an authorized caller can use the following code. Include all fields required by the reviewed rules.

```php
use App\Actions\Product\CreateAction;
use App\Actions\Product\UpdateAction;

$product = app(CreateAction::class)->handle(['name' => 'Desk']);
$product = app(UpdateAction::class)->handle($product->getKey(), ['name' => 'Standing desk']);
```

| Action | Input | Effect |
| --- | --- | --- |
| CreateAction | Field array | Returns the saved model |
| UpdateAction | Primary key and field array | Returns the updated model |
| DeleteAction | Primary key | Trashes a SoftDeletes record or deletes an ordinary record |
| RestoreAction | Primary key of a trashed record | Restores the record |
| ForceDeleteAction | Primary key of a trashed record | Permanently deletes the record |

Keys can be integers or strings. Pass the model primary key even when the URL binds a different route key. Each action uses the model connection and an Eloquent transaction. Existing-record actions query within global scopes, request a write lock, and check the applicable policy against the resolved row. Restore and permanent delete retain other global scopes. Model events and casts run normally. A cancelled mutation or an exception rolls back the transaction. Side effects outside the database belong in after-commit listeners.

## Validation and mass assignment

For resources with Create or Update, edit `app/Validation/{Resource}Validation.php` to customize the rules, field labels and messages. Generated Livewire components use the standard `rules()`, `messages()` and `validationAttributes()` hooks. Page fields validate on blur with `validateOnly()`. Call `validateForm()` for a whole-form check. Livewire uses error keys such as `form.email`. A direct action call validates the same input with keys such as `email` after authorization. Update uniqueness uses the scoped record resolved from its primary key. The generated `messages()` method returns an empty array by default. Laravel resolves built-in messages for the active locale, including application overrides in `lang/{locale}/validation.php`. Sirius phone rules use Sirius UI translations. Add entries to `messages()` only for resource-specific overrides.

Generated actions pass explicit attribute arrays to Eloquent `create()` and `update()`. Missing optional attributes keep their database or model defaults on Create and their current values on Update. The wizard offers all supported columns and flags those for which the model’s `isFillable()` returns false. Maintain `$fillable` or `$guarded` on the model and rerun the wizard after changing these settings. At runtime the generated actions use Eloquent’s configured mass-assignment behavior. Delete calls `delete()` on the authorized, locked instance. Restore and permanent deletion call `restore()` and `forceDelete()` on a trashed instance.

Each generated action catches database exceptions outside its transaction. The caller receives an operation-specific exception with the database exception as its cause. Validation, authorization and missing-record exceptions retain their normal Laravel behavior. A cancelled model event causes rollback.

## Configure access

The default protected resource checks `create`, `update`, `delete`, `restore`, or `forceDelete` inside its action. A new policy initially returns false for each generated ability, including `viewAny` and `view`. Customize the appropriate methods, including tenant or ownership checks, before expecting successful writes. Existing policies are preserved. Register unconventional policy locations in the consuming application. Explicitly disabling policy enforcement leaves access control to the caller.

## Fields and fixtures

Actions apply the reviewed rules, then assign only selected writable columns. Unknown input keys cannot change other attributes. Confirmation values are used only for validation. An empty or omitted Update password keeps the existing hash. A new password must match its confirmation. The action honors Laravel's hashed cast or verifies a password mutator's output against the submitted value. Use a compatible application hasher. Password fields are excluded from read values.

JSON text is validated and decoded for supported model casts. Uncast JSON uses its validated string. Supply decimal values as strings, such as `'1299.90'`. Database storage precision depends on the database type. Richtext uses HTML Purifier with a formatting and link allowlist. Images, scripts, style attributes and event attributes are excluded. The read-value helper sanitizes legacy richtext too. Custom model casts, database defaults, foreign records and tenant scopes should be represented in the consuming application's fixtures. The wizard reports omitted required columns for review. Supply those through the model or select them before generating a Create action.

When upgrading a local path installation, run `composer update sirius/laravel-builder` in the consuming project to install the HTML Purifier dependency. Regenerate previously scaffolded actions and review each overwrite. Generated Index/detail and mutation screens are covered in subsequent phases.
