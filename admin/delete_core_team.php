<?php
declare(strict_types=1);

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

use App\Models\CoreTeam;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        http_response_code(403);
        die('Invalid CSRF token');
    }

    // The ID comes from the URL, which the router places in $_GET
    $id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
    
    // Debug logging
    error_log("Delete Core Team - ID from GET: " . ($_GET['id'] ?? 'not set'));
    error_log("Delete Core Team - ID from POST: " . ($_POST['id'] ?? 'not set'));
    error_log("Delete Core Team - Final ID: " . $id);
    
    $member = CoreTeam::find($id);

    // Debug logging
    error_log("Delete Core Team - Member found: " . ($member ? 'yes' : 'no'));
    if ($member) {
        error_log("Delete Core Team - Member ID: " . $member->id . ", Name: " . $member->name);
    }

    if ($member) {
        $member->delete();
        error_log("Delete Core Team - Member deleted successfully");
    } else {
        error_log("Delete Core Team - Member not found for ID: " . $id);
    }

    header('Location: ' . $admin_path . 'team');
    exit;
}