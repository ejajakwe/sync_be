<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

use App\Console\Commands\CheckPendingTransactions;

Schedule::command(CheckPendingTransactions::class)->everyFiveMinutes();

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('sync:digiflazz', function () {
    Artisan::call('sync:digiflazz-products');
})->describe('Sinkronisasi produk Digiflazz');