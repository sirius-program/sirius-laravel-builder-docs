<?php

declare(strict_types=1);

test('documentation pages are public and render Sirius UI', function (string $path, string $lead): void {
    $this->get($path)
        ->assertOk()
        ->assertSee($lead);
})->with([
    ['/', 'Your models.'],
    ['/getting-started/introduction', 'Get to know the core concepts behind Sirius Builder.'],
    ['/getting-started/installation', 'A ready foundation for you to build you own Laravel app.'],
    ['/getting-started/architecture', 'Clear responsibilities in every layer.'],
]);

test('the homepage distinguishes available checks from planned generation', function (): void {
    $this->get('/')->assertOk()->assertSee('make:crud --check')->assertSee('not yet available')->assertSee('Planned');
});
