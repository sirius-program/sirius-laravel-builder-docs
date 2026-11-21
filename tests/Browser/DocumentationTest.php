<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Vite;

beforeEach(function (): void {
    Vite::useHotFile(__DIR__ . '/.unused-vite-hot');
});

it('navigates public documentation without authentication', function (): void {
    $page = visit('/')->resize(1440, 1000);
    $page->assertSee('Your models.')->assertSee('not yet available')
        ->assertNotPresent('a[href$="/login"]')->assertNotPresent('a[href$="/register"]')
        ->assertNoJavaScriptErrors()
        ->screenshot(filename: 'phase-1-home-light');

    $page->click('a[href$="/docs/getting-started"]')->assertPathIs('/docs/getting-started')->assertSee('A ready foundation.')
        ->click('Architecture')->assertPathIs('/docs/architecture')->assertSee('Clear responsibilities.')
        ->assertNoJavaScriptErrors();
});

it('persists the anonymous theme preference across navigation and reload', function (): void {
    $page = visit('/')->resize(1440, 1000);
    $page->script('localStorage.setItem("sirius-builder-theme", "light")');
    $page->refresh()->assertAttribute('[data-theme-toggle]', 'aria-pressed', 'false');
    $page->click('[data-theme-toggle]')->assertAttribute('[data-theme-toggle]', 'aria-pressed', 'true')
        ->assertAttributeContains('html[class]', 'class', 'dark')->screenshot(filename: 'phase-1-home-dark');
    $page->click('a[href$="/docs/getting-started"]')->assertAttributeContains('html[class]', 'class', 'dark');
    $page->refresh()->assertAttribute('[data-theme-toggle]', 'aria-pressed', 'true');
    $page->click('[data-theme-toggle]')->assertAttribute('[data-theme-toggle]', 'aria-pressed', 'false')
        ->assertNoJavaScriptErrors();
});

it('keeps homepage and documentation usable on a narrow screen', function (): void {
    $page = visit('/')->resize(375, 812);
    $page->assertSee('Your models.')->screenshot(filename: 'phase-1-home-mobile');
    expect($page->script('document.documentElement.scrollWidth <= window.innerWidth'))->toBeTrue();
    $page->click('a[href$="/docs/getting-started"]')->assertSee('A ready foundation.');
    expect($page->script('document.documentElement.scrollWidth <= window.innerWidth'))->toBeTrue();
    $page->assertNoJavaScriptErrors();
});

it('uses the Sirius sidebar and mobile navigation for documentation', function (): void {
    $page = visit('/docs')->resize(1440, 1000);
    $page->script('localStorage.setItem("sirius-builder-theme", "light")');
    $page->refresh()->assertVisible('aside[aria-label="Documentation sidebar"]')
        ->screenshot(filename: 'docs-sidebar-light');
    $page->click('aside a[href$="/docs/getting-started"]')->assertPathIs('/docs/getting-started');
    $page->click('[data-theme-toggle]')->assertAttributeContains('html[class]', 'class', 'dark')
        ->screenshot(filename: 'docs-sidebar-dark');

    $page->resize(375, 812)->click('[aria-label="Open navigation"]')
        ->assertVisible('#docs-navigation [data-docs-navigation]')
        ->screenshot(filename: 'docs-mobile-navigation');
    $page->click('#docs-navigation a[href$="/docs/architecture"]')
        ->assertPathIs('/docs/architecture')->assertSee('Clear responsibilities.')
        ->screenshot(filename: 'docs-mobile-content')->assertNoJavaScriptErrors();
    expect($page->script('document.documentElement.scrollWidth <= window.innerWidth'))->toBeTrue();
});

it('centers documentation and navigates breadcrumbs and back to top', function (): void {
    $page = visit('/docs/getting-started')->resize(1904, 900);
    $page->assertSeeIn('[aria-label="Breadcrumb"]', 'Getting started');
    expect($page->script('(() => { const container = document.querySelector("#docs-top").getBoundingClientRect(); const workspace = document.querySelector(".builder-docs-workspace").getBoundingClientRect(); return Math.abs((container.left + container.right) / 2 - (workspace.left + workspace.right) / 2) < 2; })()'))->toBeTrue();

    $page->click('a[href="#docs-top"]');
    expect($page->script('document.querySelector("#docs-top").getBoundingClientRect().top >= document.querySelector(".builder-docs-header").getBoundingClientRect().bottom'))->toBeTrue();
    $page->screenshot(filename: 'docs-centered-content');
    $page->click('[aria-label="Breadcrumb"] a[href$="/docs"]')->assertPathIs('/docs')
        ->assertSee('Build on your models.')->assertNoJavaScriptErrors();
});

