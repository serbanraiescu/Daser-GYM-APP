<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Setting;

return new class extends Migration
{
    public function up(): void
    {
        $settings = [
            [
                'key' => 'license_key',
                'value' => '',
                'type' => 'string',
                'group' => 'system',
                'is_public' => false,
            ],
            [
                'key' => 'license_status_cache',
                'value' => json_encode([
                    'status' => 'active', // Default to active for initial installation
                    'days_left' => null,
                    'is_grace_period' => false,
                    'grace_days_left' => null,
                ]),
                'type' => 'json',
                'group' => 'system',
                'is_public' => false,
            ],
            [
                'key' => 'license_last_check',
                'value' => now()->toDateTimeString(),
                'type' => 'string',
                'group' => 'system',
                'is_public' => false,
            ],
            [
                'key' => 'license_kill_token',
                'value' => 'SecretPassword123',
                'type' => 'string',
                'group' => 'system',
                'is_public' => false,
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }

    public function down(): void
    {
        Setting::whereIn('key', ['license_key', 'license_status_cache', 'license_last_check', 'license_kill_token'])->delete();
    }
};
