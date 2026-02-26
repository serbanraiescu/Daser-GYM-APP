<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\SettingHistory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class SettingsService
{
    protected string $cachePrefix = 'setting:';

    /**
     * Get a setting value by key, with type-casting.
     */
    public function get(string $key, $default = null)
    {
        $cacheKey = $this->cachePrefix . $key;
        
        $setting = Cache::remember($cacheKey, now()->addHours(24), function () use ($key) {
            return Setting::where('key', $key)->first();
        });

        if (!$setting) {
            return $default;
        }

        return $this->castValue($setting->value, $setting->type);
    }

    /**
     * Set a setting value, creating it if it doesn't exist, and logging history.
     */
    public function set(string $key, $value, ?string $type = null, ?string $group = null, bool $isPublic = false)
    {
        $setting = Setting::where('key', $key)->first();
        $oldValue = $setting ? $setting->value : null;

        $stringValue = $this->prepareValue($value, $type ?? ($setting->type ?? 'string'));

        if (!$setting) {
            $setting = Setting::create([
                'key' => $key,
                'value' => $stringValue,
                'type' => $type ?? 'string',
                'group' => $group ?? 'branding',
                'is_public' => $isPublic,
                'version' => 1,
                'updated_by' => Auth::id(),
            ]);
        } else {
            $setting->update([
                'value' => $stringValue,
                'version' => $setting->version + 1,
                'updated_by' => Auth::id(),
            ]);
        }

        // Log history if changed
        if ($oldValue !== $stringValue) {
            SettingHistory::create([
                'setting_id' => $setting->id,
                'old_value' => $oldValue,
                'new_value' => $stringValue,
                'changed_by' => Auth::id(),
            ]);
        }

        Cache::forget($this->cachePrefix . $key);
        Cache::forget('settings:public');
        Cache::forget('public:website');

        return $setting;
    }

    /**
     * Get all public settings as a key-value array.
     */
    public function getPublicSettings(): array
    {
        return Cache::remember('settings:public', now()->addHours(24), function () {
            return Setting::where('is_public', true)
                ->get()
                ->mapWithKeys(fn ($s) => [$s->key => $this->castValue($s->value, $s->type)])
                ->toArray();
        });
    }

    /**
     * Cast raw string value to specified type.
     */
    protected function castValue($value, string $type)
    {
        return match ($type) {
            'json' => json_decode($value, true),
            'int' => (int) $value,
            'bool' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            default => $value,
        };
    }

    /**
     * Prepare value for storage based on type.
     */
    protected function prepareValue($value, string $type): string
    {
        if ($type === 'json' || is_array($value)) {
            return json_encode($value);
        }
        return (string) $value;
    }
}
