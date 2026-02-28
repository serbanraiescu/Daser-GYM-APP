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

        // 4. Permissions Fix
        $output .= "<h2>4. Permissions Fix</h2>";
        $storageAppPublic = storage_path('app/public');
        if (file_exists($storageAppPublic)) {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($storageAppPublic, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::SELF_FIRST
            );

            foreach ($iterator as $item) {
                if ($item->isDir()) {
                    chmod($item->getPathname(), 0755);
                } else {
                    chmod($item->getPathname(), 0644);
                }
            }
            chmod($storageAppPublic, 0755);
            $output .= "<p style='color:green'>Permissions fixed recursively for storage/app/public (Directories: 755, Files: 644).</p>";
        }

        // 5. Diagnostic
        $output .= "<h2>5. Diagnostic</h2>";
        $output .= "<ul>";
        $output .= "<li><b>Public Path:</b> " . public_path() . "</li>";
        $output .= "<li><b>Storage Link Path:</b> " . public_path('storage') . "</li>";
        $output .= "<li><b>Is Link:</b> " . (is_link(public_path('storage')) ? "YES" : "NO") . "</li>";
        $output .= "<li><b>Target Exists:</b> " . (file_exists(public_path('storage')) ? "YES" : "NO") . "</li>";
        $output .= "<li><b>Symlink Function Available:</b> " . (function_exists('symlink') ? "YES" : "NO") . "</li>";
        $output .= "</ul>";

        return $output;
    } catch (\Exception $e) {
        return "<h1>Error</h1><pre>" . $e->getMessage() . "</pre>";
    }
});
