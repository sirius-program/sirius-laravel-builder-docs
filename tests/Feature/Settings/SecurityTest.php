<?php

declare(strict_types=1);

test('security and verification endpoints are unavailable', function (string $method, string $path): void {
    $this->call($method, $path)->assertNotFound();
})->with([
    ['GET', '/settings/security'], ['GET', '/email/verify'], ['POST', '/email/verification-notification'],
    ['GET', '/user/confirm-password'], ['POST', '/user/confirm-password'], ['GET', '/two-factor-challenge'],
    ['POST', '/two-factor-challenge'], ['POST', '/user/two-factor-authentication'], ['GET', '/user/two-factor-qr-code'],
]);
