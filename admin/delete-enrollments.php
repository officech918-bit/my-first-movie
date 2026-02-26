<?php
declare(strict_types=1);

use App\Models\Enrollment;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/database.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo '405 Method Not Allowed';
    exit;
}

if (!isset($_SESSION['uid'])) {
    http_response_code(401);
    echo 'Unauthorized: You must be logged in to perform this action.';
    exit;
}

if (!isset($_SESSION['user_type']) || !in_array($_SESSION['user_type'], ['admin', 'webmaster'], true)) {
    http_response_code(403);
    echo 'Forbidden';
    exit;
}

if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
    http_response_code(400);
    echo 'Invalid CSRF token.';
    exit;
}

// The ID comes from the URL, which the router places in $_GET
$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
if ($id <= 0) {
    http_response_code(400);
    echo 'Invalid enrollment id.';
    exit;
}

$enrollment = Enrollment::find($id);
if (!$enrollment) {
    $_SESSION['error_message'] = 'Enrollment not found.';
} else {
    $enrollment->delete();
    $_SESSION['success_message'] = 'Enrollment deleted successfully.';
}

header('Location: ' . $admin_path . 'enrollments');
exit;