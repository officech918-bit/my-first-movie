<?php
require_once('inc/requires.php');

// Start the session if it's not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// CSRF token validation
if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    http_response_code(403);
    exit('Invalid CSRF token.');
}

// Whitelist of allowed tables
$allowed_tables = ['bts', 'seasons', 'categories', 'testimonials', 'core_team', 'panelists'];
$table = $_POST['table'] ?? '';

if (!in_array($table, $allowed_tables, true)) {
    http_response_code(400);
    exit('Invalid table specified.');
}

$ids = $_POST['ids'] ?? [];
if (!is_array($ids)) {
    http_response_code(400);
    exit('Invalid IDs provided.');
}

// Use the database connection from the bootstrap file
$database = new MySQLDB();
$db = $database->db;

// Prepare the statement
$query = "UPDATE `$table` SET short_order = ? WHERE id = ?";
$stmt = $db->prepare($query);

if (!$stmt) {
    http_response_code(500);
    exit('Failed to prepare statement: ' . $db->error);
}

$count = 1;
foreach ($ids as $id) {
    // Bind parameters and execute
    try {
        $stmt->execute([$count, $id]);
    } catch (PDOException $e) {
        // Optional: Log error, but don't expose details to the user
        error_log('Failed to update sorting for table ' . $table . ': ' . $e->getMessage());
    }
    $count++;
}


$stmt->close();

// Send a success response
echo 'Sorting updated successfully.';
?>