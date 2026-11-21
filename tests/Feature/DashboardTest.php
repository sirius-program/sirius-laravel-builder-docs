<?php

declare(strict_types=1);

test('the private dashboard is removed rather than redirecting to login', function (): void {
    $this->get('/dashboard')->assertNotFound();
});
