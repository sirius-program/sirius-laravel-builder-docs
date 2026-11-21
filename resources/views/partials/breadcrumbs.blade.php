@unless ($breadcrumbs->isEmpty())
    <x-sirius::breadcrumb label="Breadcrumb" class="flex-wrap">
        @foreach ($breadcrumbs as $breadcrumb)
            <x-sirius::breadcrumb.item :link="$breadcrumb->url && ! $loop->last ? $breadcrumb->url : null" :current="$loop->last" wire:navigate>{{ $breadcrumb->title }}</x-sirius::breadcrumb.item>
        @endforeach
    </x-sirius::breadcrumb>
@endunless
