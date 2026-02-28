<?php
// This file is built from the ground up, based on working dashboard.php template.
// It handles both creating and editing categories.

// --- 1. BOOTSTRAPPING ---
// This section ensures all necessary files and sessions are loaded, providing a safety net
// whether the file is accessed directly or through the router.

// Start the session if it hasn't been started yet
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

// Auth Guard: Block access if user is not an authorized admin or webmaster.
if (!isset($_SESSION['user_type']) || !in_array($_SESSION['user_type'], ['webmaster', 'admin'])) {
    // You can redirect to a login page or show a generic access denied error.
    http_response_code(403);
    exit('Forbidden: You do not have permission to access this page.');
}

// Load Composer autoloader and Eloquent database connection.
if (!class_exists('App\\Models\\Category')) {
    require_once __DIR__ . '/../vendor/autoload.php';
    require_once __DIR__ . '/../config/database.php';
}
// Include legacy functions if they are not loaded by middleware.
if (!function_exists('create_thumbnail')) {
    include('inc/requires.php');
}


// --- 2. PHP LOGIC ---
use App\Models\Category;

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

// Define a base URL for consistent pathing.
define('BASE_URL', $admin_path);

// Define the upload directory for category images.
define('UPLOAD_DIR', 'uploads/categories/');

// Determine the correct menu to include based on the user's role.
// This is guaranteed to be set by the AuthMiddleware or a direct login.
if (isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'webmaster') {
    $menu = 'inc/left-menu-webmaster.php';
} else {
    $menu = 'inc/left-menu-admin.php';
}

// Determine if we are in "edit" mode or "create" mode.
$is_edit = false;
$category = new Category(); // Start with a new, empty category object.

// If an ID is passed (e.g., from the router as /categories/123/edit), load that category.
if (isset($vars['id'])) {
    $is_edit = true;
    // Use a more descriptive variable name.
    $category_id = (int)$vars['id'];
    $category = Category::find($category_id);
    if (!$category) {
        http_response_code(404);
        echo 'Category not found.';
        exit;
    }
}

// Generate a CSRF token to prevent cross-site request forgery.
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

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

$error = [];
$success_msg = '';

// Handle the form submission for both creating and updating.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("CSRF token validation failed.");
    }

    // --- Validation ---
    $is_error = false;
    if (empty($_POST['title'])) {
        $is_error = true;
        $error['title'] = "Title cannot be empty";
    }
    // The original code already had numeric validation, this block is being updated to match the instruction's context.
    if (!empty($_POST['fee']) && !is_numeric($_POST['fee'])) {
        $is_error = true;
        $error['fee'] = "Fee must be a numeric value.";
    }

    if (!$is_error) {
        // Populate the category object with data from the form.
        $category->title = $_POST['title'];
        $category->display_note = $_POST['display_note'];
        $category->status = (int)$_POST['status'];
        $category->fee = $_POST['fee'];
        $category->modified_by = $_SESSION['user_id'] ?? 0;

        // If this is a new category, calculate its sort order.
        if (!$is_edit) {
            $category->short_order = (Category::max('short_order') ?? 0) + 1;
        }

        // --- Secure File Upload Logic ---
        // The comprehensive validation logic from the original content is preserved as it meets the instruction's requirements.
        if (isset($_FILES['cat_img']) && $_FILES['cat_img']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['cat_img']['tmp_name'];
            $fileName = $_FILES['cat_img']['name'];
            $fileSize = $_FILES['cat_img']['size'];

            // 1. Check file size (e.g., 2MB limit)
            if ($fileSize > 2 * 1024 * 1024) {
                $error['file'] = 'File size must be less than 2MB.';
            } else {
                // 2. Verify MIME type
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($finfo, $fileTmpPath);
                finfo_close($finfo);
                $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

                if (!in_array($mime, $allowedMimeTypes)) {
                    $error['file'] = 'Invalid file type. Only JPG, PNG, GIF, and WEBP are allowed.';
                }
                // 3. Verify it's a real image
                elseif (getimagesize($fileTmpPath) === false) {
                    $error['file'] = 'The uploaded file is not a valid image.';
                } else {
                    // Sanitize filename and get extension
                    $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
                    $newFileName = 'category_' . time() . '.' . $fileExtension;
                    
                    // Use S3Uploader for environment-based upload
                try {
                    $s3Path = 'categories/' . $newFileName;
                    $uploadedUrl = $s3Uploader->uploadFile($fileTmpPath, $s3Path);
                    
                    // Store the URL returned by S3Uploader
                    $category->cat_img = $uploadedUrl;
                    $category->cat_img_thumb = $uploadedUrl;
                    
                } catch (Exception $e) {
                    $error['file'] = 'Upload failed: ' . $e->getMessage();
                }
            }
        }
        }

        // Only save if there are no file upload errors
        if (!isset($error['file'])) {
            $category->save();

            if ($is_edit) {
                $success_msg = "Category updated successfully!";
            } else {
                // On successful creation, redirect to the edit page (PRG pattern).
                header('Location: ' . BASE_URL . 'categories/' . $category->id . '/edit?created=true');
                exit;
            }
        }
    }
}

