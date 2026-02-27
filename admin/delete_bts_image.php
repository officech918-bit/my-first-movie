<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

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
        // Define the path to the image file
        $image_path = __DIR__ . '/uploads/bts/' . $image->image;

        // Delete the file from the server
        if (file_exists($image_path)) {
            unlink($image_path);
        }
        
        // Also delete the thumbnail if it exists
        if ($image->image_thumb) {
            $thumb_path = __DIR__ . '/uploads/bts/' . $image->image_thumb;
            if (file_exists($thumb_path)) {
                unlink($thumb_path);
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