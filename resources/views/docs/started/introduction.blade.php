<x-docs-layout title="Introduction" section="started.introduction"
    :navigation="[
        'available-today' => 'Available today',
        'planned-workflow' => 'Interactive resource workflow',
        'read-next' => 'Read next'
    ]"
>
    <x-header
        section="Getting Started"
        title="Introduction"
        lead="Get to know the core concepts behind Sirius Builder."
    />

    <p>Sirius Builder for Laravel is an evolving Laravel package for action-based Livewire CRUD, using Sirius UI.</p>
    <x-sirius::message variant="info">Run <code>php artisan make:crud</code> to configure a resource and review its files and routes. File writing follows in a later milestone.</x-sirius::message>
    
    <h2 id="available-today" tabindex="-1">Available today</h2>
    <ul>
        <li><code>php artisan make:crud --check</code> checks dependencies, configured paths, and the default database connection.</li>
        <li>Publishable configuration for discovery paths, stub overrides, and route defaults.</li>
        <li>Architecture tests, isolated application fixtures, and Sirius UI integration.</li>
    </ul>
    
    <h2 id="planned-workflow" tabindex="-1">Interactive resource workflow</h2>
    <p>Select an existing model, customize its resource and fields, choose a layout, and review the output. The wizard always includes Index and detail. Select the mutations to plan. Soft-deleting models also offer Restore and Permanent Delete. From review, edit a section, restart, or cancel.</p>
    <p>Generated output will include focused actions, Livewire classes, Blade views, named routes, and Pest tests. You can publish and customize the CRUD stubs now. Saved wizard configurations belong to a later milestone.</p>
    
    <h2 id="read-next" tabindex="-1">Read next</h2>
    <div class="doc-next">
        <x-sirius::link :href="route('started.installation')" wire:navigate>Set up the development package →</x-sirius::link>
        <x-sirius::link :href="route('started.architecture')" wire:navigate>Understand the architecture →</x-sirius::link>
    </div>
</x-docs-layout>
