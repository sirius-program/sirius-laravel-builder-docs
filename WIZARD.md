# Interactive CRUD wizard

Run `php artisan make:crud` in an interactive terminal inside your Laravel application. Use `--check` for a noninteractive prerequisite check.

1. Search the discovered Eloquent models, including names such as `Client\\Product`. Accept the model-relative resource name or enter another PHP identifier. The table defaults to the selected model's `getTable()` on its own connection.
2. Choose columns, labels, and suggested field types. For a password, accept **Confirm {label}** or enter your own confirmation label. For a select, enter a same-origin endpoint or leave it blank to configure later. Choosing a compatible PHP enum supplies the options from its cases.
3. Accept or edit shared, Create, and Update validation rules using pipes. The wizard retains structured constraints such as uniqueness and foreign keys.
4. Choose pages or dialogs, then select write operations. Index and detail are always available. Page mode previews separate GET routes. Dialog mode previews one Index URL. Soft-deleting models additionally offer restore and permanent delete.
5. Review the URL and route-name prefixes, authentication, policy, optional policy scaffold, and browser tests. When Diglactic has several existing breadcrumb files, choose the target. The review shows planned trails and their parents.
6. Inspect the proposed routes, files, conflicts, and fields. **Edit a section** revisits an answer group. **Restart** returns to model selection. **Cancel** exits successfully. **Generate** applies the reviewed files and routes. Use **php artisan make:crud --dry-run** to preview only. See [file generation and recovery](GENERATION.md). Answers exist only for the current session.

On native Windows, the Laravel Prompts fallback presents choices through Symfony Console. Enter multiple choices as a comma-separated list. Use WSL for the rich keyboard interface. Add `--no-ansi` for plain output.

## Reading the review

Read the sections in order: **Resource**, **Access**, **Fields & validation**, **Routes & breadcrumbs**, and **Planned files**. When there are schema warnings, omitted required columns, a blocking policy, or conflicts, **Warnings & conflicts** lists them at the end. Each field groups its Create and Update rules, constraints, options, confirmation label, and notes together. Section headings are bold cyan and field names are bold. With `--no-ansi`, blank lines and indentation preserve the same grouping.
