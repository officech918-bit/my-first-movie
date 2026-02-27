<?php
// Set secure session cookie parameters
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => isset($_SERVER['HTTPS']), // Send cookie only over HTTPS
    'httponly' => true, // Prevent JavaScript access to the session cookie
    'samesite' => 'Strict' // Prevent CSRF attacks
]);

// Start the session if it's not already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include the main application requirements
require_once __DIR__ . '/../../inc/requires.php';

// --- Member-specific Initializations ---

// Initialize the database connection for all member pages.
$database = new MySQLDB();

// Instantiate the web_user object, making it available to all member pages.
$user = new web_user();

// Generate a CSRF token if one doesn't exist in the session.
// This will be used for all forms in the members' area.
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}