<?php

/**
 * Support Engine Configuration
 * 
 * Why we need this file:
 * This file centralizes default behavior for options storage, database table names,
 * currency formatting symbols, and standard date formats across the entire ecosystem.
 * Keeping these decoupled allows host apps to modify behaviors without editing package code.
 */
return [
    /*
    |--------------------------------------------------------------------------
    | Options Storage Driver
    |--------------------------------------------------------------------------
    |
    | The interface implementation used to persist options.
    |
    */
    'options_store' => \Vaneetjoshi\LaravelUtilities\Stores\DatabaseOptionsStore::class,

    /*
    |--------------------------------------------------------------------------
    | Options Database Table
    |--------------------------------------------------------------------------
    |
    | The table name used by the DatabaseOptionsStore. Must contain 'type' and 'value'.
    |
    */
    'options_table' => 'options',

    /*
    |--------------------------------------------------------------------------
    | Currency Formatting Defaults
    |--------------------------------------------------------------------------
    |
    | Default symbols, decimal precision, and separators used by FormattingService.
    |
    */
    'currency' => [
        'default' => 'USD',
        'symbol' => '$',
        'decimals' => 2,
        'decimal_separator' => '.',
        'thousand_separator' => ',',
    ],

    /*
    |--------------------------------------------------------------------------
    | Date Formatting Defaults
    |--------------------------------------------------------------------------
    |
    | Default date structure used by FormattingService when rendering timestamps.
    |
    */
    'date_format' => 'd F Y H:i A',

    /*
    |--------------------------------------------------------------------------
    | Settings UI Configuration
    |--------------------------------------------------------------------------
    | Configure the built-in dynamic settings dashboard.
    */
    'ui' => [
        'enabled' => env('UTILITIES_SETTINGS_UI_ENABLED', true),
        'prefix' => 'settings',
        // By default, require standard web and auth middleware. 
        // Host apps can add 'step_up' or 'firewall' here.
        'middleware' => ['web', 'auth'], 
    ],
];