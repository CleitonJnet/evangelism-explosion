<?php

return [
    'currency' => env('APP_CURRENCY', 'BRL'),
    'symbols' => [
        'BRL' => 'R$',
        'USD' => '$',
    ],
    'separators' => [
        'BRL' => [
            'decimal' => ',',
            'thousand' => '.',
        ],
        'USD' => [
            'decimal' => '.',
            'thousand' => ',',
        ],
    ],
];
