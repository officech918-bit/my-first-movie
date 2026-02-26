<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Autoload dependencies and initialize database connection
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/database.php';

// Get admin path dynamically for redirects
$scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$requestUri = $_SERVER['REQUEST_URI'];

// Extract the actual path from the current request
$uriParts = explode('/', trim($requestUri, '/'));
$adminIndex = array_search('admin', $uriParts);

if ($adminIndex !== false) {
    $admin_path = $scheme . '://' . $host . '/' . implode('/', array_slice($uriParts, 0, $adminIndex + 1)) . '/';
} else {
    $admin_path = $scheme . '://' . $host . '/admin/'; // fallback
}

use App\Models\Panelist;

// Security checks
if (!isset($_SESSION['user_type']) || !in_array($_SESSION['user_type'], ['webmaster', 'admin'])) {
    http_response_code(403);
    exit('Forbidden: You do not have permission to access this page.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    die('Invalid CSRF token');
}

// The panelist ID is passed in the route, available here from $vars
if (isset($vars['id']) && is_numeric($vars['id'])) {
    $id = $vars['id'];
    $panelist = Panelist::find($id);

    if ($panelist) {
        // Optional: Delete the associated image file if it exists
        if ($panelist->image) {
            $image_path = __DIR__ . '/../uploads/panelists/' . $panelist->image;
            if (file_exists($image_path)) {
                unlink($image_path);
            }
        }
        
        $panelist->delete();
        $_SESSION['flash_message'] = 'Panelist deleted successfully.';
    } else {
        $_SESSION['flash_message'] = 'Panelist not found.';
    }
}

header('Location: ' . $admin_path . 'panelists');
exit;