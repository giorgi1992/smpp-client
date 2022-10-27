<?php

return [

    /*
    * Host <host>, string
    * Port ton <port>, number
    * Login ton <login>, string
    * Password npi <password>, string
    * Sender <sender>, number or string
    * Timeout <timeout>, number
    * System type <system_type>, string
    * SMS registered delivery flag <registered_delivery_flag>, bool
    * Debug <debug>, bool
    */

    'providers' => [
        'example' => [
            'host' => '127.0.0.1',
            'port' => 9999,
            'login' => '',
            'password' => '',
            'sender' => '',
            'timeout' => 5000,

            'system_type' => 'default',
            'data_coding_ucs2' => 8,
            'sms_registered_delivery_flag' => true,
            'debug' =>  false
        ],
    ],

    /*
    * Default provider config <default_provider>, string
    */
    'default_provider' => 'example',

];
