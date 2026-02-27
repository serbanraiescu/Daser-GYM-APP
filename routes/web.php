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
        $output = "<h1>System Maintenance</h1>";

        // 1. Run Migrations
        $output .= "<h2>1. Migrations</h2>";
        \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        $output .= "<pre>" . (\Illuminate\Support\Facades\Artisan::output() ?: "No new migrations.") . "</pre>";

        // 2. Storage Link
        $output .= "<h2>2. Storage Link</h2>";
        $publicStoragePath = public_path('storage');
        if (file_exists($publicStoragePath) && !is_link($publicStoragePath)) {
            $output .= "<p style='color:orange'>Warning: <b>{$publicStoragePath}</b> is a real directory, not a symlink. Please delete it via cPanel File Manager for the automatic link to work.</p>";
        } else {
            \Illuminate\Support\Facades\Artisan::call('storage:link');
            $output .= "<pre>" . \Illuminate\Support\Facades\Artisan::output() . "</pre>";
        }

        // 3. Cache Clear
        $output .= "<h2>3. Cache Clearing</h2>";
        \Illuminate\Support\Facades\Artisan::call('optimize:clear');
        $output .= "<pre>" . \Illuminate\Support\Facades\Artisan::output() . "</pre>";

        return $output;
    } catch (\Exception $e) {
        return "<h1>Error</h1><pre>" . $e->getMessage() . "</pre>";
    }
});
