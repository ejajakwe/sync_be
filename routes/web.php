<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/_once/storage-link', function () {
    abort_unless(app()->environment('production'), 403);
    \Illuminate\Support\Facades\Artisan::call('storage:link');
    return 'storage:link done';
});