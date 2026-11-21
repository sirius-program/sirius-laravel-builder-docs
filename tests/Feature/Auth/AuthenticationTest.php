<?php

declare(strict_types=1);

test('authentication endpoints are unavailable', function (string $method, string $path): void {
    $this->call($method, $path)->assertNotFound();
})->with([
    ['GET', '/login'], ['POST', '/login'], ['GET', '/register'], ['POST', '/register'], ['POST', '/logout'],
]);
