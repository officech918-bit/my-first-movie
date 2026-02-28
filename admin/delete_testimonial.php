<?php

declare(strict_types=1);

use App\Models\Testimonial;

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

require_once __DIR__ . '/../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Auth Check
if (!isset($_SESSION['user_type']) || !in_array($_SESSION['user_type'], ['webmaster', 'admin'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized: You do not have permission to perform this action.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit();
}

if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'CSRF token mismatch.']);
    exit();
}

$testimonialId = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($testimonialId === 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid Testimonial ID.']);
    exit();
}

$testimonial = Testimonial::find($testimonialId);

if (!$testimonial) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Testimonial not found.']);
    exit();
}

// Delete S3/local files if they exist
if ($testimonial->logo) {
    try {
        if ($isProduction) {
            // In production, delete from S3
            $s3Uploader->deleteFile($testimonial->logo);
        } else {
            // In local development, delete from local filesystem
            $uploadDir = dirname(__DIR__) . '/uploads/testimonials/';
            if (file_exists($uploadDir . $testimonial->logo)) {
                unlink($uploadDir . $testimonial->logo);
            }
        }
    } catch (Exception $e) {
        // Log error but continue with deletion
        error_log('Failed to delete logo: ' . $e->getMessage());
    }
}

if ($testimonial->logo_thumb) {
    try {
        if ($isProduction) {
            // In production, delete from S3
            $s3Uploader->deleteFile($testimonial->logo_thumb);
        } else {
            // In local development, delete from local filesystem
            $uploadDir = dirname(__DIR__) . '/uploads/testimonials/';
            if (file_exists($uploadDir . $testimonial->logo_thumb)) {
                unlink($uploadDir . $testimonial->logo_thumb);
            }
        }
    } catch (Exception $e) {
        // Log error but continue with deletion
        error_log('Failed to delete logo thumb: ' . $e->getMessage());
    }
}

// Delete the testimonial from the database
$testimonial->delete();

header('Content-Type: application/json');
echo json_encode(['success' => true]);
exit();