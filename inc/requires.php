<?php
declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Session Initialization
|--------------------------------------------------------------------------
|
| Start the PHP session. This must be called before any output is sent
| to the browser and before using the $_SESSION superglobal.
|
*/


/*
|--------------------------------------------------------------------------
| Composer Autoloader
|--------------------------------------------------------------------------
|
| We'll load the Composer auto-loader so that our classes are automatically
| loaded when we need them. We will not be using a full "app" container
| for this application but this file is a good place to register the loader.
|
*/
require_once __DIR__ . '/../vendor/autoload.php';

/*
|--------------------------------------------------------------------------
| Eloquent ORM Initialization
|--------------------------------------------------------------------------
|
| This file sets up the Eloquent ORM (Object Relational Mapper) using
| Laravel's Capsule manager. It configures the database connection
| and makes Eloquent available for use throughout the application.
|
*/
require_once __DIR__ . '/../config/database.php';

/*
|--------------------------------------------------------------------------
| Production Error Handling (Temporarily Disabled for Debugging)
|--------------------------------------------------------------------------
*/

/*
ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL & ~E_DEPRECATED);
*/

/*
|--------------------------------------------------------------------------
| Development Error Reporting (Enabled for Debugging)
|--------------------------------------------------------------------------
*/

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL & ~E_DEPRECATED);

/*
|--------------------------------------------------------------------------
| Force HTTPS
|--------------------------------------------------------------------------
*/

$isHttps = (
    (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off') ||
    (isset($_SERVER['SERVER_PORT']) && (string) $_SERVER['SERVER_PORT'] === '443') ||
    (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && stripos((string) $_SERVER['HTTP_X_FORWARDED_PROTO'], 'https') !== false) ||
    (isset($_SERVER['HTTP_X_FORWARDED_SSL']) && strtolower((string) $_SERVER['HTTP_X_FORWARDED_SSL']) === 'on') ||
    (isset($_SERVER['HTTP_CF_VISITOR']) && stripos((string) $_SERVER['HTTP_CF_VISITOR'], '"scheme":"https"') !== false)
);

if (!$isHttps) {
    header("Location: https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], true, 301);
    exit;
}

/*
|--------------------------------------------------------------------------
| Security Headers
|--------------------------------------------------------------------------
*/

// Generate a nonce for inline scripts to be used in the Content Security Policy
$nonce = base64_encode(random_bytes(16));

header("X-Frame-Options: SAMEORIGIN");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Permissions-Policy: camera=(), microphone=(), geolocation=()");
header("X-XSS-Protection: 0"); // Modern browsers rely on CSP

// Content Security Policy to allow necessary external resources and inline scripts via a nonce.
// 'unsafe-inline' for styles is a temporary measure to prevent breaking styles.
$csp = "default-src 'self';";
$csp .= "script-src 'self' https://cdn.curator.io https://connect.facebook.net https://www.googletagmanager.com 'unsafe-eval' 'nonce-$nonce' 'sha256-C+IGLEBTrzg1cqDKPZqpHDyT8Xu0DaPNG2w4A4c/YwA=' 'sha256-LLGKWCKo6gSlfa1Y5IfMzx97O/X0znj4iGotWE1trk4=' 'unsafe-hashes';";
$csp .= "style-src 'self' https://fonts.googleapis.com 'unsafe-inline';";
$csp .= "img-src 'self' data: https://image.tmdb.org https://* blob: https://secure.ccavenue.com;";
$csp .= "font-src 'self' https://fonts.gstatic.com; ";
$csp .= "frame-src 'self' https://www.instagram.com https://www.youtube.com; ";
$csp .= "object-src 'none'; ";
$csp .= "frame-ancestors 'self'; ";
$csp .= "base-uri 'self'; ";
$csp .= "form-action 'self' https://secure.ccavenue.com;";
header("Content-Security-Policy: " . $csp);


// Enable HSTS (only if HTTPS is fully enabled everywhere)
header("Strict-Transport-Security: max-age=63072000; includeSubDomains; preload");

/*
|--------------------------------------------------------------------------
| Composer Autoload
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/../vendor/autoload.php';

/*
|--------------------------------------------------------------------------
| Class Autoloading
|--------------------------------------------------------------------------
*/

require_once dirname(__DIR__) . '/classes/database.class.php';
require_once dirname(__DIR__) . '/classes/main.class.php';
require_once dirname(__DIR__) . '/classes/validation.class.php';
require_once dirname(__DIR__) . '/classes/imageResizer.php';


/*
|--------------------------------------------------------------------------
| Environment Variables
|--------------------------------------------------------------------------
*/

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad(); // safer than load()

/*
|--------------------------------------------------------------------------
| Secure Session Initialization
|--------------------------------------------------------------------------
*/

if (session_status() === PHP_SESSION_NONE) {

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => true, // Always true because HTTPS enforced
        'httponly' => true,
        'samesite' => 'Strict'
    ]);

    session_start();

    // Prevent session fixation
    if (empty($_SESSION['initiated'])) {
        session_regenerate_id(true);
        $_SESSION['initiated'] = true;
    }
}

/*
|--------------------------------------------------------------------------
| CSRF Protection
|--------------------------------------------------------------------------
*/

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function verify_csrf(): void
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        if (
            empty($_POST['csrf_token']) ||
            empty($_SESSION['csrf_token']) ||
            !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
        ) {
            http_response_code(403);
            exit('Invalid CSRF token.');
        }
    }
}

/*
|--------------------------------------------------------------------------
| Database Connection (Secure PDO)
|--------------------------------------------------------------------------
*/

try {

    $dsn = sprintf(
        "mysql:host=%s;dbname=%s;charset=utf8mb4",
        $_ENV['DB_HOST'],
        $_ENV['DB_DATABASE']
    );

    $pdo = new PDO(
        $dsn,
        $_ENV['DB_USERNAME'],
        $_ENV['DB_PASSWORD'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );

} catch (PDOException $e) {

    error_log($e->getMessage());
    http_response_code(500);
    exit('Database connection failed.');
}

/*
|--------------------------------------------------------------------------
| Helper: Escape Output (XSS Protection)
|--------------------------------------------------------------------------
*/

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/*
|--------------------------------------------------------------------------
| Helper: Lazy Load Image (Performance)
|--------------------------------------------------------------------------
*/

function lazy_image(string $src, string $alt, string $class = '', array $attributes = []): string
{
    // Basic validation
    if (empty($src)) {
        // Return a placeholder or an empty string if no source is provided
        return '';
    }

    $default_attributes = [
        'loading' => 'lazy',
        'decoding' => 'async',
    ];

    // User-provided attributes, with user's taking precedence
    $all_attributes = array_merge($default_attributes, $attributes);

    // Always set alt and class, ensuring they are escaped
    $all_attributes['alt'] = e($alt);
    $all_attributes['class'] = trim('lazy ' . e($class));

    $attribute_string = '';
    foreach ($all_attributes as $key => $value) {
        // Sanitize the attribute key as well
        $key = e($key);
        $value = e($value);
        $attribute_string .= sprintf(' %s="%s"', $key, $value);
    }

    return sprintf('<img src="%s"%s>', e($src), $attribute_string);
}