<?php
// Ensure session is started.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load environment variables
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
    if (class_exists('Dotenv\Dotenv')) {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
        $dotenv->load();
    }
}

// Load S3Uploader for environment detection
if (file_exists(__DIR__ . '/../classes/S3Uploader.php')) {
    require_once __DIR__ . '/../classes/S3Uploader.php';
    $s3Uploader = new S3Uploader();
    $isProduction = $s3Uploader->isS3Enabled();
}

// Load database connection using the same approach as dashboard.php
include('inc/requires.php');

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

use App\Models\News;

// Auth Guard: Block access if user is not an authorized admin or webmaster.
if (!isset($_SESSION['user_type']) || !in_array($_SESSION['user_type'], ['webmaster', 'admin'])) {
    http_response_code(403);
    exit('Forbidden: You do not have permission to access this page.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    http_response_code(403);
    die('Invalid CSRF token');
}

// Debug: Log incoming request
error_log("Delete news request received: " . print_r($_REQUEST, true));
error_log("Request method: " . $_SERVER['REQUEST_METHOD']);
error_log("GET data: " . print_r($_GET, true));
error_log("POST data: " . print_r($_POST, true));

// The ID now comes from the URL, which the router places in $_GET.
$newsId = (int)($_GET['id'] ?? 0);
error_log("Extracted news ID: " . $newsId);

if ($newsId > 0) {
    $newsItem = News::find($newsId);

    if ($newsItem) {
        // Delete the associated image file
        $uploadFileDir = realpath(__DIR__ . '/../uploads/news');
        if ($newsItem->image && file_exists($uploadFileDir . '/' . $newsItem->image)) {
            unlink($uploadFileDir . '/' . $newsItem->image);
        }

        // Delete the database record
        $newsItem->delete();

        $_SESSION['success_message'] = 'News article deleted successfully!';
    } else {
        $_SESSION['error_message'] = 'News article not found.';
    }
} else {
    $_SESSION['error_message'] = 'Invalid news ID.';
}

header('Location: ' . $admin_path . 'all-news');
exit();