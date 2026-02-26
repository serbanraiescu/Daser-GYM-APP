<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Application Feature Flags
    |--------------------------------------------------------------------------
    |
    | Define modules that can be toggled on/off globally.
    |
    */

    'classes' => env('FEATURE_CLASSES', false),
    'credits' => env('FEATURE_CREDITS', false),
    'sms' => env('FEATURE_SMS', false),
];
