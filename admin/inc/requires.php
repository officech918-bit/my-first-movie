<?php 
// 1. Harden session cookie settings with SameSite attribute
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0, // 0 = until browser is closed
        'path' => '/',
        'domain' => '', // Current domain
        'secure' => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Strict' // Or 'Lax' if you have cross-site needs
    ]);
}

// 2. Start the session globally for the admin area
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 3. Generate CSRF token if it doesn't exist
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Include security functions and configurations
require_once __DIR__ . '/security.php';

	// Define a consistent base URL for the admin panel
	$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
	$host = $_SERVER['HTTP_HOST'];
	// Dynamically build the path to the admin directory
	$admin_path = dirname($_SERVER['SCRIPT_NAME']);
	// Define the constant, ensuring it ends with a slash
	define('ADMIN_BASE_URL', rtrim("$scheme://$host$admin_path", '/') . '/');

    // 3. Composer Autoloader
    require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

    // Load environment variables from .env file
    $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__, 2));
    $dotenv->load();

    // 4. Eloquent Database Bootstrap
    require_once dirname(__DIR__, 2) . '/bootstrap/database.php';


	require_once (dirname(__DIR__, 2) . "/classes/database.class.php");
	require_once (dirname(__DIR__, 2) . "/classes/main.class.php");
    require_once (dirname(__DIR__, 2) . "/classes/validation.class.php");
	require_once (dirname(__DIR__, 2) . "/classes/imageResizer.php");

function delete_directory($dir) {
    if (!file_exists($dir)) {
        return true;
    }

    if (!is_dir($dir)) {
        return unlink($dir);
    }

    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') {
            continue;
        }

        if (!delete_directory($dir . DIRECTORY_SEPARATOR . $item)) {
            return false;
        }
    }

    return rmdir($dir);
}

?>