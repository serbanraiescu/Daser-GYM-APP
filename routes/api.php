<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/public/settings', [\App\Http\Controllers\Api\PublicSettingController::class, 'index']);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/v1/license-kill', \App\Http\Controllers\Api\LicenseKillController::class);
