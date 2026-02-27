<?php
// This script handles the AJAX request to delete a category.

// 1. Bootstrap the application
// This ensures the session, autoloader, and database are ready.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/database.class.php';
require_once __DIR__ . '/../classes/main.class.php';

use App\Models\Category;

// 2. Security Checks
// Enforce POST method, validate CSRF, and require webmaster role.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    http_response_code(403); // Forbidden
    echo json_encode(['success' => false, 'message' => 'CSRF token validation failed.']);
    exit;
}

if (!isset($_SESSION['user_type']) || !in_array($_SESSION['user_type'], ['webmaster', 'admin'])) {
    http_response_code(403); // Forbidden
    echo json_encode(['success' => false, 'message' => 'You do not have permission to perform this action.']);
    exit;
}


// 3. Deletion Logic
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($id === 0) {
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'message' => 'Invalid Category ID.']);
    exit;
}

// Find the category using our Eloquent model.
$category = Category::find($id);

if ($category) {
    // --- Delete associated files ---
    // Use realpath to prevent path traversal vulnerabilities and ensure we're deleting from the correct directory.
    $uploadBaseDir = realpath(__DIR__ . '/../uploads/categories');
    
    if ($category->cat_img) {
        $imagePath = realpath($uploadBaseDir . '/' . basename($category->cat_img));
        if ($imagePath && strpos($imagePath, $uploadBaseDir) === 0 && file_exists($imagePath)) {
            unlink($imagePath);
        }
    }
    if ($category->cat_img_thumb) {
        $thumbPath = realpath($uploadBaseDir . '/' . basename($category->cat_img_thumb));
        if ($thumbPath && strpos($thumbPath, $uploadBaseDir) === 0 && file_exists($thumbPath)) {
            unlink($thumbPath);
        }
    }

    // Delete the category from the database.
    $category->delete();

    // Return a success response.
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Category deleted successfully.']);
} else {
    // If the category was not found.
    http_response_code(404); // Not Found
    echo json_encode(['success' => false, 'message' => 'Category not found.']);
}


exit;