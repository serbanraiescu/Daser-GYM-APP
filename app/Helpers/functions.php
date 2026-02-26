<?php

if (!function_exists('feature_enabled')) {
    /**
     * Check if a specific feature flag is enabled.
     */
    function feature_enabled(string $feature): bool
    {
        return config("features.{$feature}", false);
    }
}
