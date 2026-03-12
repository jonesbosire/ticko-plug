<?php

return [
    'public_key'     => env('FLUTTERWAVE_PUBLIC_KEY'),
    'secret_key'     => env('FLUTTERWAVE_SECRET_KEY'),
    'encryption_key' => env('FLUTTERWAVE_ENCRYPTION_KEY'),
    'webhook_secret' => env('FLUTTERWAVE_WEBHOOK_SECRET'),
    'redirect_url'   => env('FLUTTERWAVE_REDIRECT_URL'),
    'base_url'       => 'https://api.flutterwave.com/v3',
    'currency'       => 'KES',
];
