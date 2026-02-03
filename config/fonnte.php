<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Fonnte API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Fonnte WhatsApp API integration.
    | Get your token from https://fonnte.com
    |
    */

    'token' => env('FONNTE_TOKEN', ''),

    'api_url' => 'https://api.fonnte.com/send',

    // Token expiry in minutes (for password reset)
    'token_expiry' => 15,
];
