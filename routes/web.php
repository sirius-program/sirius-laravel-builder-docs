<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::redirect('/docs', '/getting-started/introduction')->name('docs');

Route::view('/getting-started/introduction', 'docs.started.introduction')->name('started.introduction');
Route::view('/getting-started/installation', 'docs.started.installation')->name('started.installation');
Route::view('/getting-started/architecture', 'docs.started.architecture')->name('started.architecture');
