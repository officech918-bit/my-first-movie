<?php

declare(strict_types=1);

use App\Models\Testimonial;

require_once __DIR__ . '/../vendor/autoload.php';
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

// Define the upload directory
$uploadDir = dirname(__DIR__) . '/uploads/testimonials/';

// Delete the logo and thumbnail if they exist
if ($testimonial->logo && file_exists($uploadDir . $testimonial->logo)) {
    unlink($uploadDir . $testimonial->logo);
}

if ($testimonial->logo_thumb && file_exists($uploadDir . $testimonial->logo_thumb)) {
    unlink($uploadDir . $testimonial->logo_thumb);
}

// Delete the testimonial from the database
$testimonial->delete();

header('Content-Type: application/json');
echo json_encode(['success' => true]);
exit();