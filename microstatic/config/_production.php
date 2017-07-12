<?php

return [

// ----------------------------------------------------------------------------
// DATABASE

    'database' => [
        'driver' => 'mysql',
        'host' => 'localhost',
        'port' => '3306',
        'user' => '',
        'password' => '',
        'database' => '',
        'charset' => 'utf8',
        'handler' => \PDO::class,
        'handlerOptions' => [
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES => false,
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
        ]
    ],

// ----------------------------------------------------------------------------
// ERROR REPORTING

    'errors' => [
        'error_reporting' => 0,
        'display_errors' => 0
    ],

];