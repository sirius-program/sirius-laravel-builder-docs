@props(['title', 'navigation'])
<nav aria-label="On this page" class="builder-docs-toc">
    <p class="builder-docs-toc-title">On this page</p>
    <p class="builder-docs-toc-group">{{ $title }}</p>
    <ul>
        @foreach($navigation as $id => $label)
            <li><x-sirius::link :href="'#'.$id">{{ $label }}</x-sirius::link></li>
        @endforeach
    </ul>
</nav>
