<?php

return [
    'admin' => [
        'email' => env('SEED_ADMIN_EMAIL', 'admin@example.test'),
        'password' => env('SEED_ADMIN_PASSWORD', 'local-development-only'),
        'first_name' => env('SEED_ADMIN_FIRST_NAME', 'Development'),
    ],
];
