<?php
// This file is now loaded through the router, which handles all bootstrapping.
// We add these checks here as a fallback for direct access, ensuring the file is self-sufficient.

// Ensure session is started.
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

// Auth Guard: Ensure user is logged in and is an admin or webmaster.
if (!isset($_SESSION['user_type']) || !in_array($_SESSION['user_type'], ['webmaster', 'admin'])) {
    header('Location: login.php'); // Or a dedicated access-denied page.
    exit();
}

// Get admin path dynamically - same approach as other files
$scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$sub_location = "members"; 
$admin_location = "admin";

$admin_path = "";
if($sub_location != ""){
    $admin_path = $scheme . '://' . $host . '/' . $sub_location . '/' . $admin_location . '/';
} else {
    $admin_path = $scheme . '://' . $host . '/' . $admin_location . '/';
}

// We can directly use the classes and variables set up by the middleware_loader.

use App\Models\Category;

// Determine the correct menu to include based on the user's role.
// This session variable is guaranteed to exist by the AuthMiddleware.
if ($_SESSION['user_type'] == 'webmaster') {
    $menu = 'inc/left-menu-webmaster.php';
} else {
    $menu = 'inc/left-menu-admin.php';
}

// Fetch all categories from the database using our Eloquent model.
// The `orderBy` clause ensures they are displayed in the correct order.
try {
    $categories = Category::orderBy('short_order', 'asc')->get();
} catch (Exception $e) {
    // If there's a database error, show it
    die('Database error: ' . $e->getMessage());
}

// Generate a CSRF token for delete actions.
// The token is stored in the session to be validated on the deletion request.
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
?>
<!DOCTYPE html>
<html lang="en" class="no-js">
<head>
    <meta charset="utf-8"/>
    <title>All Categories | My First Movie</title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="width=device-width, initial-scale=1" name="viewport"/>
    <meta content="Category management page" name="description"/>
    <meta content="" name="author"/>
    <!-- BEGIN GLOBAL MANDATORY STYLES -->
    <link href="http://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700&subset=all" rel="stylesheet" type="text/css"/>
    <link href="assets/global/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css"/>
    <link href="assets/global/plugins/simple-line-icons/simple-line-icons.min.css" rel="stylesheet" type="text/css"/>
    <link href="assets/global/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <link href="assets/global/plugins/uniform/css/uniform.default.css" rel="stylesheet" type="text/css"/>
    <!-- END GLOBAL MANDATORY STYLES -->
    <!-- BEGIN PAGE LEVEL STYLES -->
    <link rel="stylesheet" type="text/css" href="assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css"/>
    <!-- END PAGE LEVEL STYLES -->
    <!-- BEGIN THEME STYLES -->
    <link href="assets/global/css/components.css" id="style_components" rel="stylesheet" type="text/css"/>
    <link href="assets/global/css/plugins.css" rel="stylesheet" type="text/css"/>
    <link href="assets/admin/layout/css/layout.css" rel="stylesheet" type="text/css"/>
    <link href="assets/admin/layout/css/themes/darkblue.css" rel="stylesheet" type="text/css" id="style_color"/>
    <link href="assets/admin/layout/css/custom.css" rel="stylesheet" type="text/css"/>
    <!-- END THEME STYLES -->
    <link rel="shortcut icon" href="favicon.ico"/>
</head>
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
    <div class="page-content-wrapper">
        <div class="page-content">
            <div class="page-bar">
                <ul class="page-breadcrumb">
                    <li>
                        <i class="fa fa-home"></i>
                        <a href="dashboard.php">Home</a>
                        <i class="fa fa-angle-right"></i>
                    </li>
                    <li>
                        <a href="#">Categories</a>
                        <i class="fa fa-angle-right"></i>
                    </li>
                    <li>
                        <a href="all-categories.php">All Categories</a>
                    </li>
                </ul>
            </div>
            <h3 class="page-title">
                All Categories
            </h3>
            <div class="row">
                <div class="col-md-12">
                    <div class="portlet box blue">
                        <div class="portlet-title">
                            <div class="caption">
                                <i class="fa fa-folder-open"></i>Categories
                            </div>
                            <div class="tools">
                                <a href="javascript:;" class="collapse"></a>
                            </div>
                        </div>
                        <div class="portlet-body">
                            <div class="table-toolbar">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="btn-group">
                                            <a href="categories/create" class="btn green">
                                                Add New <i class="fa fa-plus"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <table class="table table-striped table-bordered table-hover" id="sample_1">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Category Name</th>
                                    <th>Image</th>
                                    <th>Status</th>
                                    <th>Edit</th>
                                    <th>Delete</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php if ($categories->count() > 0): ?>
                                    <?php foreach ($categories as $category): ?>
                                        <tr class="odd gradeX" id="cat-<?php echo $category->id; ?>">
                                            <td><?php echo $category->short_order; ?></td>
                                            <td><?php echo htmlspecialchars($category->title); ?></td>
                                            <td>
                                                <img src="<?= getImageUrl($category->cat_img_thumb, $isProduction) ?>" alt="Thumbnail" width="50" 
                                                     onerror="this.src='assets/admin/layout/img/no-image.png';" />
                                            </td>
                                            <td>
                                                <?php if ($category->status == 1): ?>
                                                    <span class="label label-sm label-success">Active</span>
                                                <?php else: ?>
                                                    <span class="label label-sm label-warning">Inactive</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="categories/<?php echo $category->id; ?>/edit" class="btn btn-xs blue">
                                                    <i class="fa fa-edit"></i> Edit
                                                </a>
                                            </td>
                                            <td>
                                                <button class="btn btn-xs red delete-cat" data-id="<?php echo $category->id; ?>">
                                                    <i class="fa fa-trash-o"></i> Delete
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center">No categories found.</td>
                                    </tr>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- BEGIN FOOTER -->
<?php include('inc/footer.php'); ?>
<!-- END FOOTER -->
<!-- BEGIN JAVASCRIPTS -->
<script src="assets/global/plugins/jquery.min.js" type="text/javascript"></script>
<script src="assets/global/plugins/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
<script src="assets/global/plugins/jquery-slimscroll/jquery.slimscroll.min.js" type="text/javascript"></script>
<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="assets/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<!-- END PAGE LEVEL PLUGINS -->
<script src="assets/global/scripts/metronic.js" type="text/javascript"></script>
<script src="assets/admin/layout/scripts/layout.js" type="text/javascript"></script>
<script>
    jQuery(document).ready(function() {
        Metronic.init(); // init metronic core components
        Layout.init(); // init current layout
        $('#sample_1').DataTable();

        // Handle the delete button click
        $('.delete-cat').on('click', function() {
            var catId = $(this).data('id');
            if (confirm('Are you sure you want to delete this category? This action cannot be undone.')) {
                $.ajax({
                    url: 'delete_category.php',
                    type: 'POST',
                    data: {
                        id: catId,
                        csrf_token: '<?php echo $csrf_token; ?>'
                    },
                    dataType: 'json', // Expect a JSON response from the server
                    success: function(response) {
                        // Check if the server confirmed the deletion
                        if (response.success) {
                            // On success, remove the table row from the DOM
                            $('#cat-' + catId).fadeOut(300, function() { $(this).remove(); });
                        } else {
                            // If the server returned an error, display it
                            alert('Error: ' + response.message);
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        // Handle network errors or unexpected server responses
                        alert('An error occurred: ' + textStatus + ' - ' + errorThrown);
                    }
                });
            }
        });
    });
</script>
<!-- END JAVASCRIPTS -->
</body>
</html>