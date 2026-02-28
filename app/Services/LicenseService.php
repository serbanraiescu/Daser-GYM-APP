<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Carbon;

class LicenseService
{
    protected string $apiUrl = 'https://app.daserdesign.ro/api/license/verify';

    public function checkLicense(bool $force = false): array
    {
        $lastCheck = $this->getSetting('license_last_check');
        $cache = $this->getSetting('license_status_cache', [
            'status' => 'active',
            'days_left' => null,
            'is_grace_period' => false,
            'grace_days_left' => null,
        ]);

        $shouldCheck = $force || !$lastCheck || Carbon::parse($lastCheck)->addHours(12)->isPast();

        if ($shouldCheck) {
            try {
                $licenseKey = $this->getSetting('license_key');
                if (empty($licenseKey)) {
                     // If no key, we might want to default to denied or just wait for activation
                     return $cache;
                }

                $response = Http::timeout(10)->post($this->apiUrl, [
                    'license_key' => $licenseKey,
                    'fingerprint' => request()->getHost(),
                ]);

                if ($response->successful()) {
                    $newCache = $response->json();
                    $this->updateLicenseCache($newCache);
                    return $newCache;
                }
            } catch (\Exception $e) {
                // If API is unreachable, check the 48h rule
                if ($lastCheck && Carbon::parse($lastCheck)->addHours(48)->isPast()) {
                    // Too long without check, downgrade but don't hard lock yet? 
                    // Master guide says: "allow usage for up to 48 hours"
                    // So after 48h we might want to force a warning
                }
            }
        }

        return $cache;
    }

    public function activate(string $key): array
    {
        Setting::where('key', 'license_key')->update(['value' => $key]);
        Cache::forget('setting:license_key');
        
        return $this->checkLicense(true);
    }

    public function revoke(): void
    {
        $this->updateLicenseCache([
            'status' => 'denied',
            'days_left' => 0,
            'is_grace_period' => false,
            'grace_days_left' => null,
        ]);
    }

    protected function updateLicenseCache(array $data): void
    {
        Setting::where('key', 'license_status_cache')->update(['value' => json_encode($data)]);
        Setting::where('key', 'license_last_check')->update(['value' => now()->toDateTimeString()]);
        
        Cache::forget('setting:license_status_cache');
        Cache::forget('setting:license_last_check');
    }

    protected function getSetting(string $key, $default = null)
    {
        return app(SettingsService::class)->get($key, $default);
    }
}
