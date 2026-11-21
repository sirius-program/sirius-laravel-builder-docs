<?php

declare(strict_types=1);

test('password reset endpoints are unavailable', function (string $method, string $path): void {
    $this->call($method, $path)->assertNotFound();
})->with([
    ['GET', '/forgot-password'], ['POST', '/forgot-password'], ['GET', '/reset-password/token'], ['POST', '/reset-password'],
]);