// If the page was loaded with "?created=true", show a success message.
if (isset($_GET['created'])) {
    $success_msg = "Category created successfully!";
}

?>
<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8 no-js"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9 no-js"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en" class="no-js">
<!--<![endif]-->
<!-- BEGIN HEAD (Copied directly from dashboard.php) -->
<head>
<meta charset="utf-8"/>
<title><?php echo $is_edit ? 'Edit' : 'Add'; ?> Category | My First Movie</title>
<base href="<?php echo BASE_URL; ?>">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta content="width=device-width, initial-scale=1" name="viewport"/>
<meta content="" name="description"/>
<meta content="" name="author"/>
<!-- BEGIN GLOBAL MANDATORY STYLES -->
<link href="http://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700&subset=all" rel="stylesheet" type="text/css"/>
<link href="assets/global/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css"/>
<link href="assets/global/plugins/simple-line-icons/simple-line-icons.min.css" rel="stylesheet" type="text/css"/>
<link href="assets/global/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
<link href="assets/global/plugins/uniform/css/uniform.default.css" rel="stylesheet" type="text/css"/>
<link href="assets/global/plugins/bootstrap-switch/css/bootstrap-switch.min.css" rel="stylesheet" type="text/css"/>
<!-- END GLOBAL MANDATORY STYLES -->
<!-- BEGIN THEME STYLES -->
<link href="assets/global/css/components.css" id="style_components" rel="stylesheet" type="text/css"/>
<link href="assets/global/css/plugins.css" rel="stylesheet" type="text/css"/>
<link href="assets/admin/layout/css/layout.css" rel="stylesheet" type="text/css"/>
<link href="assets/admin/layout/css/themes/darkblue.css" rel="stylesheet" type="text/css" id="style_color"/>
<link href="assets/admin/layout/css/custom.css" rel="stylesheet" type="text/css"/>
<!-- END THEME STYLES -->
<link rel="shortcut icon" href="favicon.ico"/>
</head>
<!-- END HEAD -->
<!-- BEGIN BODY -->
<body class="page-header-fixed page-quick-sidebar-over-content page-style-square">
<!-- BEGIN HEADER -->
<?php include('inc/header.php'); ?>
<!-- END HEADER -->
<div class="clearfix">
</div>
<!-- BEGIN CONTAINER -->
<div class="page-container">
	<!-- BEGIN SIDEBAR -->
	<div class="page-sidebar-wrapper">
		<div class="page-sidebar navbar-collapse collapse">
			<!-- BEGIN SIDEBAR MENU -->
			<?php include($menu); ?>
			<!-- END SIDEBAR MENU -->
		</div>
	</div>
	<!-- END SIDEBAR -->
	<!-- BEGIN CONTENT -->
	<div class="page-content-wrapper">
		<div class="page-content">
			<!-- BEGIN PAGE HEADER-->
			<h3 class="page-title">
			<?php echo $is_edit ? 'Edit Category' : 'Add New Category'; ?>
			</h3>
			<div class="page-bar">
				<ul class="page-breadcrumb">
					<li>
						<i class="fa fa-home"></i>
						<a href="dashboard.php">Home</a>
						<i class="fa fa-angle-right"></i>
					</li>
					<li>
						<a href="all-categories.php">All Categories</a>
                        <i class="fa fa-angle-right"></i>
					</li>
                    <li>
						<a href="#"><?php echo $is_edit ? 'Edit' : 'Add'; ?></a>
					</li>
				</ul>
			</div>
			<!-- END PAGE HEADER-->
			<!-- BEGIN MAIN CONTENT -->
            <div class="row">
                <div class="col-md-12">
                    <form class="form-horizontal form-row-seperated" action="" method="POST" enctype="multipart/form-data">
                        <div class="portlet">
                            <div class="portlet-title">
                                <div class="caption">
                                    <i class="fa fa-shopping-cart"></i><?php echo htmlspecialchars($category->title ?? 'New Category'); ?>
                                </div>
                                <div class="actions btn-set">
                                    <a href="all-categories.php" class="btn default"><i class="fa fa-angle-left"></i> Back</a>
                                    <button type="submit" name="submit" class="btn green"><i class="fa fa-check"></i> Save</button>
                                </div>
                            </div>
                            <div class="portlet-body">
                                <?php if (isset($error['file'])): ?>
                                    <div class="alert alert-danger">
                                        <?php echo $error['file']; ?>
                                    </div>
                                <?php endif; ?>
                                <?php if ($success_msg): ?>
                                    <div class="alert alert-success">
                                        <?php echo $success_msg; ?>
                                    </div>
                                <?php endif; ?>
                                <div class="form-body">
                                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>"/>
                                    <div class="form-group">
                                        <label class="col-md-2 control-label">Title: <span class="required">*</span></label>
                                        <div class="col-md-10">
                                            <input type="text" class="form-control" name="title" placeholder="" value="<?php echo htmlspecialchars($category->title ?? ''); ?>">
                                            <?php if (isset($error['title'])): ?><span class="help-block" style="color: red;"><?php echo $error['title']; ?></span><?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-2 control-label">Display Note:</label>
                                        <div class="col-md-10">
                                            <textarea class="form-control" name="display_note"><?php echo htmlspecialchars($category->display_note ?? ''); ?></textarea>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-2 control-label">Fee:</label>
                                        <div class="col-md-10">
                                            <input type="text" class="form-control" name="fee" placeholder="" value="<?php echo htmlspecialchars($category->fee ?? ''); ?>">
                                            <?php if (isset($error['fee'])): ?><span class="help-block" style="color: red;"><?php echo $error['fee']; ?></span><?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-2 control-label">Status: <span class="required">*</span></label>
                                        <div class="col-md-10">
                                            <select class="table-group-action-input form-control input-medium" name="status">
                                                <option value="1" <?php echo ($category->status ?? 1) == 1 ? 'selected' : ''; ?>>Active</option>
                                                <option value="0" <?php echo ($category->status ?? 1) == 0 ? 'selected' : ''; ?>>Inactive</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-2 control-label">Category Image:</label>
                                        <div class="col-md-10">
                                            <input type="file" name="cat_img">
                                            <?php if ($is_edit && $category->cat_img): ?>
                                                <p class="help-block">Current image: 
                                                    <img src="<?= getImageUrl($category->cat_img, $isProduction) ?>" width="50" alt=""
                                                         onerror="this.src='assets/admin/layout/img/no-image.png';" />
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
			<!-- END MAIN CONTENT -->
		</div>
	</div>
	<!-- END CONTENT -->
