<?php
declare(strict_types=1);

// Load composer autoloader first (before anything else)
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

// Load environment variables from .env file
if (class_exists('Dotenv\Dotenv')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
}

// Load S3Uploader for environment detection
if (file_exists(__DIR__ . '/../classes/S3Uploader.php')) {
    require_once __DIR__ . '/../classes/S3Uploader.php';
    $s3Uploader = new S3Uploader();
}

// 1. BOOTSTRAPPING & DEPENDENCIES
require_once 'inc/requires.php';
use App\Models\BehindTheSceneImage;

// 2. INITIAL RESPONSE
$response = ['status' => 'error', 'message' => 'An unknown error occurred.'];

// 3. SECURITY & VALIDATION
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Invalid request method.';
    echo json_encode($response);
    exit;
}

if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    $response['message'] = 'CSRF token mismatch.';
    echo json_encode($response);
    exit;
}

if (!isset($_POST['image_id']) || !filter_var($_POST['image_id'], FILTER_VALIDATE_INT)) {
    $response['message'] = 'Invalid image ID.';
    echo json_encode($response);
    exit;
}

$image_id = (int)$_POST['image_id'];

// 4. DATABASE OPERATION
try {
    $image = BehindTheSceneImage::find($image_id);

    if ($image) {
        // Delete the file from storage (S3 or local)
        if ($image->image) {
            if ($s3Uploader && $s3Uploader->isS3Enabled()) {
                // Delete from S3
                $s3Uploader->deleteFile($image->image);
            } else {
                // Delete from local storage
                $image_path = __DIR__ . '/../uploads/bts/' . basename($image->image);
                if (file_exists($image_path)) {
                    unlink($image_path);
                }
            }
        }
        
        // Also delete the thumbnail if it exists
        if ($image->image_thumb) {
            if ($s3Uploader && $s3Uploader->isS3Enabled()) {
                // Delete from S3
                $s3Uploader->deleteFile($image->image_thumb);
            } else {
                // Delete from local storage
                $thumb_path = __DIR__ . '/../uploads/bts/' . basename($image->image_thumb);
                if (file_exists($thumb_path)) {
                    unlink($thumb_path);
                }
            }
        }

        // Delete the record from the database
        $image->delete();

        $response['status'] = 'success';
        $response['message'] = 'Image deleted successfully.';
    } else {
        $response['message'] = 'Image not found in the database.';
    }
} catch (\Exception $e) {
    error_log("Error deleting BTS image: " . $e->getMessage());
    $response['message'] = 'A database error occurred while trying to delete the image.';
}

// 5. FINAL RESPONSE
echo json_encode($response);
exit;
?>