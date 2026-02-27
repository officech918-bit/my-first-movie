<?php

use Illuminate\Database\Capsule\Manager as Capsule;

// This file will be included by our main bootstrap process
// It requires the Composer autoloader to be loaded first.

$capsule = new Capsule;

$capsule->addConnection([
    'driver'    => 'mysql',
    'host'      => getenv('DB_HOST') ?: 'localhost',
    'database'  => getenv('DB_NAME') ?: 'myfirstm_live',
    'username'  => getenv('DB_USER') ?: 'root',
    'password'  => getenv('DB_PASS') ?: '',
    'charset'   => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix'    => '',
]);

// Make this Capsule instance available globally via static methods
$capsule->setAsGlobal();

// Setup the Eloquent ORM
$capsule->bootEloquent();
