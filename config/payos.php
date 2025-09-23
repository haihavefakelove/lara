<?php
return [
    'client_id'       => env('PAYOS_CLIENT_ID', ''),
    'api_key'         => env('PAYOS_API_KEY', ''),
    'checksum_key'    => env('PAYOS_CHECKSUM_KEY', ''),
    'return_url'      => env('PAYOS_RETURN_URL', ''),
    'cancel_url'      => env('PAYOS_CANCEL_URL', ''),
    'base_url'        => env('PAYOS_BASE_URL', 'https://api-merchant.payos.vn'),
    'timeout'         => env('PAYOS_TIMEOUT', 60),
    'connect_timeout' => env('PAYOS_CONNECT_TIMEOUT', 30),
    'verify_ssl'      => env('PAYOS_VERIFY_SSL', true),
];

