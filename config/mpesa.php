<?php

return [
    'env'              => env('MPESA_ENV', 'sandbox'),
    'consumer_key'     => env('MPESA_CONSUMER_KEY'),
    'consumer_secret'  => env('MPESA_CONSUMER_SECRET'),
    'shortcode'        => env('MPESA_SHORTCODE'),
    'passkey'          => env('MPESA_PASSKEY'),
    'stk_callback_url' => env('MPESA_STK_CALLBACK_URL'),
    'c2b_validation_url'   => env('MPESA_C2B_VALIDATION_URL'),
    'c2b_confirmation_url' => env('MPESA_C2B_CONFIRMATION_URL'),

    'base_url' => env('MPESA_ENV', 'sandbox') === 'production'
        ? 'https://api.safaricom.co.ke'
        : 'https://sandbox.safaricom.co.ke',

    // Safaricom IP whitelist for webhook validation
    'allowed_ips' => [
        '196.201.214.200', '196.201.214.206', '196.201.213.114',
        '196.201.214.207', '196.201.214.208', '196.201.213.44',
        '196.201.212.127', '196.201.212.138', '196.201.212.129',
        '196.201.212.136', '196.201.212.74',  '196.201.212.69',
    ],
];
