<?php

/**
 * | Created On-08-06-2023 
 * | Author-Anshu Kumar
 * | Created for - Symbolic Constants Used On various Functions and classes
 */
return [
    "MICROSERVICES_APIS"   => env('MICROSERVICES_APIS'),
    "CUSTOM_RELATIVE_PATH" => "Uploads/Custom",
    "DOC_URL"              => env('DOC_URL'),
    "DMS_URL"              => env('DMS_URL'),
    "WHATSAPP_TOKEN"        => env("WHATSAPP_TOKEN", "xxx"),
    "WHATSAPP_NUMBER_ID"    => env("WHATSAPP_NUMBER_ID", "xxx"),
    "WHATSAPP_URL"          => env("WHATSAPP_URL", "xxx"),

    #_Module Constants
    "PROPERTY_MODULE_ID"      => 1,
    "WATER_MODULE_ID"         => 2,
    "TRADE_MODULE_ID"         => 3,
    "SWM_MODULE_ID"           => 4,
    "ADVERTISEMENT_MODULE_ID" => 5,
    "WATER_TANKER_MODULE_ID"  => 11,


    "USER_TYPE" => [
        "Admin",
        "Employee",
        "JSK",
        "TC",
        "TL",
        "Pseudo User",
        "Water-Agency",
        "UlbUser",
    ],

    "ADMIN_ROLE" => 1,

];
