<?php
// Ensure the session is started for potential future use.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get admin path dynamically for redirects
$scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$requestUri = $_SERVER['REQUEST_URI'];

// Extract the actual path from the current request
$uriParts = explode('/', trim($requestUri, '/'));
$adminIndex = array_search('admin', $uriParts);

if ($adminIndex !== false) {
    $admin_path = $scheme . '://' . $host . '/' . implode('/', array_slice($uriParts, 0, $adminIndex + 1)) . '/';
} else {
    $admin_path = $scheme . '://' . $host . '/admin/'; // fallback
}

// CRITICAL: Load the Composer autoloader and Eloquent ORM.
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/database.php';

use App\Models\Season;
use Illuminate\Database\Capsule\Manager as DB; // Use the DB capsule for transactions.

// Get season ID from router parameter or POST data
$seasonId = null;
if (isset($vars['id']) && is_numeric($vars['id'])) {
    $seasonId = (int)$vars['id'];
} elseif (isset($_POST['id']) && is_numeric($_POST['id'])) {
    $seasonId = (int)$_POST['id'];
} elseif (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $seasonId = (int)$_GET['id'];
}

if ($seasonId) {

    try {
        // Use a database transaction to ensure both operations succeed or fail together.
        DB::transaction(function () use ($seasonId) {
            // 1. Reset all other seasons to not be the default.
            Season::where('is_default', 1)->update(['is_default' => 0]);

            // 2. Find the selected season and set it as the default.
            $season = Season::find($seasonId);
            if ($season) {
                $season->is_default = 1;
                $season->status = 1;
                $season->save();
            }
        });
    } catch (\Exception $e) {
        // If the transaction fails, log the error for debugging.
        error_log('Failed to set default season: ' . $e->getMessage());
    }
}

// Redirect back to the seasons list page using an absolute path.
header('Location: ' . $admin_path . 'all-seasons.php');
exit();
