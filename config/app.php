<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application, which will be used when the
    | framework needs to place the application's name in a notification or
    | other UI elements where an application name needs to be displayed.
    |
    */

    'name' => env('APP_NAME', 'Laravel'),

    /*
    |--------------------------------------------------------------------------
    | Application Environment
    |--------------------------------------------------------------------------
    |
    | This value determines the "environment" your application is currently
    | running in. This may determine how you prefer to configure various
    | services the application utilizes. Set this in your ".env" file.
    |
    */

    'env' => env('APP_ENV', 'production'),

    /*
    |--------------------------------------------------------------------------
    | Application Debug Mode
    |--------------------------------------------------------------------------
    |
    | When your application is in debug mode, detailed error messages with
    | stack traces will be shown on every error that occurs within your
    | application. If disabled, a simple generic error page is shown.
    |
    */

    'debug' => (bool) env('APP_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    |
    | This URL is used by the console to properly generate URLs when using
    | the Artisan command line tool. You should set this to the root of
    | the application so that it's available within Artisan commands.
    |
    */

    'url' => env('APP_URL', 'http://localhost'),

    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default timezone for your application, which
    | will be used by the PHP date and date-time functions. The timezone
    | is set to "UTC" by default as it is suitable for most use cases.
    |
    */

    'timezone' => 'UTC',

    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    |
    | The application locale determines the default locale that will be used
    | by Laravel's translation / localization methods. This option can be
    | set to any locale for which you plan to have translation strings.
    |
    */

    'locale' => env('APP_LOCALE', 'en'),

    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),

    'faker_locale' => env('APP_FAKER_LOCALE', 'en_US'),

    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    |
    | This key is utilized by Laravel's encryption services and should be set
    | to a random, 32 character string to ensure that all encrypted values
    | are secure. You should do this prior to deploying the application.
    |
    */

    'cipher' => 'AES-256-CBC',

    'key' => env('APP_KEY'),

    'previous_keys' => [
        ...array_filter(
            explode(',', env('APP_PREVIOUS_KEYS', ''))
        ),
    ],

    /*
    |--------------------------------------------------------------------------
    | Maintenance Mode Driver
    |--------------------------------------------------------------------------
    |
    | These configuration options determine the driver used to determine and
    | manage Laravel's "maintenance mode" status. The "cache" driver will
    | allow maintenance mode to be controlled across multiple machines.
    |
    | Supported drivers: "file", "cache"
    |
    */

    'maintenance' => [
        'driver' => env('APP_MAINTENANCE_DRIVER', 'file'),
        'store' => env('APP_MAINTENANCE_STORE', 'database'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Base Currency
    |--------------------------------------------------------------------------
    |
    | This value is the default currency that will be used as the base for
    | all currency conversions. All exchange rates are stored relative to
    | this currency.
    |
    */
    'base_currency' => env('ACCOUNTING_BASE_CURRENCY', 'USD'),
    
    /*
    |--------------------------------------------------------------------------
    | Available Currencies
    |--------------------------------------------------------------------------
    |
    | This is the list of currencies available in the system. Each currency
    | has a code, symbol, name, and decimal places. Add to this list as needed.
    |
    */
    'currencies' => [
        'USD' => [
            'code' => 'USD',
            'symbol' => '$',
            'name' => 'US Dollar',
            'decimal_places' => 2,
        ],
        'EUR' => [
            'code' => 'EUR',
            'symbol' => '€',
            'name' => 'Euro',
            'decimal_places' => 2,
        ],
        'GBP' => [
            'code' => 'GBP',
            'symbol' => '£',
            'name' => 'British Pound',
            'decimal_places' => 2,
        ],
        'JPY' => [
            'code' => 'JPY',
            'symbol' => '¥',
            'name' => 'Japanese Yen',
            'decimal_places' => 0,
        ],
        'CNY' => [
            'code' => 'CNY',
            'symbol' => '¥',
            'name' => 'Chinese Yuan',
            'decimal_places' => 2,
        ],
        'AUD' => [
            'code' => 'AUD',
            'symbol' => 'A$',
            'name' => 'Australian Dollar',
            'decimal_places' => 2,
        ],
        'CAD' => [
            'code' => 'CAD',
            'symbol' => 'C$',
            'name' => 'Canadian Dollar',
            'decimal_places' => 2,
        ],
        'SGD' => [
            'code' => 'SGD',
            'symbol' => 'S$',
            'name' => 'Singapore Dollar',
            'decimal_places' => 2,
        ],
        'INR' => [
            'code' => 'INR',
            'symbol' => '₹',
            'name' => 'Indian Rupee',
            'decimal_places' => 2,
        ],
        'MYR' => [
            'code' => 'MYR',
            'symbol' => 'RM',
            'name' => 'Malaysian Ringgit',
            'decimal_places' => 2,
        ],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Exchange Rate API Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for automatic exchange rate updates. 
    | You can specify the API provider and related settings.
    |
    */
    'exchange_rate_api' => [
        'provider' => env('EXCHANGE_RATE_API_PROVIDER', 'exchangerate-api'),
        'api_key' => env('EXCHANGE_RATE_API_KEY'),
        'auto_update' => env('EXCHANGE_RATE_AUTO_UPDATE', false),
        'update_frequency' => env('EXCHANGE_RATE_UPDATE_FREQUENCY', 'daily'), // daily, weekly, monthly
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Accounting Period Settings
    |--------------------------------------------------------------------------
    |
    | Settings for accounting periods, fiscal year configuration, and related 
    | period closing operations.
    |
    */
    'accounting_periods' => [
        'fiscal_year_start_month' => env('FISCAL_YEAR_START_MONTH', 1), // 1 = January
        'allow_posting_to_closed_period' => env('ALLOW_POSTING_TO_CLOSED_PERIOD', false),
        'auto_create_next_period' => env('AUTO_CREATE_NEXT_PERIOD', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Currency Exchange Gain/Loss Accounts
    |--------------------------------------------------------------------------
    |
    | Default GL accounts for recording currency exchange gains and losses.
    |
    */
    'exchange_gain_account_id' => env('EXCHANGE_GAIN_ACCOUNT_ID'),
    'exchange_loss_account_id' => env('EXCHANGE_LOSS_ACCOUNT_ID'),
    
    /*
    |--------------------------------------------------------------------------
    | Multicurrency Formatting Settings
    |--------------------------------------------------------------------------
    |
    | Settings for how currency amounts should be displayed in the UI.
    |
    */
    'currency_display' => [
        'show_currency_code' => env('SHOW_CURRENCY_CODE', true),
        'show_symbol' => env('SHOW_CURRENCY_SYMBOL', true),
        'symbol_position' => env('CURRENCY_SYMBOL_POSITION', 'before'), // 'before' or 'after'
    ],

    /*
    |--------------------------------------------------------------------------
    | Rounding Settings
    |--------------------------------------------------------------------------
    |
    | Configure how rounding should be handled for different operations.
    |
    */
    'rounding' => [
        'method' => env('ROUNDING_METHOD', 'half_up'), // 'half_up', 'half_down', 'half_even', 'ceiling', 'floor'
        'transaction_precision' => env('TRANSACTION_PRECISION', 2),
        'exchange_rate_precision' => env('EXCHANGE_RATE_PRECISION', 6),
    ],

];
