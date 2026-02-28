<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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

// Load database connection using the same approach as dashboard.php
include('inc/requires.php');

// Get admin path dynamically for CSS/JS loading
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

// Get the correct base path from current request
$script = $_SERVER['SCRIPT_NAME'] ?? '';
$basePath = '';
if ($script) {
    $parts = explode('/', trim($script, '/'));
    if (!empty($parts)) {
        $basePath = '/' . $parts[0]; // This will give us /myfirstmovie3
    }
}
$correct_base_path = $basePath;

use App\Models\Panelist;

// DEBUG: Check database table structure
try {
    $table_info = \Illuminate\Support\Facades\DB::select("DESCRIBE panelists");
    error_log("Panelists table structure: " . json_encode($table_info));
    
    // Also check if there are any existing records
    $count = \Illuminate\Support\Facades\DB::select("SELECT COUNT(*) as count FROM panelists");
    error_log("Panelists record count: " . json_encode($count));
    
    // Check the highest ID
    $max_id = \Illuminate\Support\Facades\DB::select("SELECT MAX(id) as max_id FROM panelists");
    error_log("Panelists max ID: " . json_encode($max_id));
} catch (\Exception $e) {
    error_log("Error checking panelists table: " . $e->getMessage());
}

// Security check for user type
if (!isset($_SESSION['user_type']) || !in_array($_SESSION['user_type'], ['webmaster', 'admin'])) {
    http_response_code(403);
    exit('Forbidden: You do not have permission to access this page.');
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/**
 * Get image URL based on environment
 */
function getImageUrl(string $imagePath, bool $isProduction): string {
    if (empty($imagePath)) {
        return 'assets/admin/layout/img/no-image.png';
    }
    
    if ($isProduction) {
        // In production, assume S3 URLs are stored
        return $imagePath;
    } else {
        // In local development, convert relative paths to full URLs
        if (strpos($imagePath, 'http') === 0) {
            return $imagePath; // Already a full URL
        }
        return '../' . $imagePath; // Convert to relative path
    }
}

$panelist = new Panelist();
$is_edit = false;
$page_title = 'Add New Panelist';
$form_action = 'panelist/create';

// Check if we are editing an existing panelist (ID from router $vars)
if (isset($vars['id']) && is_numeric($vars['id'])) {
    $id = $vars['id'];
    error_log("Editing panelist with ID: " . $id); // DEBUG
    $panelist = Panelist::find($id);
    if (!$panelist) {
        http_response_code(404);
        exit('Panelist not found.');
    }
    $is_edit = true;
    $page_title = 'Edit Panelist';
    $form_action = "panelist/{$id}/edit";
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die('Invalid CSRF token');
    }

    $panelist->name = $_POST['name'] ?? '';
    $panelist->intro = $_POST['intro'] ?? '';
    $panelist->display_order = (int)($_POST['display_order'] ?? 0);
    $panelist->create_date = $panelist->create_date ?? date('Y-m-d H:i:s');

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $image_name = basename($_FILES["image"]["name"]);
        
        // Use S3Uploader for environment-based upload
        try {
            $s3Path = 'panelists/' . $image_name;
            $uploadedUrl = $s3Uploader->uploadFile($_FILES["image"]["tmp_name"], $s3Path);
            
            // Store the URL returned by S3Uploader
            $panelist->image = $uploadedUrl;
            
        } catch (Exception $e) {
            $error = 'Upload failed: ' . $e->getMessage();
        }
    }

    $panelist->save();

    // DEBUG: Check the ID after save and database state
    $saved_id = $panelist->id;
    error_log("Panelist saved with ID: " . $saved_id);
    
    // DEBUG: Check the last inserted ID from database
    try {
        $last_id = \Illuminate\Support\Facades\DB::select("SELECT LAST_INSERT_ID() as last_id");
        error_log("Last inserted ID from DB: " . json_encode($last_id));
    } catch (\Exception $e) {
        error_log("Error checking last inserted ID: " . $e->getMessage());
    }

    $_SESSION['flash_message'] = 'Panelist ' . ($is_edit ? 'updated' : 'added') . ' successfully.';
    header('Location: ' . $admin_path . 'panelists');
    exit;
}

