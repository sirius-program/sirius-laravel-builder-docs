<x-docs-layout title="Installation" section="started.installation"
    :navigation="[
        'requirements' => 'Requirements',
        'install-package' => 'Install the development package',
        'check-application' => 'Check your application',
        'publish-configuration' => 'Publish configuration',
        'asset-setup' => 'Asset setup'
    ]"
>
    <x-header
        section="Getting Started"
        title="Installation"
        lead="A ready foundation for you to build you own Laravel app.">
    </x-header>
    
    <p>Try the prerequisite checks in a compatible Laravel application. The development package does not generate CRUD files yet.</p>

    <h2 id="requirements" tabindex="-1">Requirements</h2>
    <ul>
        <li>PHP 8.3 or newer</li>
        <li>Laravel ^12.61.1 or ^13.12.0</li>
        <li>Livewire 4 and Sirius UI ^0.1.0</li>
        <li>A configured database connection and an existing models directory</li>
    </ul>

    <h2 id="install-package" tabindex="-1">Install the development package</h2>
    <p>Until a Builder release is published, clone the repository next to your Laravel application and add it as a local Composer repository. Run these commands from the application:</p>
    @include('partials.code.install-dev-package')
    <p>Laravel discovers the provider automatically. Sirius UI is installed as a dependency; configure its assets before using its components in your application.</p>
    
    <h2 id="check-application" tabindex="-1">Check your application</h2>
    @include('partials.code.make-crud-check')
    <p>A successful check returns exit code 0. Invalid paths, missing dependencies, or an unavailable connection return exit code 1 with a diagnostic. The check connects to the default database without querying records. The interactive wizard then inspects the selected model on its own connection.</p>
    <p>Running <code>make:crud</code> without <code>--check</code> opens the interactive resource wizard. Review the planned files, routes, validation, and breadcrumbs. Choose Cancel to exit successfully.</p>
    
    <h2 id="publish-configuration" tabindex="-1">Publish configuration</h2>
    @include('partials.code.publish-config')
    <p>Edit <code>config/sirius-builder.php</code>. Paths are relative to the application root. Defaults are <code>app/Models</code>, <code>app/Enums</code>, <code>stubs/sirius-builder</code>, and <code>routes/web.php</code>. Enum and stub directories are optional at this stage. Re-publishing preserves your configuration unless you explicitly use <code>--force</code>.</p>

    <p>To customize the generator templates, run <code>php artisan vendor:publish --tag=sirius-builder-stubs</code>. Edit the individual templates under <code>stubs/sirius-builder</code>. Publishing again preserves existing files unless you pass <code>--force</code>.</p>

    <h2 id="asset-setup" tabindex="-1">Asset setup</h2>
    <p>With Vite, import <code>vendor/sirius/ui/dist/sirius.css</code> into your app CSS and <code>vendor/sirius/ui/dist/sirius.js</code> into your app JavaScript using paths relative to those files. Load Livewire assets once and run your frontend build.</p>

    <div class="flex justify-between mt-10">
        <x-sirius::link :href="route('started.introduction')" wire:navigate>← Introduction</x-sirius::link>
        <x-sirius::link :href="route('started.architecture')" wire:navigate>Architecture →</x-sirius::link>
    </div>
</x-docs-layout>
