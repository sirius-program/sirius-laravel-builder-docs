<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Vite;

it('finds the interactive wizard instructions from the homepage', function (): void {
    Vite::useHotFile(__DIR__ . '/.unused-vite-hot');
    visit('/')->assertSee('interactive resource planning')
        ->click('a[href$="/getting-started/installation"]')
        ->assertPathIs('/getting-started/installation')
        ->assertSee('opens the interactive resource wizard')
        ->assertNoJavaScriptErrors();
});
