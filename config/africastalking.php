<?php

return [
    'username'         => env('AFRICASTALKING_USERNAME', 'sandbox'),
    'api_key'          => env('AFRICASTALKING_API_KEY'),
    'sender_id'        => env('AFRICASTALKING_SENDER_ID', 'TICKOPLUG'),
    'whatsapp_sender'  => env('AFRICASTALKING_WHATSAPP_SENDER'),
    'base_url'         => env('AFRICASTALKING_USERNAME', 'sandbox') === 'sandbox'
        ? 'https://api.sandbox.africastalking.com'
        : 'https://api.africastalking.com',
    'sms_url'          => '/version1/messaging',
    'whatsapp_url'     => '/version1/messaging/whatsapp',
];
