<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// --- Daser Root Proxy ---
$APP_DIR = __DIR__ . '/daser_gym_app';

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = $APP_DIR.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require $APP_DIR.'/vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once $APP_DIR.'/bootstrap/app.php';

// Force public path to current folder for root access
$app->usePublicPath(__DIR__);

$app->handleRequest(Request::capture());
