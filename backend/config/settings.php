<?php
declare(strict_types=1);

use Monolog\Logger;

return [

    'errors' => [
        // Should be set to false in production
        'display_error_details'     => true,

        'log_errors'                => true,
        'log_error_details'         => true,
    ],

    'logger' => [
        'name'  => 'ICR',
        'path'  => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../logs/app.log',
        'level' => Logger::DEBUG,
    ],

    'database' => [
        'driver'    => 'pdo_pgsql',
        'host'      => DB_HOST,
        'user'      => DB_USER,
        'password'  => DB_PASS,
        'dbname'    => DB_NAME,
    ],

    'google_client' => [
        'config' => [
            'application_name'  => 'Svarta',
            'client_id'         => '860134715289-59jrq9stg5pafairm9l6tcbhi7i54jof.apps.googleusercontent.com',
            'client_secret'     => 'VbSm1Bm0SGw3TGXDr-YU-Iab',
        ],
        'scopes' => [
            'email',
            'profile'
        ],
        'redirect_uri' => 'http://localhost/api/login'
        // 'redirect_uri' => 'http://v167714.hosted-by-vdsina.ru/api/login'
    ],

    'permissions' => [
        'users' => [
            'guest' => 'r',
            'admin' => 'rw'
        ],
        'jobs' => [
            'guest' => 'r',
            'admin' => 'rw'
        ],
        'jobs_report' => [
            'guest' => 'r',
            'admin' => 'rw'
        ],
        'personnel' => [
            'guest' => 'r',
            'admin' => 'rw'
        ],
        'projects' => [
            'guest' => 'r',
            'admin' => 'rw'
        ],
        'worker_report' => [
            'guest' => 'r',
            'admin' => 'rw'
        ],
    ]
];
