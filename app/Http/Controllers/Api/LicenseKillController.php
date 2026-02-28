<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\LicenseService;
use App\Services\SettingsService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class LicenseKillController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $token = $request->input('kill_token') ?? $request->header('X-Kill-Token');
        $expectedToken = app(SettingsService::class)->get('license_kill_token');

        \Illuminate\Support\Facades\Log::info('License Kill Switch Attempted', [
            'token_provided' => $token,
            'ip' => $request->ip()
        ]);

        if (empty($expectedToken) || $token !== $expectedToken) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        app(LicenseService::class)->revoke();
        
        \Illuminate\Support\Facades\Log::warning('License REVOKED via Kill Switch');

        return response()->json(['message' => 'License revoked successfully']);
    }
}
