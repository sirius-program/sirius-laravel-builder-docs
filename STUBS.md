# Customize generated templates

Run this in the Laravel application that requires Builder:

~~~shell
php artisan vendor:publish --tag=sirius-builder-stubs
~~~

Open the generated files in **stubs/sirius-builder** (configurable through **sirius-builder.paths.stubs**). Each file overrides only its matching package template. To use the shipped version again, remove that individual override. Publishing again fills in missing files and keeps existing ones. Laravel's **--force** replaces customized files.

For example, wrap the content of **view.stub** in an article:

~~~blade
<article class="resource-page">
    %{content}%
</article>
~~~

The interactive wizard previews the plan. Generate applies the reviewed templates and route registrations. See [generation and recovery](GENERATION.md). Publishing writes only the template files.

## Template contract (version 1)

Use the exact placeholders below, including the surrounding **%{** and **}%**. Each is required and may be repeated. Rendering substitutes values once and preserves Blade expressions in the template. Unknown or malformed tokens and missing inputs raise errors. PHP templates are syntax-checked after substitution. Override files are trusted application source.

| File | Placeholders and context |
| --- | --- |
| action.stub | namespace and class are PHP identifiers. parameters is the method parameter list, return_type the PHP return type, and body the handle method statements. |
| livewire.stub | namespace and class identify the component. members contains its PHP members. view is the quoted PHP expression for the view name. |
| view.stub | content is rendered Blade markup inside the root element. |
| pest.stub | description is a quoted PHP string and body contains Pest assertions. |
| breadcrumb.stub | name is the quoted route name, parameters extend the callback after the trail argument (including a leading comma when present), and body defines the trail. This is a PHP fragment. |
| field.stub | namespace and component select the Sirius Blade tag. id also supplies the stable wire key. binding is the Livewire property path. label is a PHP string expression and attributes contains the control-specific Blade attributes. |

After upgrading Builder, compare each override with its counterpart in **vendor/sirius/laravel-builder/stubs** and incorporate any new placeholders. Compare before force-publishing.

## Field examples

A password named **secret** in scope **product** binds to **form.secret** and **form.secret_confirmation** with distinct IDs. Its confirmation uses the wizard label. Initialize both properties empty when editing a record. Selects render enum options as value/label records and keep string IDs, including leading zeros. A remote select receives **search-url** only when the developer supplies an endpoint.

Currency exposes an unformatted decimal string. Date/time fields use the Sirius datetime picker. Sirius UI 0.1.0 accepts a limited set of input types, so email and URL use its text input with the corresponding inputmode and field validation. The renderer uses the configured Blade namespace and explicit error keys. Give each mounted form a distinct stable scope when rendering the same resource more than once.
