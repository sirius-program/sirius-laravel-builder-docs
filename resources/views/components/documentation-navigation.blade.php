@props(['section'])
<x-sirius::menu label="Documentation" data-docs-navigation>
    <x-sirius::menu.category title="Getting Started" class="builder-docs-nav-section">
        <x-sirius::menu.item icon="heroicon-s-rocket-launch" :link="route('started.introduction')" wire:navigate :active="$section === 'started.introduction'">Introduction</x-sirius::menu.item>
        <x-sirius::menu.item icon="heroicon-s-clipboard-document-list" :link="route('started.installation')" wire:navigate :active="$section === 'started.installation'">Installation</x-sirius::menu.item>
        <x-sirius::menu.item icon="heroicon-s-square-3-stack-3d" :link="route('started.architecture')" wire:navigate :active="$section === 'started.architecture'">Architecture</x-sirius::menu.item>
    </x-sirius::menu.category>
    <x-sirius::menu.category title="Resources" class="builder-docs-nav-section">
        <x-sirius::menu.item icon="heroicon-o-code-bracket" link="https://github.com/sirius-program/sirius-laravel-builder" target="_blank">Source repository</x-sirius::menu.item>
        <x-sirius::menu.item icon="heroicon-o-sparkles" link="https://github.com/sirius-program/sirius-ui" target="_blank">Sirius UI</x-sirius::menu.item>
    </x-sirius::menu.category>
</x-sirius::menu>