it('copies exact multiline code and keeps copy feedback local to its block', function (): void {
    $page = visit('/docs/getting-started');
    $page->script('Object.defineProperty(navigator, "clipboard", {configurable: true, value: {writeText: async text => { window.copiedCode = text; }}})');

    $page->click('[data-docs-code]:nth-of-type(1) [data-copy-code]')->assertSeeIn('[data-docs-code]:nth-of-type(1) [data-copy-label]', 'Copied');

    expect($page->script('window.copiedCode'))->toBe("composer config repositories.sirius-builder path ../sirius-laravel-builder\ncomposer require sirius/laravel-builder:@dev");
    $page->assertSeeIn('[data-docs-code]:nth-of-type(2) [data-copy-code]', 'Copy')->assertNoJavaScriptErrors();
});

it('links to article sections and updates the in-page navigation after transitions', function (): void {
    $page = visit('/docs/getting-started')->resize(1904, 900);
    $page->assertVisible('[aria-label="On this page"]')->screenshot(filename: 'docs-on-this-page-desktop');
    expect($page->script('Array.from(document.querySelectorAll("[aria-label=\"On this page\"] a")).every(link => document.getElementById(link.hash.slice(1)))'))->toBeTrue();
    $page->click('[aria-label="On this page"] a[href="#publish-configuration"]');
    expect($page->script('location.hash'))->toBe('#publish-configuration');
    expect($page->script('document.querySelector("#publish-configuration").getBoundingClientRect().top >= document.querySelector(".builder-docs-header").getBoundingClientRect().bottom'))->toBeTrue();

    $page->click('aside a[href$="/docs/architecture"]')->assertSeeIn('[aria-label="On this page"]', 'Executable boundaries')
        ->assertNotPresent('[aria-label="On this page"] a[href="#publish-configuration"]');
    $page->resize(375, 812)->screenshot(filename: 'docs-on-this-page-mobile');
    expect($page->script('document.querySelector("[aria-label=\"On this page\"]").getBoundingClientRect().bottom <= document.querySelector("article").getBoundingClientRect().top'))->toBeTrue();
    $page->click('[aria-label="On this page"] a[href="#boundaries"]');
    expect($page->script('location.hash'))->toBe('#boundaries');
    expect($page->script('document.documentElement.scrollWidth <= window.innerWidth'))->toBeTrue();
    $page->assertNoJavaScriptErrors();
});

it('navigates without reloading and restores controls through browser history', function (): void {
    $page = visit('/')->resize(1440, 1000);
    $page->script('window.navigationMarker = "same-document"; window.copyCalls = 0; Object.defineProperty(navigator, "clipboard", {configurable: true, value: {writeText: async text => { window.copiedCode = text; window.copyCalls++; }}})');

    $page->click('a[href$="/docs/getting-started"]')->assertPathIs('/docs/getting-started');
    expect($page->script('window.navigationMarker'))->toBe('same-document');
    $page->click('aside a[href$="/docs/architecture"]')->assertSee('Clear responsibilities.')
        ->click('[data-copy-code]')->assertSeeIn('[data-copy-label]', 'Copied');
    expect($page->script('window.copiedCode'))->toBe('composer test');
    expect($page->script('window.copyCalls'))->toBe(1);

    $page->script('history.back()');
    $page->assertPathIs('/docs/getting-started')->assertSee('A ready foundation.');
    $page->script('history.forward()');
    $page->assertPathIs('/docs/architecture')->assertSeeIn('[data-copy-label]', 'Copy');
    expect($page->script('window.navigationMarker'))->toBe('same-document');
    $page->click('[data-copy-code]')->assertSeeIn('[data-copy-label]', 'Copied');
    expect($page->script('window.copyCalls'))->toBe(2);
    $page->click('aside a[aria-label="Sirius Builder for Laravel home"]')->assertPathIs('/')->assertSee('Your models.');
    expect($page->script('window.navigationMarker'))->toBe('same-document');
    $page->assertNoJavaScriptErrors();
});

it('selects code for manual copying when clipboard access is unavailable', function (bool $denied): void {
    $page = visit('/docs/architecture');
    $page->script($denied
        ? 'Object.defineProperty(navigator, "clipboard", {configurable: true, value: {writeText: async () => { throw new Error("Denied"); }}})'
        : 'Object.defineProperty(navigator, "clipboard", {configurable: true, value: undefined})');

    $page->click('[data-copy-code]')->assertSee('Select & copy');

    expect($page->script('window.getSelection().toString()'))->toBe('composer test');
    $page->assertSeeIn('[data-copy-status]', 'Clipboard unavailable')->assertNoJavaScriptErrors();
})->with([true, false]);
