<?php

/*
|--------------------------------------------------------------------------
| 1LINK / BOP Bill Payment Integration
|--------------------------------------------------------------------------
|
| BOP / 1LINK calls our inquiry and payment endpoints
| (POST /api/onelink/inquiry and POST /api/onelink/payment).
| All values are read from .env so nothing secret lives in the codebase.
|
*/

return [

    // Master switch. When false, every request returns response code 05.
    'enabled' => (bool) env('ONELINK_ENABLED', false),

    // Bearer token BOP sends in the Authorization header.
    // Generate a long random value, e.g.: php -r "echo bin2hex(random_bytes(32));"
    'token' => env('ONELINK_TOKEN', ''),

    // Comma-separated IPs or IPv4/IPv6 CIDR ranges allowed to call the API.
    // Empty = nobody allowed. "*" = allow all (local testing only, never production).
    'allowed_ips' => array_values(array_filter(array_map('trim', explode(',', (string) env('ONELINK_ALLOWED_IPS', ''))))),

    // Returned in responses. Institute code is assigned by BOP / 1LINK.
    'institute_code' => env('ONELINK_INSTITUTE_CODE', ''),
    'institute_name' => env('ONELINK_INSTITUTE_NAME'), // falls back to general_settings.name
    'channel'        => (int) env('ONELINK_CHANNEL', 1),

    // If set, requests with a different bank_mnemonic are rejected (code 01). Empty = accept any.
    'bank_mnemonic' => env('ONELINK_BANK_MNEMONIC', ''),

    // Amount format used in responses:
    //   plain  -> "27500" / "27500.50"         (as in BOP's sample document)
    //   padded -> "+0000002750000"             (standard 1LINK: sign + 13 digits, last 2 = paisa)
    // Incoming transaction_amount is accepted in either format.
    'amount_format' => env('ONELINK_AMOUNT_FORMAT', 'plain'),

    // Total length of new challan numbers (the part after one_bill_prefix).
    // New numbers look like 1 + zero-padded challan id, e.g. 10000123 for id 123.
    'challan_no_length' => (int) env('ONELINK_CHALLAN_NO_LENGTH', 8),

    // Timezone used to decide "today" for due-date / late-fee checks and to read tran_date/tran_time.
    // The app itself runs on APP_TIMEZONE (UTC); 1LINK dates are Pakistan local time.
    'timezone' => env('ONELINK_TIMEZONE', 'Asia/Karachi'),

    // Requests per minute allowed from BOP.
    'rate_limit' => (int) env('ONELINK_RATE_LIMIT', 120),
];
