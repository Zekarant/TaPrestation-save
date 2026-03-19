<?php

return [
    /*
    |--------------------------------------------------------------------------
    | HashIds Configuration
    |--------------------------------------------------------------------------
    |
    | This is the salt used by HashIds to generate unique hashes.
    | IMPORTANT: Ne jamais changer cette valeur en production !
    |
    */

    'default' => 'main',

    'connections' => [
        'main' => [
            'salt' => env('HASHIDS_SALT', 'TaPrestation-Secure-Salt-2024-@#$!'),
            'length' => 10,
            'alphabet' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890',
        ],
    ],
];
