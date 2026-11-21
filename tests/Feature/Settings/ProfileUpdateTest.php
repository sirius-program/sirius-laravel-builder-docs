<?php

declare(strict_types=1);

test('profile and account settings are unavailable', function (string $path): void {
    $this->get($path)->assertNotFound();
})->with(['/settings', '/settings/profile', '/settings/appearance']);
