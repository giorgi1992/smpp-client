<?php

return [
    /*
    * Host <host>, string
    * Port ton <port>, number
    * Login ton <login>, string
    * Password npi <password>, string
    * Sender <sender>, number or string
    * Timeout <timeout>, number
    * Source ton <source_ton>, number
    * Source npi <source_npi>, number
    * Destination ton <destination_ton>, number
    * Destination npi <destination_npi>, number
    * System type <system_type>, string
    * SMS registered delivery flag <registered_delivery_flag>, bool
    * Force IPV4 <force_ipv4>, bool
    * Debug <debug>, bool
    */

    'providers' => [
        'default' => [
            'host' => '127.0.0.1',
            'port' => 9999,
            'login' => '',
            'password' => '',
            'sender' => '',
            'timeout' => 5000,

            'source_ton' => 5,
            'source_npi' => 1,
            'destination_ton' => 1,
            'destination_npi' => 1,

            'sms_registered_delivery_flag' => true,
            'system_type' => 'default',
            'force_ipv4' =>  false,
            'debug' =>  false
        ]
    ]
];
