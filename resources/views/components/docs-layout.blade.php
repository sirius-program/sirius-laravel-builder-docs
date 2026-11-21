@props(['title' => 'Build on your models', 'section' => null, 'navigation' => []])

@if($section !== null)
    <x-documentation-shell :title="$title" :section="$section" :navigation="$navigation">{{ $slot }}</x-documentation-shell>
@else
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
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body>
        <a href="#main" class="skip-link">Skip to content</a>
        <header class="site-header">
            <div class="site-width header-inner">
                <a class="brand" href="{{ route('home') }}" wire:navigate aria-label="Sirius Builder for Laravel home">
                    <span class="brand-mark"><x-sirius::icon name="heroicon-o-square-3-stack-3d" size="lg" /></span>
                    <span>Sirius <strong>Builder</strong><small>FOR LARAVEL</small></span>
                </a>
                <nav aria-label="Main navigation" class="main-nav">
                    <a href="{{ route('docs') }}" wire:navigate @if($section !== null) aria-current="page" @endif>Documentation</a>
                    <a href="https://github.com/sirius-program/sirius-laravel-builder" class="source-link">GitHub <span aria-hidden="true">↗</span></a>
                    <x-sirius::button variant="ghost" size="sm" data-theme-toggle aria-label="Toggle color theme" aria-pressed="false">
                        <x-sirius::icon name="heroicon-o-sun" /><span class="theme-label">Theme</span>
                    </x-sirius::button>
                </nav>
            </div>
        </header>
        <main id="main">{{ $slot }}</main>
        <footer class="site-footer site-width">
            <span>Sirius Builder for Laravel <span class="footer-dot">/</span> Built with <a href="https://github.com/sirius-program/sirius-ui">Sirius UI</a><span class="footer-dot">/</span>Developed with ❤️ by <a href="https://fathulhusnan.com/">Fathul Husnan</a></span>
            <span>Open source · MIT license</span>
        </footer>
        @livewireScripts
    </body>
    </html>
@endif
