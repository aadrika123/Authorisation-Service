<?php

return [

    'paths' => [
        'api/*',
        'auth/*',
        'sanctum/csrf-cookie'
    ],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'https://jharkhandegovernance.com',
        'https://www.jharkhandegovernance.com',
        'https://epramaan.meripehchaan.gov.in',
        'https://www.aadrikainfomedia.com',
        'https://www.egov.rsccl.in'
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,
];
