<?php
/**
 * Daser Quick Update - Server Side (Enhanced Debugging)
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

$secret_token = 'daser_gym_secure_2025';

function response($status, $message, $data = []) {
    die(json_encode(['status' => $status, 'message' => $message, 'data' => $data]));
}

if (!isset($_POST['token']) || $_POST['token'] !== $secret_token) {
    response('error', 'Unauthorized');
}

if (!class_exists('ZipArchive')) {
    response('error', 'ZipArchive extension is NOT enabled on this server.');
}

if (!isset($_FILES['update_zip'])) {
    response('error', 'No file uploaded. POST data size: ' . $_SERVER['CONTENT_LENGTH'] . ' bytes.');
}

$zip_file = $_FILES['update_zip']['tmp_name'];
$error_code = $_FILES['update_zip']['error'];

if ($error_code !== UPLOAD_ERR_OK) {
    response('error', 'Upload failed with PHP error code: ' . $error_code);
}

$zip = new ZipArchive;
$res = $zip->open($zip_file);

if ($res === TRUE) {
    $extract_path = __DIR__;
    
    if (!is_writable($extract_path)) {
        response('error', 'Extract path is not writable: ' . $extract_path);
    }

    if ($zip->extractTo($extract_path)) {
        // Fix permissions recursively to 0644 for files and 0755 for dirs
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($extract_path, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            if ($item->isDir()) {
                @chmod($item, 0755);
            } else {
                @chmod($item, 0644);
            }
        }
        
        // Final check for critical files
        @chmod(__DIR__ . '/index.php', 0644);
        @chmod(__DIR__ . '/.htaccess', 0644);
        @chmod(__DIR__ . '/.env', 0640);

        $zip->close();
        
        // Caches
        @shell_exec('php artisan view:clear');
        @shell_exec('php artisan cache:clear');
        
        response('success', 'Update extracted and permissions fixed (0644/0755).');
    } else {
        response('error', 'Failed to extract ZIP to ' . $extract_path);
    }
} else {
    response('error', 'Failed to open ZIP archive. Error code: ' . $res);
}
