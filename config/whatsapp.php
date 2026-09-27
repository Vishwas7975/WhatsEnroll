<?php

return [
    'phone_number_id'     => env('WHATSAPP_PHONE_NUMBER_ID', ''),
    'business_account_id' => env('WHATSAPP_BUSINESS_ACCOUNT_ID', ''),
    'api_token'           => env('WHATSAPP_API_TOKEN', ''),
    'verify_token'        => env('WHATSAPP_VERIFY_TOKEN', ''),
    // Used to verify X-Hub-Signature-256 on incoming webhook POSTs from Meta.
    // Found at: developers.facebook.com -> Your App -> Settings -> Basic -> App Secret
    'meta_app_secret'     => env('WHATSAPP_META_APP_SECRET', ''),
];
