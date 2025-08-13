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
    "bkash_username" => env("BKASH_USERNAME", "sandboxTokenizedUser02"),
    "bkash_password" => env("BKASH_PASSWORD", "sandboxTokenizedUser02@12345"),
    "bkash_app_key" => env("BKASH_APP_KEY", "4f6o0cjiki2rfm34kfdadl1eqq"),
    "bkash_app_secret" => env("BKASH_APP_SECRET", "2is7hdktrekvrbljjh44ll3d9l1dtjo4pasmjvs5vl5qr3fug4b"),
    "bkash_base_url_sandbox" => "https://tokenized.sandbox.bka.sh/v1.2.0-beta/tokenized",
    "bkash_base_url_production" => "https://tokenized.Pay.bka.sh/v1.2.0-beta/tokenized",
    "bkash_callback_url" => env("BKASH_CALLBACK_URL", "http://127.0.0.1:8000/bkash/callback"),
];