<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\LandingPageController;
use App\Http\Controllers\Api\PublicWebsiteController;

use App\Http\Controllers\PageController;

Route::get('/', [LandingPageController::class, 'index'])->name('home');
Route::get('/politica-confidentialitate', [LandingPageController::class, 'privacy'])->name('privacy');
Route::get('/termeni-si-conditii', [LandingPageController::class, 'terms'])->name('terms');

// Dynamic AIO Pages
Route::get('/p/{slug}', [PageController::class, 'show'])->name('pages.show');

Route::get('/website-config', [PublicWebsiteController::class, 'getWebsiteConfig'])->name('public.website');
Route::get('/public/website', [PublicWebsiteController::class, 'getWebsiteConfig']); // Legacy compatibility

// License Re-verification Route
Route::get('/admin/license/reverify', function() {
    app(\App\Services\LicenseService::class)->checkLicense(true);
    return redirect('/admin');
})->name('license.reverify');

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

        // 3. .htaccess update for Symlinks
        $output .= "<h2>3. .htaccess Patch</h2>";
        $htaccessPath = public_path('.htaccess');
        if (file_exists($htaccessPath)) {
            $content = file_get_contents($htaccessPath);
            if (!str_contains($content, 'Options +FollowSymLinks')) {
                file_put_contents($htaccessPath, "Options +FollowSymLinks\n" . $content);
                $output .= "<p style='color:green'>Added <b>Options +FollowSymLinks</b> to .htaccess</p>";
            } else {
                $output .= "<p>Already has FollowSymLinks.</p>";
            }
        }

        // 3. Cache Clear
        $output .= "<h2>3. Cache Clearing</h2>";
        \Illuminate\Support\Facades\Artisan::call('optimize:clear');
        $output .= "<pre>" . \Illuminate\Support\Facades\Artisan::output() . "</pre>";

        // 4. Permissions Fix
        $output .= "<h2>4. Permissions Fix</h2>";
        
        // Fix the root app directory and storage folder itself - CRITICAL for traversal
        @chmod(base_path(), 0755);
        @chmod(storage_path(), 0755);
        @chmod(storage_path('app'), 0755);

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
        $output .= "<li><b>App Dir Permissions:</b> " . substr(sprintf('%o', fileperms(base_path())), -4) . " (Recommended: 0755)</li>";
        $output .= "<li><b>Public Dir Permissions:</b> " . substr(sprintf('%o', fileperms(public_path())), -4) . " (Recommended: 0755)</li>";
        $output .= "</ul>";

        // Try listing a file if possible
        $output .= "<h3>Recent Assets</h3>";
        if (file_exists($target)) {
            $files = array_diff(scandir($target), array('.', '..'));
            $output .= "<ul>";
            foreach (array_slice($files, 0, 5) as $f) {
                $output .= "<li>$f</li>";
            }
            $output .= "</ul>";
        }
        return $output;
    } catch (\Exception $e) {
        return "<h1>Error</h1><pre>" . $e->getMessage() . "</pre>";
    }
});

// Aggressive Force Sync Route (V2)
Route::get('/fix-build', function() {
    $out = "<h3>Aggressive Sync & Build Fix (V2)</h3><pre>";

    $base = base_path();
    
    // 1. Force Git Restore on the Repo Build folder
    $out .= "<b>[GIT]</b> Forcing sync from origin...\n";
    $gitCmd = "cd {$base} && git fetch origin main 2>&1 && git reset --hard origin/main 2>&1 && git clean -fd 2>&1";
    $out .= shell_exec($gitCmd) . "\n";
    
    // Explicitly restore just in case
    $out .= shell_exec("cd {$base} && git restore public/build 2>&1") . "\n\n";

    // 2. Identify the Web Document Root
    $docRoot = $_SERVER['DOCUMENT_ROOT'] ?? __DIR__;
    $out .= "<b>[ENV]</b> Document Root: {$docRoot}\n";
    $out .= "<b>[ENV]</b> App Base: {$base}\n\n";

    $repoBuild = "{$base}/public/build";
    $liveBuild = "{$docRoot}/build";

    // 3. Verify Repo Build
    $out .= "<b>[VERIFY]</b> Repo Build exists ({$repoBuild}): " . (file_exists($repoBuild) ? "YES" : "NO") . "\n";
    if (file_exists($repoBuild)) {
        $out .= "Files: " . implode(", ", scandir($repoBuild)) . "\n\n";
    }

    // 4. Force copy from repo to live
    if (file_exists($repoBuild) && $repoBuild !== $liveBuild) {
        $out .= "<b>[COPY]</b> Syncing to {$liveBuild}...\n";
        
        // Remove old physical or symlink
        if (file_exists($liveBuild)) {
            if (is_link($liveBuild)) {
                unlink($liveBuild);
            } else {
                shell_exec("rm -rf {$liveBuild}");
            }
        }
        
        // Copy recursive
        $out .= shell_exec("cp -R {$repoBuild} {$liveBuild} 2>&1") . "\n";
        
        $out .= "Verify Live Build: " . (file_exists($liveBuild) ? "YES" : "NO") . "\n";
        if (file_exists($liveBuild)) {
            $out .= "Live Files: " . implode(", ", scandir($liveBuild)) . "\n";
        }
    } else {
        $out .= "\n<b>[SKIP]</b> Live folder is the same as Repo folder, no copy needed.\n";
    }

    $out .= "</pre><h3>Gata! Testeaza Landing Page-ul!</h3>";
    return $out;
});
