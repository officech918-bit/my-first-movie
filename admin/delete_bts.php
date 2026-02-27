<?php
declare(strict_types=1);

// Start session at the very top to access $_SESSION variables
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('Asia/Kolkata');

// 1. Bootstrap the application (loads autoloader, Eloquent, middleware)
// The middleware will handle the authentication check.
require_once 'inc/middleware_loader.php';

use App\Models\BehindTheScene;

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

    // 3. Use Eloquent to Delete the record
    // The destroy method returns the number of records deleted.
    $deleted_count = BehindTheScene::destroy($bts_id);

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