@props(['language' => 'Shell', 'source'])
<div class="builder-docs-code" data-docs-code>
    <div class="builder-docs-code-header">
        <span>{{ $language }}</span>
        <x-sirius::button variant="ghost" size="sm" data-copy-code disabled aria-label="Copy code"><span data-copy-label>Copy</span></x-sirius::button>
        <span class="sr-only" role="status" data-copy-status></span>
    </div>
    <pre tabindex="0" aria-label="{{ $language }} code example"><x-sirius::code block :text="$source" /></pre>
</div>
