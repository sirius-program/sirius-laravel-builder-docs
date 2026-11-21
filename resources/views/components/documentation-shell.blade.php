@props(['title', 'section', 'navigation' => []])
<!DOCTYPE html>
<html lang="en" class="sir-scrollbar">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Sirius Builder for Laravel: action-based Livewire CRUD scaffolding, powered by Sirius UI. Explore the package, its architecture, and development progress.">
    <title>{{ $title }} - Sirius Builder for Laravel</title>
    <script>
        (() => {
            let preference = null;
            try { preference = localStorage.getItem('sirius-builder-theme'); } catch {}
            const dark = preference === 'dark' || (preference !== 'light' && matchMedia('(prefers-color-scheme: dark)').matches);
            document.documentElement.classList.toggle('dark', dark);
            document.documentElement.style.colorScheme = dark ? 'dark' : 'light';
        })();
    </script>
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="builder-docs-shell">
    <x-sirius::link href="#main" class="skip-link">Skip to content</x-sirius::link>
    <aside class="builder-docs-sidebar" aria-label="Documentation sidebar">
        <div class="builder-docs-brand"><x-documentation-brand /></div>
        <div class="builder-docs-scroll"><x-documentation-navigation :section="$section" /></div>
        <div class="builder-docs-status"><x-sirius::badge variant="outline" size="sm">Foundation milestone</x-sirius::badge></div>
    </aside>
    <div class="builder-docs-workspace">
        <header class="builder-docs-header">
            <div class="builder-docs-heading">
                <x-sirius::button variant="ghost" icon="heroicon-o-bars-3" class="builder-docs-mobile-toggle" data-sir-dialog-open="docs-navigation" aria-controls="docs-navigation" aria-label="Open navigation" />
                <div>
                    <p class="builder-docs-title">{{ $title }}</p>
                    {{ \Diglactic\Breadcrumbs\Breadcrumbs::view('partials.breadcrumbs') }}
                </div>
            </div>
            <x-sirius::button variant="ghost" size="sm" data-theme-toggle disabled aria-label="Toggle color theme" aria-pressed="false" icon="heroicon-o-sun" />
        </header>
        <main id="main" class="builder-docs-main" tabindex="-1">
            <div id="docs-top" class="builder-docs-container" tabindex="-1">
                <div @class(['builder-docs-article-grid' => count($navigation) > 0])>
                    <article class="docs-content">{{ $slot }}</article>
                    @if(count($navigation) > 0)
                        <aside class="builder-docs-page-navigation">
                            <x-documentation-toc :title="$title" :navigation="$navigation" />
                        </aside>
                    @endif
                </div>
                <x-sirius::separator class="builder-docs-separator" />
                <footer class="builder-docs-footer">
                    <span>Sirius Builder for Laravel <span class="footer-dot">/</span> Built with <x-sirius::link href="https://github.com/sirius-program/sirius-ui">Sirius UI</x-sirius::link><span class="footer-dot">/</span>Developed with ❤️ by <x-sirius::link href="https://fathulhusnan.com/">Fathul Husnan</x-sirius::link></span>
                    <x-sirius::link href="#docs-top">Back to top</x-sirius::link></footer>
            </div>
        </main>
    </div>
    <x-sirius::slideover id="docs-navigation" side="left" size="sm" class="builder-docs-mobile-navigation" header-class="builder-docs-mobile-header" body-class="builder-docs-mobile-body">
        <x-slot:header><x-documentation-brand /></x-slot:header>
        <x-slot:body><x-documentation-navigation :section="$section" /></x-slot:body>
    </x-sirius::slideover>
    @livewireScripts
</body>
</html>
