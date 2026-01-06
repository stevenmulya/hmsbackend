<?php
return [
    'defaults' => ['guard' => 'web', 'passwords' => 'customers'],
    'guards' => [
        'web' => ['driver' => 'session', 'provider' => 'customers'],
        'sanctum' => ['driver' => 'sanctum', 'provider' => 'customers'],
        'admin' => ['driver' => 'sanctum', 'provider' => 'admins'],
    ],
    'providers' => [
        'customers' => ['driver' => 'eloquent', 'model' => App\Models\Customer::class],
        'admins' => ['driver' => 'eloquent', 'model' => App\Models\Admin::class],
    ],
    'passwords' => [ 'customers' => ['provider' => 'customers', 'table' => 'password_reset_tokens', 'expire' => 60, 'throttle' => 60] ],
    'password_timeout' => 10800,
];