<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// --- Daser GYM APP Portable Shared Hosting ---
$APP_DIR = __DIR__ . '/../daser_gym_app';

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = $APP_DIR.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require $APP_DIR.'/vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once $APP_DIR.'/bootstrap/app.php';

$app->handleRequest(Request::capture());
