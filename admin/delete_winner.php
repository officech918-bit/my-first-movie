<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_type']) || !in_array($_SESSION['user_type'], ['webmaster', 'admin'])) {
    http_response_code(403);
    exit('Forbidden');
}
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

use App\Models\Winner;
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}
if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
    http_response_code(400);
    exit('Invalid CSRF token');
}

// The ID comes from the URL, which the router places in $_GET
$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);

if ($id <= 0) {
    http_response_code(400);
    exit('Invalid winner id');
}
$winner = Winner::find($id);
if ($winner) {
    $winner->delete();
    $_SESSION['success_message'] = 'Winner deleted successfully!';
} else {
    $_SESSION['error_message'] = 'Winner not found.';
}
header('Location: ' . $admin_path . 'all-winners');
exit;
