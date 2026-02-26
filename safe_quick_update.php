<?php
/**
 * Daser Quick Update - Server Side (Safe Version)
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
    response('error', 'ZipArchive extension is NOT enabled.');
}

if (!isset($_FILES['update_zip'])) {
    response('error', 'No file uploaded. Check max_upload_size and post_max_size.');
}

$zip_file = $_FILES['update_zip']['tmp_name'];
$zip = new ZipArchive;
$res = $zip->open($zip_file);

if ($res === TRUE) {
    $extract_path = __DIR__;
    
    if (!is_writable($extract_path)) {
        response('error', 'Extract path is not writable: ' . $extract_path);
    }

    if ($zip->extractTo($extract_path)) {
        $zip->close();
        
        // --- NOTE: shell_exec is removed for security compatibility ---
        // We will clear the cache via a separate script if needed.
        
        response('success', 'Update extracted successfully to root!');
    } else {
        response('error', 'Failed to extract ZIP.');
    }
} else {
    response('error', 'Failed to open ZIP archive. Error code: ' . $res);
}
