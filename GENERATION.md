# Generate resource files

In a Laravel application with a configured Livewire page layout, run the wizard against a model whose table already exists:

~~~shell
php artisan make:crud --dry-run
~~~

Review the proposed GET routes, breadcrumb trails, and each file marked Create, Update, or Unchanged. Choose **Generate** to finish the dry run, or **Cancel** to exit. To apply the plan, run **php artisan make:crud** interactively and choose **Generate** after review. Both modes build the same manifest using your published stub overrides.

Generation writes Livewire pages, Blade views, action scaffolds, a table subclass, a Pest feature test, and the approved route changes. The wizard can also scaffold a deny-by-default policy and a browser test. Pages use the host application's configured Livewire page layout. Page mode includes Index and detail, with Create/Edit routes for the selected operations. Dialog mode registers Index only. The Phase 7 table returns an empty collection. The generated action entry points fail explicitly until persistence is implemented. Dialog interaction and fully functional CRUD screens follow in later phases.

## Repeated runs and conflicts

Builder owns one marked block per resource in the configured web route file and, when selected, the existing Diglactic breadcrumb file. Route names and trail parents use the reviewed prefix. Detail and edit trails accept the bound model. A repeated run replaces the existing block in place and leaves unrelated PHP untouched. Default to keeping local changes: an identical file requires no approval, while each differing generated file requires its own explicit overwrite answer. Declining one cancels the entire write. Changing operation sets or switching layouts updates registrations. Previously generated standalone files outside the new manifest are retained for manual review.

A collision with another registered route or trail blocks the write. Use a distinct path or name and review again. Clear cached routes before applying changes. A checksum mismatch means the managed block was edited. Compare it with your version control history, move any application-specific registrations outside the managed block, and restore the last generated block before retrying. Use ordinary top-level PHP route files. The writer refuses unsupported structures such as namespace declarations, closing PHP tags, and top-level returns.

## Recovery

Builder validates PHP and compiled Blade in memory, stages changed files beside their destinations, and saves original bytes before replacing them. It rechecks the reviewed contents and confines output to the application root. A second generator targeting the same project must wait for the current run to finish. If files change after review, restart and inspect the new plan.

On a recoverable failure, Builder restores replaced files in reverse order and removes newly written files. If rollback encounters another edit or a filesystem error, the message identifies the affected path and retained backup. Stop other writers, compare the current file and backup, copy back the desired version, then rerun the dry run. For a newly created file without an original, remove it only after confirming it contains no independent changes. Multiple files are replaced individually; recovery after a process or machine crash may require comparing the working tree and adjacent staging files with version control.
