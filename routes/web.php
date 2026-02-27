<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\LandingPageController;
use App\Http\Controllers\Api\PublicWebsiteController;

Route::get('/', [LandingPageController::class, 'index'])->name('home');
Route::get('/politica-confidentialitate', [LandingPageController::class, 'privacy'])->name('privacy');
Route::get('/termeni-si-conditii', [LandingPageController::class, 'terms'])->name('terms');

Route::get('/website-config', [PublicWebsiteController::class, 'getWebsiteConfig'])->name('public.website');
Route::get('/public/website', [PublicWebsiteController::class, 'getWebsiteConfig']); // Legacy compatibility

// Emergency Route for Shared Hosting (Zero-Terminal)
Route::get('/force-migrate', function() {
    try {
        \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        \Illuminate\Support\Facades\Artisan::call('optimize:clear');
        return "<h1>Success!</h1><p>Migrations run and cache cleared.</p><pre>" . \Illuminate\Support\Facades\Artisan::output() . "</pre>";
    } catch (\Exception $e) {
        return "<h1>Error</h1><pre>" . $e->getMessage() . "</pre>";
    }
});
