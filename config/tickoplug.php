<?php

return [
    'platform_fee_percentage' => (float) env('PLATFORM_FEE_PERCENTAGE', 5),
    'platform_fee_fixed'      => (float) env('PLATFORM_FEE_FIXED', 30),
    'cart_expiry_minutes'     => (int) env('CART_EXPIRY_MINUTES', 15),
    'payout_hold_days'        => (int) env('PAYOUT_HOLD_DAYS', 7),
    'currency'                => 'KES',
    'max_tickets_per_order'   => 10,
    'stk_poll_timeout_secs'   => 120,
    'checkin_window_hours'    => 2,  // how many hours before event doors_open to allow check-in
];
