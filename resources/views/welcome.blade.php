<x-docs-layout>
    <section class="hero site-width">
        <div class="hero-copy">
            <div class="release-line"><span class="status-dot"></span> FOUNDATION MILESTONE <span class="release-divider">/</span> IN DEVELOPMENT</div>
            <h1>Your models.<br><span>Less boilerplate.</span></h1>
            <p class="hero-description">A head start for your next Laravel resource. Action-based CRUD, Livewire interactions, and Sirius UI components — designed around the models you already have.</p>
            <div class="hero-actions">
                <x-sirius::button as="a" :href="route('started.installation')" wire:navigate variant="primary" size="lg">Get started <span aria-hidden="true">→</span></x-sirius::button>
                <x-sirius::button as="a" :href="route('started.architecture')" wire:navigate variant="outline" size="lg">Explore the architecture</x-sirius::button>
            </div>
            <p class="availability-note">Available now: interactive resource planning and review.<br>Generate Livewire scaffolds and register their routes after review.</p>
            <div class="stack-strip"><span>PHP 8.3+</span><i></i><span>Laravel 12 / 13</span><i></i><span>Livewire 4</span></div>
        </div>
        <div class="hero-visual">
            <div class="terminal">
                <div class="terminal-bar"><span class="terminal-dots" aria-hidden="true">● ● ●</span><span>your-laravel-app</span><span class="terminal-shell">zsh</span></div>
                <div class="terminal-content">
                    <div class="terminal-comment"># Start with a ready foundation</div>
                    <div class="terminal-command"><span>$</span> php artisan make:crud --check</div>
                    <div class="terminal-output"><span>✓</span> CRUD prerequisites are ready.</div>
                    <div class="terminal-muted">No files or database records were changed.</div>
                    <div class="terminal-separator"></div>
                    <div class="terminal-comment"># Designed for a clear separation</div>
                    <div class="file-tree"><span>app/</span><br>├── Actions/ <em>business operations</em><br>├── Livewire/ <em>interactive screens</em><br>└── Models/ <em>your existing data</em></div>
                    <div class="terminal-prompt"><span>$</span> <i aria-hidden="true"></i></div>
                </div>
            </div>
            <div class="visual-caption"><x-sirius::icon name="heroicon-o-command-line" size="sm" /> One command. A deliberate architecture.</div>
        </div>
    </section>
    <section class="principles site-width" aria-labelledby="principles-title">
        <div class="section-heading"><p class="eyebrow">A FOUNDATION YOU CAN OWN</p><h2 id="principles-title">Familiar Laravel. Thoughtful defaults.</h2></div>
        <div class="principle-grid">
            <article><span class="principle-number">01 / STRUCTURE</span><h3>Actions at the center</h3><p>Keep validation, authorization, and persistence in focused actions. Let Livewire handle the interaction.</p><a href="{{ route('started.architecture') }}" wire:navigate>See the boundaries <span aria-hidden="true">↗</span></a></article>
            <article><span class="principle-number">02 / INTERFACE</span><h3>A consistent UI language</h3><p>Sirius UI provides the controls, colors, and light/dark themes. No second component system to maintain.</p><a href="https://github.com/sirius-program/sirius-ui">Meet Sirius UI <span aria-hidden="true">↗</span></a></article>
            <article><span class="principle-number">03 / CONFIDENCE</span><h3>Test the foundation first</h3><p>Executable architecture rules and isolated application tests protect the structure as the generator grows.</p><a href="{{ route('docs') }}" wire:navigate>Follow development <span aria-hidden="true">↗</span></a></article>
        </div>
    </section>
    <section class="roadmap-section site-width">
        <div><p class="eyebrow">THE ROAD AHEAD</p><h2>From model selection<br>to a resource of your own.</h2><p>Run the wizard to inspect your schema, configure fields and validation, and review the planned files and routes.</p></div>
        <div class="roadmap-details">
            <div><span>01</span><p><strong>Choose your model</strong><small>Discover nested models and inspect their schema.</small></p><x-sirius::badge variant="outline" size="sm">Available</x-sirius::badge></div>
            <div><span>02</span><p><strong>Choose your experience</strong><small>Separate pages or dialogs on the index page.</small></p><x-sirius::badge variant="outline" size="sm">Available</x-sirius::badge></div>
            <div><span>03</span><p><strong>Review, then generate</strong><small>Actions, components, views, routes, and Pest tests.</small></p><x-sirius::badge variant="outline" size="sm">Available</x-sirius::badge></div>
        </div>
    </section>
</x-docs-layout>
