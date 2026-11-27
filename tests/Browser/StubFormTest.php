<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Vite;
use Livewire\Livewire;
use Tests\Fixtures\StubForm;

it('binds rendered Sirius fields, shows validation, and clears canonical widget values', function (): void {
    Vite::useHotFile(__DIR__ . '/.unused-vite-hot');
    Livewire::component('stub-form', StubForm::class);
    Route::get('/_test/stub-form', static fn (): string => Blade::render('<x-docs-layout><livewire:stub-form /></x-docs-layout>'));
    $page = visit('/_test/stub-form');
    foreach (['email', 'url', 'password', 'password_confirmation', 'phone', 'number', 'switch', 'textarea', 'richtext', 'select', 'date', 'datetime', 'time'] as $name) {
        $page->assertPresent('label[for="product-' . $name . '"]');
    }
    $page->click('Save')->assertAttribute('#product-title', 'aria-invalid', 'true');
    $page->fill('#product-title', 'Example')->fill('#product-price', '12.34')->click('Save')->assertSee('12.34');
    $page->click('Clear')->assertValue('#product-title', '')->assertValue('#product-price', '')
        ->fill('#product-title', 'Again')->fill('#product-price', '4.56')->click('Save')->assertSee('4.56')->assertNoJavaScriptErrors();
});
