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

## Configure access

The default protected resource checks `create`, `update`, `delete`, `restore`, or `forceDelete` inside its action. A new policy initially returns false for each generated ability, including `viewAny` and `view`. Customize the appropriate methods, including tenant or ownership checks, before expecting successful writes. Existing policies are preserved. Register unconventional policy locations in the consuming application. Explicitly disabling policy enforcement leaves access control to the caller.

## Fields and fixtures

Actions apply the reviewed rules, then assign only selected writable columns. Unknown input keys cannot change other attributes. Confirmation values are used only for validation. An empty or omitted Update password keeps the existing hash. A new password must match its confirmation. The action honors Laravel's hashed cast or verifies a password mutator's output against the submitted value. Use a compatible application hasher. Password fields are excluded from read values.

JSON text is validated and decoded for supported model casts. Uncast JSON uses its validated string. Supply decimal values as strings, such as `'1299.90'`. Database storage precision depends on the database type. Richtext uses HTML Purifier with a formatting and link allowlist. Images, scripts, style attributes and event attributes are excluded. The read-value helper sanitizes legacy richtext too. Custom model casts, database defaults, foreign records and tenant scopes should be represented in the consuming application's fixtures. The wizard reports omitted required columns for review. Supply those through the model or select them before generating a Create action.

When upgrading a local path installation, run `composer update sirius/laravel-builder` in the consuming project to install the HTML Purifier dependency. Regenerate previously scaffolded actions and review each overwrite. Generated Index/detail and mutation screens are covered in subsequent phases.
