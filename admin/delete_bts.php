<?php
declare(strict_types=1);

// Load composer autoloader first (before anything else)
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

// Start session at the very top to access $_SESSION variables
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('Asia/Kolkata');

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

// 1. Bootstrap the application (loads autoloader, Eloquent, middleware)
// The middleware will handle the authentication check.
require_once 'inc/middleware_loader.php';

use App\Models\BehindTheScene;
use App\Models\BehindTheSceneImage;

// --- Main Deletion Logic ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'], $_POST['csrf_token'])) {
    
    // 1. Validate CSRF Token
    if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        header('HTTP/1.1 403 Forbidden');
        echo json_encode(['error' => 'Invalid security token.']);
        exit();
    }

    // 2. Sanitize and Validate Input
    $bts_id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

    if ($bts_id === false || $bts_id <= 0) {
        header('HTTP/1.1 400 Bad Request');
        echo json_encode(['error' => 'Invalid ID provided.']);
        exit();
    }

    // 3. Get the BTS record before deletion to handle file cleanup
    $bts = BehindTheScene::find($bts_id);
    if (!$bts) {
        header('HTTP/1.1 404 Not Found');
        echo json_encode(['error' => 'Item not found.']);
        exit();
    }

    // 4. Delete associated files (title image and gallery images)
    try {
        // Delete title image if exists
        if ($bts->screenshot) {
            if ($s3Uploader && $s3Uploader->isS3Enabled()) {
                // Delete from S3
                $s3Uploader->deleteFile($bts->screenshot);
            } else {
                // Delete from local storage
                $localPath = __DIR__ . '/../uploads/bts/' . basename($bts->screenshot);
                if (file_exists($localPath)) {
                    unlink($localPath);
                }
            }
        }

        // Delete gallery images
        $galleryImages = BehindTheSceneImage::where('bts', $bts_id)->get();
        foreach ($galleryImages as $image) {
            if ($image->image) {
                if ($s3Uploader && $s3Uploader->isS3Enabled()) {
                    // Delete from S3
                    $s3Uploader->deleteFile($image->image);
                } else {
                    // Delete from local storage
                    $localPath = __DIR__ . '/../uploads/bts/' . basename($image->image);
                    if (file_exists($localPath)) {
                        unlink($localPath);
                    }
                }
            }
            $image->delete(); // Delete gallery image record
        }
    } catch (Exception $e) {
        // Log error but continue with database deletion
        error_log("File deletion error for BTS ID $bts_id: " . $e->getMessage());
    }

    // 5. Delete the BTS record from database
    $deleted_count = $bts->delete();

    if ($deleted_count > 0) {
        header('Content-Type: application/json');
        echo json_encode(['success' => 'Item deleted successfully.']);
    } else {
        // This happens if the ID does not exist in the database.
        header('HTTP/1.1 404 Not Found');
        echo json_encode(['error' => 'Item not found or already deleted.']);
    }

} else {
    header('HTTP/1.1 405 Method Not Allowed');
    echo json_encode(['error' => 'Invalid request method or missing parameters.']);
    exit();
}
?>