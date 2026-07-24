<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the database connections below you wish
    | to use as your default connection for all database work. Of course
    | you may use many connections at once using the Database library.
    |
    */

    'default' => env('DB_CONNECTION', 'mysql'),

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | Here are each of the database connections setup for your application.
    | Of course, examples of configuring each database platform that is
    | supported by Laravel is shown below to make development simple.
    |
    |
    | All database work in Laravel is done through the PHP PDO facilities
    | so make sure you have the driver for your particular database of
    | choice installed on your machine before you begin development.
    |
    */

    'connections' => [

        'remote_license_db' => [
            'driver' => 'mysql', // O el driver de tu base de datos remota
            'host' => env('REMOTE_DB_HOST', '161.132.37.232'), // **¡IMPORTANTE!** Reemplaza con la IP o dominio de tu servidor remoto
            'port' => env('REMOTE_DB_PORT', '3306'),
            'database' => env('REMOTE_DB_DATABASE', 'clave_sol_clientes'), // Nombre de tu DB remota
            'username' => env('REMOTE_DB_USERNAME', 'root'), // Usuario de la DB remota
            'password' => env('REMOTE_DB_PASSWORD', '4Dm1n@2025desarrollador'), // Contraseña de la DB remota
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
                PDO::MYSQL_ATTR_SSL_CERT => env('MYSQL_ATTR_SSL_CERT'),
                PDO::MYSQL_ATTR_SSL_KEY => env('MYSQL_ATTR_SSL_KEY'),
            ]) : [],
        ],

        'mysql_bbang' => [
            'driver'    => 'mysql',
            'host'      => env('DB_BBANG_HOST', '127.0.0.1'),
            'port'      => env('DB_BBANG_PORT', '3306'),
            'database'  => env('DB_BBANG_DATABASE', 'your_bbang_database'), // <--- Nombre de tu base de datos 'bbang'
            'username'  => env('DB_BBANG_USERNAME', 'root'),             // <--- Usuario de la base de datos 'bbang'
            'password'  => env('DB_BBANG_PASSWORD', ''),                 // <--- Contraseña del usuario 'bbang'
            'unix_socket' => env('DB_BBANG_SOCKET', ''),
            'charset'   => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix'    => '',
            'strict'    => true,
            'engine'    => null,
        ],

        'sqlsrv_concar' => [
            'driver' => 'sqlsrv',
            'host' => env('DB_CONCAR_HOST', 'localhost'),
            'port' => env('DB_CONCAR_PORT', '1433'),
            'database' => env('DB_CONCAR_DATABASE', 'forge'),
            'username' => env('DB_CONCAR_USERNAME', 'forge'),
            'password' => env('DB_CONCAR_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
        ],

        'antigua' => [
            'driver'    => 'mysql',
            'host'      => env('DB_HOST_OLD', '127.0.0.1'),
            'port'      => env('DB_PORT_OLD', '3306'),
            'database'  => env('DB_DATABASE_OLD', 'bd_lk_0426'),
            'username'  => env('DB_USERNAME_OLD', 'root'),
            'password'  => env('DB_PASSWORD_OLD', ''),
            'charset'   => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix'    => '',
            'strict'    => true,
            'engine'    => null,
        ],
        

        'sqlite' => [
            'driver' => 'sqlite',
            'database' => env('DB_DATABASE', database_path('database.sqlite')),
            'prefix' => '',
        ],

        'concar_sql' => [
            'driver' => 'sqlsrv',
            'host' => env('CONCAR_DB_HOST', 'localhost'),
            'port' => env('CONCAR_DB_PORT', '1433'),
            'database' => env('CONCAR_DB_DATABASE', 'forge'),
            'username' => env('CONCAR_DB_USERNAME', 'forge'),
            'password' => env('CONCAR_DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
        ],

        'mysql' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => false,
            'engine' => null,
        ],

        'pgsql' => [
            'driver' => 'pgsql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'schema' => 'public',
            'sslmode' => 'prefer',
        ],

        'sqlsrv' => [
            'driver' => 'sqlsrv',
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', '1433'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table
    |--------------------------------------------------------------------------
    |
    | This table keeps track of all the migrations that have already run for
    | your application. Using this information, we can determine which of
    | the migrations on disk haven't actually been run in the database.
    |
    */

    'migrations' => 'migrations',

    /*
    |--------------------------------------------------------------------------
    | Redis Databases
    |--------------------------------------------------------------------------
    |
    | Redis is an open source, fast, and advanced key-value store that also
    | provides a richer set of commands than a typical key-value systems
    | such as APC or Memcached. Laravel makes it easy to dig right in.
    |
    */

    'redis' => [

        'client' => 'predis',

        'default' => [
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', 6379),
            'database' => 0,
        ],

    ],

];
