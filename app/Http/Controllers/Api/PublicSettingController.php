<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SettingsService;
use Illuminate\Http\JsonResponse;

class PublicSettingController extends Controller
{
    public function __construct(
        protected SettingsService $settings
    ) {}

    /**
     * Return only is_public = true settings.
     */
    public function index(): JsonResponse
    {
        return response()->json($this->settings->getPublicSettings());
    }
}
