<?php

declare(strict_types=1);

use Diglactic\Breadcrumbs\Breadcrumbs;
use Diglactic\Breadcrumbs\Generator as BreadcrumbTrail;

Breadcrumbs::for('home', function (BreadcrumbTrail $trail): void {
    $trail->push('Home', route('home'));
});

Breadcrumbs::for('started', function (BreadcrumbTrail $trail): void {
    $trail->parent('home');
    $trail->push('Getting Started');
});

Breadcrumbs::for('started.introduction', function (BreadcrumbTrail $trail): void {
    $trail->parent('started');
    $trail->push('Introduction', route('started.introduction'));
});

Breadcrumbs::for('started.installation', function (BreadcrumbTrail $trail): void {
    $trail->parent('started');
    $trail->push('Installation', route('started.installation'));
});

Breadcrumbs::for('started.architecture', function (BreadcrumbTrail $trail): void {
    $trail->parent('started');
    $trail->push('Architecture', route('started.architecture'));
});