// Determine which menu to include based on user type
$menu = 'inc/left-menu-user.php'; // Default
if (isset($_SESSION['user_type'])) {
    if ($_SESSION['user_type'] == 'admin') {
        $menu = 'inc/left-menu-admin.php';
    } elseif ($_SESSION['user_type'] == 'webmaster') {
        $menu = 'inc/left-menu-webmaster.php';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <base href="<?php echo $admin_path; ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <link href="http://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700&subset=all" rel="stylesheet" type="text/css"/>
    <link href="assets/global/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css"/>
    <link href="assets/global/plugins/simple-line-icons/simple-line-icons.min.css" rel="stylesheet" type="text/css"/>
    <link href="assets/global/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <link href="assets/global/css/components.css" id="style_components" rel="stylesheet" type="text/css"/>
    <link href="assets/global/css/plugins.css" rel="stylesheet" type="text/css"/>
    <link href="assets/admin/layout/css/layout.css" rel="stylesheet" type="text/css"/>
    <link id="style_color" href="assets/admin/layout/css/themes/darkblue.css" rel="stylesheet" type="text/css"/>
    <link href="assets/admin/layout/css/custom.css" rel="stylesheet" type="text/css"/>
    <link rel="shortcut icon" href="favicon.ico"/>
</head>
<body class="page-header-fixed page-quick-sidebar-over-content">
<?php include('inc/header.php'); ?>
<div class="clearfix"></div>
<div class="page-container">
    <div class="page-sidebar-wrapper">
        <div class="page-sidebar navbar-collapse collapse">
            <?php include($menu); ?>
        </div>
    </div>
    <div class="page-content-wrapper">
        <div class="page-content">
            <h3 class="page-title"><?php echo htmlspecialchars($page_title); ?></h3>
            <div class="page-bar">
                <ul class="page-breadcrumb">
                    <li><i class="fa fa-home"></i><a href="dashboard.php">Dashboard</a><i class="fa fa-angle-right"></i></li>
                    <li><a href="panelists">All Panelists</a><i class="fa fa-angle-right"></i></li>
                    <li><span><?php echo $is_edit ? 'Edit' : 'Add'; ?></span></li>
                </ul>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="portlet box blue-hoki">
                        <div class="portlet-title">
                            <div class="caption">
                                <i class="fa fa-user"></i>Panelist Details
                            </div>
                        </div>
                        <div class="portlet-body form">
                            <form action="<?php echo htmlspecialchars($form_action); ?>" method="POST" enctype="multipart/form-data" class="form-horizontal">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                <div class="form-body">
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Name</label>
                                        <div class="col-md-4">
                                            <input type="text" name="name" class="form-control" placeholder="Enter name" value="<?php echo htmlspecialchars($panelist->name ?? ''); ?>" required>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Introduction</label>
                                        <div class="col-md-4">
                                            <textarea name="intro" class="form-control" rows="3"><?php echo htmlspecialchars($panelist->intro ?? ''); ?></textarea>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Display Order</label>
                                        <div class="col-md-4">
                                            <input type="number" name="display_order" class="form-control" value="<?php echo htmlspecialchars((string)($panelist->display_order ?? 0)); ?>" required>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Image</label>
                                        <div class="col-md-4">
                                            <input type="file" name="image" class="form-control">
                                            <?php if ($is_edit && $panelist->image): ?>
                                                <p class="help-block">Current image:</p>
                                                <img src="<?php echo getImageUrl($panelist->image, $isProduction); ?>" alt="<?php echo htmlspecialchars($panelist->name); ?>" style="max-width: 200px; margin-top: 10px;" onerror="this.src='<?php echo $admin_path; ?>assets/admin/layout/img/no-image.png';">
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-actions">
                                    <div class="row">
                                        <div class="col-md-offset-3 col-md-9">
                                            <button type="submit" class="btn green">Submit</button>
                                            <a href="panelists" class="btn default">Cancel</a>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include('inc/footer.php'); ?>
<script src="assets/global/plugins/jquery.min.js" type="text/javascript"></script>
<script src="assets/global/plugins/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
<script src="assets/global/scripts/metronic.js" type="text/javascript"></script>
<script src="assets/admin/layout/scripts/layout.js" type="text/javascript"></script>
<script>
jQuery(document).ready(function() {
   Metronic.init();
   Layout.init();
});
</script>
</body>
</html>