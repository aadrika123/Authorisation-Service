<?php

return [
    'url' => env('EPRAMAAN_URL'),
    'aes_key' => env('EPRAMAAN_AES_KEY'),
    'scope' => env('EPRAMAAN_SCOPE', 'openid'),
    'response_type' => env('EPRAMAAN_RESPONSE_TYPE', 'code'),
    'code_method' => env('EPRAMAAN_CODE_METHOD', 'S256'),
    'request_uri' => env('EPRAMAAN_REQUEST_URI'),
    'token_url' => env('EPRAMAAN_TOKEN_URL', 'https://epramaan.meripehchaan.gov.in/openid/jwt/processJwtTokenRequest.do'),

    'services' => [
        'citizen' => [
            'id' => env('EPRAMAAN_SERVICE_CITIZEN_ID'),
            'redirect' => env('EPRAMAAN_SERVICE_CITIZEN_REDIRECT'),
        ],
        'citizen-page' => [
            'id' => env('EPRAMAAN_SERVICE_CITIZEN_PAGE_ID'),
            'redirect' => env('EPRAMAAN_SERVICE_CITIZEN_PAGE_REDIRECT'),
        ],
        'mobile' => [
            'id' => env('EPRAMAAN_SERVICE_MOBILE_ID'),
            'redirect' => env('EPRAMAAN_SERVICE_MOBILE_REDIRECT'),
        ],
        'water' => [
            'id' => env('EPRAMAAN_SERVICE_WATER_ID'),
            'redirect' => env('EPRAMAAN_SERVICE_WATER_REDIRECT'),
        ],
        'trade' => [
            'id' => env('EPRAMAAN_SERVICE_TRADE_ID'),
            'redirect' => env('EPRAMAAN_SERVICE_TRADE_REDIRECT'),
        ],
        'advertisement' => [
            'id' => env('EPRAMAAN_SERVICE_ADVERTISEMENT_ID'),
            'redirect' => env('EPRAMAAN_SERVICE_ADVERTISEMENT_REDIRECT'),
        ],
        'property' => [
            'id' => env('EPRAMAAN_SERVICE_PROPERTY_ID'),
            'redirect' => env('EPRAMAAN_SERVICE_PROPERTY_REDIRECT'),
        ],
        'pet' => [
            'id' => env('EPRAMAAN_SERVICE_PET_ID'),
            'redirect' => env('EPRAMAAN_SERVICE_PET_REDIRECT'),
        ],
        'marriage' => [
            'id' => env('EPRAMAAN_SERVICE_MARRIAGE_ID'),
            'redirect' => env('EPRAMAAN_SERVICE_MARRIAGE_REDIRECT'),
        ],
        'agency' => [
            'id' => env('EPRAMAAN_SERVICE_AGENCY_ID'),
            'redirect' => env('EPRAMAAN_SERVICE_AGENCY_REDIRECT'),
        ],
    ],
];
