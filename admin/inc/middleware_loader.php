<?php

/**
 * ------------------------------------------------------------------
 * BOOTSTRAP
 *
 * This file is responsible for setting up the application environment.
 * It loads Composer's autoloader, initializes the database connection
 * using Eloquent, and then runs the application's middleware.
 * ------------------------------------------------------------------
 */

// 1. Load Composer's Autoloader
require_once __DIR__ . '/../../vendor/autoload.php';

// 2. Initialize Eloquent ORM
require_once __DIR__ . '/../../config/database.php';

/**
 * ------------------------------------------------------------------
 * MIDDLEWARE EXECUTION
 * ------------------------------------------------------------------
 */

use App\Middleware\AuthMiddleware;
use App\Middleware\CategoryMiddleware;

// Execute Authentication Middleware
(new AuthMiddleware())->handle();