<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Storage;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Paksa semua URL jadi HTTPS jika di production
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        // Override Storage::url() agar selalu pakai APP_URL
        Storage::macro('url', function ($path) {
            $appUrl = rtrim(config('app.url'), '/'); // Ambil APP_URL dari .env
            return $appUrl . '/storage/' . ltrim($path, '/');
        });
    }
}
