<?php
// Load environment variables
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
    if (class_exists('Dotenv\Dotenv')) {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
        $dotenv->load();
    }
}

// Load S3Uploader for environment detection
if (file_exists(__DIR__ . '/../classes/S3Uploader.php')) {
    require_once __DIR__ . '/../classes/S3Uploader.php';
    $s3Uploader = new S3Uploader();
    $isProduction = $s3Uploader->isS3Enabled();
}

// Bootstrap the application
require_once __DIR__ . '/../config/database.php';

use App\Models\Enrollment;
use Illuminate\Database\Capsule\Manager as DB;

// Start the session if it's not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// CSRF token validation
if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    http_response_code(403);
    exit('Invalid CSRF token.');
}

// Get base path information
$correct_base_path = $_POST['correct_base_path'] ?? '';
$admin_base_url = $_POST['admin_base_url'] ?? '';

// Whitelist of allowed tables and their configurations
$allowed_tables = [
    'testimonials' => ['type' => 'image', 'type_field' => 'logo', 'text_field' => 'company', 'path_prefix' => 'uploads/testimonials/'],
    'categories' => ['type' => 'image', 'type_field' => 'cat_img', 'text_field' => 'title', 'path_prefix' => 'uploads/categories/'],
    'panelists' => ['type' => 'image', 'type_field' => 'image', 'text_field' => 'name', 'path_prefix' => 'uploads/panelists/'],
    'core_team' => ['type' => 'image', 'type_field' => 'image', 'text_field' => 'name', 'path_prefix' => 'uploads/core_team/'],
    'seasons' => ['type' => 'text', 'type_field' => 'title', 'text_field' => 'title'],
    'behind_the_scenes' => ['type' => 'text', 'type_field' => 'title', 'text_field' => 'title'],
];

$table_name = $_POST['table'] ?? '';

if (!array_key_exists($table_name, $allowed_tables)) {
    http_response_code(400);
    exit('Invalid section specified.');
}

$config = $allowed_tables[$table_name];
$type = $config['type'];
$type_field = $config['type_field'];
$record_text_field = $config['text_field'];
$path_prefix = $config['path_prefix'] ?? '';

try {
    $results = DB::table($table_name)
        ->where('status', '=', 1)
        ->orderBy('short_order', 'asc')
        ->get();

    $echo_str = '';
    foreach ($results as $row) {
        $item_html = '';
        if ($type === 'image') {
            $image_path = htmlspecialchars($row->$type_field, ENT_QUOTES, 'UTF-8');
            
            // Check if it's an S3 URL or local path
            if (strpos($image_path, 'http') === 0) {
                // S3 URL or full URL
                $image_url = $image_path;
            } else {
                // Local path - check if already includes the path prefix
                if (strpos($image_path, $path_prefix) === 0) {
                    // Already includes the path, just prepend base path
                    $image_url = $correct_base_path . "/" . $image_path;
                } else {
                    // Just the filename, prepend full path
                    $image_url = $correct_base_path . "/" . $path_prefix . $image_path;
                }
            }
            
            $item_html = '<a href="javascript:void(0);" style="float:none;" class="image_link"><img src="' . $image_url . '" alt="" onerror="this.src=\'' . $admin_base_url . 'assets/admin/layout/img/no-image.png\';" /></a><br>';
        } else {
            $item_html = '<a href="javascript:void(0);" style="float:none;" class="image_link"></a><br>';
        }
        $text = htmlspecialchars($row->$record_text_field, ENT_QUOTES, 'UTF-8');
        $echo_str .= '<li id="image_li_' . $row->id . '" class="ui-sortable-handle">' . $item_html . ' ' . $text . '</li>';
    }

    echo $echo_str;

} catch (Exception $e) {
    http_response_code(500);
    // Log the detailed error message for debugging, but don't expose it to the user.
    error_log('Database query failed: ' . $e->getMessage());
    exit('An error occurred while fetching data.');
}
?>