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
        'path'  => isset($_ENV['docker']) ? 'php://stdout' : ROOT_DIR . getenv("LOGS_DIR"),
        'level' => Logger::DEBUG,
    ],

    'database' => [
        'driver'    => 'pdo_pgsql',
        'host'      => getenv('DB_HOST'),
        'user'      => getenv('DB_USER'),
        'password'  => getenv('DB_PASS'),
        'dbname'    => getenv('DB_NAME'),
    ],

    'google_client' => [
        'config' => [
            'application_name'  => getenv('GAPP_NAME'),
            'client_id'         => getenv('GAPP_CLIENT_ID'),
            'client_secret'     => getenv('GAPP_CLIENT_SECRET'),
        ],
        'scopes' => [
            'email',
            'profile'
        ],
        'redirect_uri' => getenv('GAPP_REDIRECT_URL')
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
