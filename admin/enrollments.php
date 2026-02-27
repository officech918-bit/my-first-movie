<?php
// This file is built from the ground up, based on the working categories.php template.
// It handles both creating and editing enrollments.

// --- 1. BOOTSTRAPPING ---
// This section ensures all necessary files and sessions are loaded, providing a safety net
// whether file is accessed directly or through the router.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Load Composer autoloader and Eloquent database connection.
if (!class_exists('App\Models\Enrollment')) {
    require_once __DIR__ . '/../vendor/autoload.php';
    require_once __DIR__ . '/../config/database.php';
}
// Include legacy functions if they are not loaded by middleware.
if (!function_exists('create_thumbnail')) {
    include('inc/requires.php');
}

// Load environment variables from .env file
if (class_exists('Dotenv\Dotenv')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
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


// --- 2. PHP LOGIC ---
// This section handles the core business logic, such as form submissions, data validation,
// and database interactions.
use App\Models\Enrollment;
use App\Models\Category;
use App\Models\CcavResponse;

// Determine the correct menu to include based on the user's role.
// This is guaranteed to be set by the AuthMiddleware or a direct login.
if (isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'webmaster') {
    $menu = 'inc/left-menu-webmaster.php';
} else {
    $menu = 'inc/left-menu-admin.php';
}

// Fetch all active categories for the dropdown.
$categories = Category::where('status', 1)->orderBy('title')->get();

// Determine if we are in "edit" mode or "create" mode.
$is_edit = false;
$enrollment = new Enrollment(); // Start with a new, empty enrollment object.

// If an ID is passed (e.g., from the router as /enrollments/123/edit), load that enrollment.
if (isset($vars['id'])) {
    $is_edit = true;
    $enrollment = Enrollment::find((int)$vars['id']);
    if (!$enrollment) {
        http_response_code(404);
        echo 'Enrollment not found.';
        exit;
    }
}

// Generate a CSRF token to prevent cross-site request forgery.
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

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
    if (empty($_POST['uid'])) {
        $is_error = true;
        $error['uid'] = "User ID cannot be empty";
    }

    if (!$is_error) {
        // Populate the enrollment object with data from the form.
        $enrollment->title = $_POST['title'];
    $enrollment->explanation = strip_tags($_POST['explanation']);
    $enrollment->no_of_files = $_POST['no_of_files'];
    $enrollment->uid = $_POST['uid'];
        $enrollment->fee = $_POST['fee'];
        $enrollment->dt = $_POST['dt'];
        $enrollment->status = $_POST['status'];

        $enrollment->save();

        // If the enrollment status is set to 'completed', update the corresponding ccav_resp entry.
        if ($enrollment->status === 'completed') {
            // Assuming 'order_id' in ccav_resp corresponds to 'id' in enrollments
            $ccavResponse = CcavResponse::where('order_id', $enrollment->id)->first();
            if ($ccavResponse) {
                $ccavResponse->act = 1; // Set act to 1 for payment done
                $ccavResponse->save();
            }
        }

        if ($is_edit) {
            $success_msg = "Enrollment updated successfully!";
        } else {
            // On successful creation, redirect to the edit page.
            // This is the Post-Redirect-Get (PRG) pattern to prevent re-submissions.
            header('Location: ' . $admin_path . 'enrollments/' . $enrollment->id . '/edit?created=true');
            exit;
        }
    }
}

// If the page was loaded with "?created=true", show a success message.
if (isset($_GET['created'])) {
    $success_msg = "Enrollment created successfully!";
}

?>
<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8 no-js"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9 no-js"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en" class="no-js">
<!--<![endif]-->
<!-- BEGIN HEAD (Copied directly from categories.php) -->
<head>
<meta charset="utf-8"/>
<title><?php echo $is_edit ? 'Edit' : 'Add'; ?> Enrollment | My First Movie</title>
<base href="<?php echo $admin_path; ?>">
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
			<?php echo $is_edit ? 'Edit Enrollment' : 'Add New Enrollment'; ?>
			</h3>
			<div class="page-bar">
				<ul class="page-breadcrumb">
					<li>
						<i class="fa fa-home"></i>
						<a href="dashboard">Home</a>
						<i class="fa fa-angle-right"></i>
					</li>
					<li>
						<a href="enrollments">All Enrollments</a>
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
                                    <i class="fa fa-shopping-cart"></i><?php echo htmlspecialchars($enrollment->title ?? 'New Enrollment'); ?>
                                </div>
                                <div class="actions btn-set">
                                    <a href="enrollments" class="btn default"><i class="fa fa-angle-left"></i> Back</a>
                                    <button type="submit" name="submit" class="btn green"><i class="fa fa-check"></i> Save</button>
                                </div>
                            </div>
                            <div class="portlet-body">
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
                                            <select class="form-control" name="title">
                                                <option value="">Select a Category</option>
                                                <?php foreach ($categories as $category): ?>
                                                    <option value="<?php echo htmlspecialchars($category->title); ?>" <?php echo (isset($enrollment->title) && $enrollment->title == $category->title) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($category->title); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <?php if (isset($error['title'])): ?><span class="help-block" style="color: red;"><?php echo $error['title']; ?></span><?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-2 control-label">User ID: <span class="required">*</span></label>
                                        <div class="col-md-10">
                                            <input type="text" class="form-control" name="uid" placeholder="" value="<?php echo htmlspecialchars($enrollment->uid ?? ''); ?>" <?php if ($is_edit) echo 'readonly'; ?>>
                                            <?php if (isset($error['uid'])): ?><span class="help-block" style="color: red;"><?php echo $error['uid']; ?></span><?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-2 control-label">Explanation:</label>
                                        <div class="col-md-10">
                                            <textarea class="form-control" name="explanation" rows="5"><?php echo htmlspecialchars($enrollment->explanation ?? ''); ?></textarea>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-2 control-label">Number of Files:</label>
                                        <div class="col-md-10">
                                            <input type="number" class="form-control" name="no_of_files" placeholder="" value="<?php echo htmlspecialchars($enrollment->no_of_files ?? ''); ?>">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-2 control-label">Fee:</label>
                                        <div class="col-md-10">
                                            <input type="text" class="form-control" name="fee" placeholder="" value="<?php echo htmlspecialchars($enrollment->fee ?? ''); ?>">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-2 control-label">Date:</label>
                                        <div class="col-md-10">
                                            <input type="date" class="form-control" name="dt" value="<?php echo htmlspecialchars($enrollment->dt ? date('Y-m-d', strtotime($enrollment->dt)) : date('Y-m-d')); ?>">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-2 control-label">Status:</label>
                                        <div class="col-md-10">
                                            <select class="form-control" name="status">
                                                <option value="pending" <?php echo (isset($enrollment->status) && $enrollment->status == 'pending') ? 'selected' : ''; ?>>Pending</option>
                                                <option value="completed" <?php echo (isset($enrollment->status) && $enrollment->status == 'completed') ? 'selected' : ''; ?>>Completed</option>
                                                <option value="failed" <?php echo (isset($enrollment->status) && $enrollment->status == 'failed') ? 'selected' : ''; ?>>Failed</option>
                                            </select>
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
<!-- BEGIN CORE PLUGINS (Copied directly from categories.php) -->
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