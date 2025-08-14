<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Enable or Disable bKash Log
    |--------------------------------------------------------------------------
    | Logging is only recommended in sandbox/testing mode.
    | In production, keep logging disabled to avoid exposing sensitive payment data.
    | Logs will be saved in the /storage/logs/laravel.log file.
    |
    | Usage:
    |   - Sandbox: BKASH_SANDBOX=true → logs enabled
    |   - Production: BKASH_SANDBOX=false → logs disabled
    */
    "bkash_log_enabled" => env("BKASH_SANDBOX", false),

    "bkash_sandbox" => env("BKASH_SANDBOX", false),
    "bkash_username" => env("BKASH_USERNAME"),
    "bkash_password" => env("BKASH_PASSWORD"),
    "bkash_app_key" => env("BKASH_APP_KEY"),
    "bkash_app_secret" => env("BKASH_APP_SECRET"),
    "bkash_base_url_sandbox" => "https://tokenized.sandbox.bka.sh/v1.2.0-beta/tokenized",
    "bkash_base_url_production" => "https://tokenized.Pay.bka.sh/v1.2.0-beta/tokenized",
    "bkash_callback_url" => env("BKASH_CALLBACK_URL", "http://127.0.0.1:8000/bkash/callback"),
];