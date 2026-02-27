<?php
declare(strict_types=1);

// Set the content type to JSON for AJAX responses.
header('Content-Type: application/json');

// 1. BOOTSTRAPPING: Load essential configurations, database, and session management.
require_once 'inc/requires.php';

// 2. DEPENDENCIES
use App\Models\Season;

// Initialize the response array.
$response = ['success' => false, 'message' => 'An unknown error occurred.'];

if (isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'webmaster') {
    $menu = 'inc/left-menu-webmaster.php';
} else {
    $menu = 'inc/left-menu-admin.php';
}


// 4. CSRF TOKEN VALIDATION
if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
    $response['message'] = 'Invalid security token. Please refresh the page and try again.';
    http_response_code(403); // Forbidden
    echo json_encode($response);
    exit;
}

// 5. INPUT VALIDATION
if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    $response['message'] = 'Invalid season ID provided.';
    http_response_code(400); // Bad Request
    echo json_encode($response);
    exit;
}

$season_id = (int)$_POST['id'];

// 6. DATABASE OPERATION
try {
    $season = Season::find($season_id);

    if ($season) {
        // Optional: Check for related records before deleting.
        // For example, if seasons have episodes, you might want to prevent deletion
        // if episodes are present, or handle their deletion here.
        // if ($season->episodes()->count() > 0) {
        //     $response['message'] = 'Cannot delete season because it has episodes associated with it.';
        //     http_response_code(409); // Conflict
        //     echo json_encode($response);
        //     exit;
        // }

        $season->delete();
        $response['success'] = true;
        $response['message'] = 'Season has been successfully deleted.';
        http_response_code(200); // OK
    } else {
        $response['message'] = 'Season not found. It may have already been deleted.';
        http_response_code(404); // Not Found
    }
} catch (\Exception $e) {
    // In a real application, you should log the error message.
    // error_log("Error deleting season {$season_id}: " . $e->getMessage());
    $response['message'] = 'A database error occurred while trying to delete the season.';
    http_response_code(500); // Internal Server Error
}

// 7. SEND RESPONSE
echo json_encode($response);
exit;