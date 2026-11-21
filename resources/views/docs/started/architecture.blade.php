<x-docs-layout title="Architecture" section="started.architecture"
    :navigation="[
        'the-package' => 'The package',
        'generated-application' => 'The generated application',
        'boundaries' => 'Executable boundaries',
        'development-checks' => 'Development checks'
    ]"
>
    <x-header 
        section="Getting Started"
        title="Architecture"
        lead="Clear responsibilities in every layer."
    />

    <p class="lead">Generated resources will follow familiar Laravel conventions, with business operations kept in focused action classes.</p>

    <h2 id="the-package" tabindex="-1">The package</h2>
    <dl class="architecture-list">
        <div><dt>Commands</dt><dd>Coordinate the workflow and report results.</dd></div>
        <div><dt>Discovery &amp; Schema</dt><dd>Find model/enum definitions and inspect database metadata.</dd></div>
        <div><dt>Definitions &amp; Inference</dt><dd>Represent configuration and recommend field mappings without I/O.</dd></div>
        <div><dt>Generators</dt><dd>Render and write reviewed application files.</dd></div>
    </dl>
    
    <h2 id="generated-application" tabindex="-1">The generated application</h2>
    <p>Actions under <code>App\Actions</code> will authorize, validate, and persist data through a public <code>handle()</code> method. Livewire components manage UI state and delegate writes. Blade views render the interface without database queries.</p>
    <p>Consumer CRUD routes retain authentication and policy defaults. This public documentation website has no login or account features; that choice does not change generated application security.</p>
    
    <h2 id="boundaries" tabindex="-1">Executable boundaries</h2>
    <p>The test suite checks strict types, dependencies, action entry points, component inheritance, and direct persistence calls. Representative application fixtures exercise valid and rejected writes with an isolated SQLite database. These handwritten fixtures are examples, not output from a completed generator.</p>

    <h2 id="development-checks" tabindex="-1">Development checks</h2>
    @include('partials.code.test')
    <p>Run the complete gate after each phase. It includes formatting, static analysis, refactor checks, and Pest tests. Browser checks are also required when UI behavior changes.</p>

    <div class="flex justify-between mt-10">
        <x-sirius::link :href="route('started.installation')" wire:navigate>← Installation</x-sirius::link>
    </div>
</x-docs-layout>
