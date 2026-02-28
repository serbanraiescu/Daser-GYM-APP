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

        // 2. Storage Link (Aggressive)
        $output .= "<h2>2. Storage Link (Aggressive)</h2>";
        $target = storage_path('app/public');
        
        // Potential link locations
        $links = [
            public_path('storage'),
            base_path('../public_html/storage'),
            base_path('public/storage'),
        ];

        foreach ($links as $link) {
            $output .= "<li>Checking: <code>$link</code>... ";
            if (file_exists($link)) {
                if (is_link($link)) {
                    $output .= "<span style='color:green'>Already a link.</span>";
                } else {
                    $output .= "<span style='color:orange'>Physical directory found. DELETING... </span>";
                    // Delete physical directory
                    $it = new RecursiveIteratorIterator(
                        new RecursiveDirectoryIterator($link, RecursiveDirectoryIterator::SKIP_DOTS),
                        RecursiveIteratorIterator::CHILD_FIRST
                    );
                    foreach($it as $file) {
                        if ($file->isDir()){ rmdir($file->getRealPath()); } else { unlink($file->getRealPath()); }
                    }
                    rmdir($link);
                    $output .= "<span style='color:red'>Deleted.</span>";
                }
            } else {
                $output .= "Not found. ";
            }

            // Create link if it doesn't exist
            if (!file_exists($link)) {
                if (@symlink($target, $link)) {
                    $output .= " <span style='color:green'>Linked successfully!</span>";
                } else {
                    $error = error_get_last();
                    $output .= " <span style='color:red'>Failed to link: " . ($error['message'] ?? 'Unknown error') . "</span>";
                }
            }
            $output .= "</li>";
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