</div>
<!-- END CONTAINER -->
<!-- BEGIN FOOTER -->
<?php include('inc/footer.php'); ?>
<!-- END FOOTER -->
<!-- BEGIN JAVASCRIPTS(Load javascripts at bottom, this will reduce page load time) -->
<!-- BEGIN CORE PLUGINS (Copied directly from dashboard.php) -->
<!--[if lt IE 9]>
<script src="assets/global/plugins/respond.min.js"></script>
<script src="assets/global/plugins/excanvas.min.js"></script>
<![endif]-->
<script src="assets/global/plugins/jquery.min.js" type="text/javascript"></script>
<script src="assets/global/plugins/jquery-migrate.min.js" type="text/javascript"></script>
<!-- IMPORTANT! Load jquery-ui-1.10.3.custom.min.js before bootstrap.min.js to fix bootstrap tooltip conflict with jquery ui tooltip -->
<script src="assets/global/plugins/jquery-ui/jquery-ui-1.10.3.custom.min.js" type="text/javascript"></script>
<script src="assets/global/plugins/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
<script src="assets/global/plugins/bootstrap-hover-dropdown/bootstrap-hover-dropdown.min.js" type="text/javascript"></script>
<script src="assets/global/plugins/jquery-slimscroll/jquery.slimscroll.min.js" type="text/javascript"></script>
<script src="assets/global/plugins/jquery.blockui.min.js" type="text/javascript"></script>
<script src="assets/global/plugins/jquery.cokie.min.js" type="text/javascript"></script>
<script src="assets/global/plugins/uniform/jquery.uniform.min.js" type="text/javascript"></script>
<script src="assets/global/plugins/bootstrap-switch/js/bootstrap-switch.min.js" type="text/js"></script>
<!-- END CORE PLUGINS -->
<!-- BEGIN PAGE LEVEL SCRIPTS -->
<script src="assets/global/scripts/metronic.js" type="text/javascript"></script>
<script src="assets/admin/layout/scripts/layout.js" type="text/javascript"></script>
<!-- END PAGE LEVEL SCRIPTS -->
<script>
jQuery(document).ready(function() {
   Metronic.init(); // init metronic core componets
   Layout.init(); // init layout
});
</script>
<!-- END JAVASCRIPTS -->
</body>
<!-- END BODY -->
</html>