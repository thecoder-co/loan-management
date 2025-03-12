<?php

return [

    'defaults' => [
        'guard' => 'api', // Recommended default for APIs
        'passwords' => 'customers',
    ],

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'customers',
        ],

        'api' => [ // <-- Add this guard
            'driver' => 'jwt',
            'provider' => 'customers',
        ],
    ],

    'providers' => [
        'customers' => [
            'driver' => 'eloquent',
            'model' => App\Models\Customer::class,
        ],
    ],

    'password_timeout' => 10800,

];
