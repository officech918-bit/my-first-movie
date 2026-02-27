<?php

use Illuminate\Database\Capsule\Manager as Capsule;

// Create a new Capsule instance
$capsule = new Capsule;

// We no longer need the legacy database file.
// require_once dirname(__DIR__) . '/classes/database.class.php';

// Check for environment variables and provide a clear error if they are missing.
if (empty($_ENV['DB_HOST']) || empty($_ENV['DB_DATABASE']) || !isset($_ENV['DB_USERNAME']) || !isset($_ENV['DB_PASSWORD'])) {
    throw new \RuntimeException('Database environment variables are not set for Eloquent. Make sure the .env file is present and loaded.');
}

$capsule->addConnection([
    'driver'    => 'mysql',
    'host'      => $_ENV['DB_HOST'],
    'database'  => $_ENV['DB_DATABASE'],
    'username'  => $_ENV['DB_USERNAME'],
    'password'  => $_ENV['DB_PASSWORD'],
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => '',
]);

// Set the capsule as global to be used statically throughout the application
$capsule->setAsGlobal();

// Boot Eloquent
$capsule->bootEloquent();