<?php
require_once 'inc/requires.php';

// Load environment variables from .env file
if (class_exists('Dotenv\Dotenv')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
}

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

if (!isset($_GET['id'])) {
    header('Location: all-enrollments.php');
    exit();
}

$enrollment = App\Models\Enrollment::with('user')->find($_GET['id']);

if (!$enrollment) {
    header('Location: all-enrollments.php');
    exit();
}

$menu = 'enrollments'; // Set the active menu item for the sidebar

// Determine the correct menu to include based on the user's role.
if (isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'webmaster') {
    $sidebar_menu = 'inc/left-menu-webmaster.php';
} else {
    $sidebar_menu = 'inc/left-menu-admin.php';
}
?>
<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8 no-js"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9 no-js"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en" class="no-js">
<!--<![endif]-->
<head>
<meta charset="utf-8"/>
<title>View Enrollment | My First Movie</title>
<base href="<?php echo $admin_path; ?>">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta content="width=device-width, initial-scale=1" name="viewport"/>
<meta content="" name="description"/>
<meta content="" name="author"/>
<link href="http://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700&subset=all" rel="stylesheet" type="text/css"/>
<link href="assets/global/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css"/>
<link href="assets/global/plugins/simple-line-icons/simple-line-icons.min.css" rel="stylesheet" type="text/css"/>
<link href="assets/global/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
<link href="assets/global/plugins/uniform/css/uniform.default.css" rel="stylesheet" type="text/css"/>
<link href="assets/global/plugins/bootstrap-switch/css/bootstrap-switch.min.css" rel="stylesheet" type="text/css"/>
<link href="assets/global/css/components.css" id="style_components" rel="stylesheet" type="text/css"/>
<link href="assets/global/css/plugins.css" rel="stylesheet" type="text/css"/>
<link href="assets/admin/layout/css/layout.css" rel="stylesheet" type="text/css"/>
<link href="assets/admin/layout/css/themes/darkblue.css" rel="stylesheet" type="text/css" id="style_color"/>
<link href="assets/admin/layout/css/custom.css" rel="stylesheet" type="text/css"/>
<link rel="shortcut icon" href="favicon.ico"/>
</head>
<body class="page-header-fixed page-quick-sidebar-over-content page-style-square">
<?php include('inc/header.php'); ?>
<div class="clearfix">
</div>
<!-- BEGIN CONTAINER -->
<div class="page-container">
    <div class="page-sidebar-wrapper">
		<div class="page-sidebar navbar-collapse collapse">
			<?php include($sidebar_menu); ?>
		</div>
    </div>
    <div class="page-content-wrapper">
        <div class="page-content">
            <div class="page-bar">
                <ul class="page-breadcrumb">
                    <li><a href="index.php">Home</a><i class="fa fa-circle"></i></li>
                    <li><a href="all-enrollments.php">All Enrollments</a><i class="fa fa-circle"></i></li>
                    <li><span>View Enrollment</span></li>
                </ul>
            </div>
            <h3 class="page-title">Enrollment Details</h3>
            <div class="row">
                <div class="col-md-12">
                    <div class="portlet light bordered">
                        <div class="portlet-title">
                            <div class="caption">
                                <i class="icon-doc font-green"></i>
                                <span class="caption-subject font-green bold uppercase">Enrollment #<?= htmlspecialchars($enrollment->id) ?></span>
                            </div>
                             <div class="actions btn-set">
                                <a href="all-enrollments.php" class="btn default"><i class="fa fa-angle-left"></i> Back</a>
                                <a href="enrollments/<?= $enrollment->id ?>/edit" class="btn green"><i class="fa fa-pencil"></i> Edit</a>
                                <form action="enrollments/<?= $enrollment->id ?>/delete" method="POST" style="display:inline-block;">
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                    <button type="submit" class="btn red" onclick="return confirm('Are you sure you want to delete this enrollment? This action cannot be undone.');">
                                        <i class="fa fa-trash"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                        <div class="portlet-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h4><strong>Title:</strong></h4>
                                    <p><?= htmlspecialchars($enrollment->title) ?></p>

                                    <h4><strong>Submitted By:</strong></h4>
                                    <p><?= $enrollment->user ? htmlspecialchars($enrollment->user->first_name . ' ' . $enrollment->user->last_name) : 'N/A' ?></p>

                                    <h4><strong>Submission Date:</strong></h4>
                                    <p><?= date('dS F, Y h:i A', strtotime($enrollment->dt)) ?></p>

                                    <h4><strong>Payment Status:</strong></h4>
                                    <p>
                                        <?php
                                        $status = htmlspecialchars($enrollment->status);
                                        $label_class = 'label-default';
                                        if ($status === 'completed') {
                                            $label_class = 'label-success';
                                        } elseif ($status === 'pending') {
                                            $label_class = 'label-warning';
                                        } elseif ($status === 'failed') {
                                            $label_class = 'label-danger';
                                        }
                                        ?>
                                        <span class="label <?= $label_class ?>"><?= ucfirst($status) ?></span>
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <h4><strong>Season:</strong></h4>
                                    <p><?= $enrollment->season_id ? App\Models\Season::find($enrollment->season_id)->short_name ?? 'Season #' . $enrollment->season_id : 'Not Set' ?></p>

                                    <h4><strong>Category:</strong></h4>
                                    <p><?= $enrollment->category_id ? App\Models\Category::find($enrollment->category_id)->title ?? 'Category #' . $enrollment->category_id : 'Not Set' ?></p>

                                    <h4><strong>Explanation:</strong></h4>
                                    <p style="white-space: pre-wrap;"><?= htmlspecialchars($enrollment->explanation) ?></p>
                                </div>
                            </div>
                            <hr>
                            <h4><strong>Uploaded Files:</strong></h4>
                            <?php
                            function display_files_admin($path, $level = 0) {
                                if (!is_dir($path)) return;
                                $items = scandir($path);
                                foreach($items as $item) {
                                    if ($item[0] === '.') continue;
                                    $fullPath = $path . DIRECTORY_SEPARATOR . $item;
                                    $displayName = str_repeat('&nbsp;', $level * 4) . htmlspecialchars($item, ENT_QUOTES);
                                    if (is_file($fullPath)) {
                                        // Create proper web path from the uploads directory
                                        $relativePath = str_replace('\\', '/', substr($fullPath, strlen(realpath(__DIR__ . '/../uploads'))));
                                        
                                        // Check if S3 is enabled and construct S3 URL if needed
                                        $useS3 = !empty($_ENV['S3_BASE_URL']);
                                        if ($useS3) {
                                            $s3BaseUrl = rtrim($_ENV['S3_BASE_URL'], '/');
                                            $webPath = $s3BaseUrl . '/enrollments/' . ltrim($relativePath, '/');
                                        } else {
                                            $webPath = '../uploads' . $relativePath;
                                        }
                                        
                                        echo '<div><a href="' . htmlspecialchars($webPath) . '" target="_blank" class="btn btn-xs blue"><i class="fa fa-download"></i> ' . $displayName . '</a></div>';
                                    } else if (is_dir($fullPath)) {
                                        echo "<div><strong>" . $displayName . "</strong></div>";
                                        display_files_admin($fullPath, $level + 1);
                                    }
                                }
                            }

                            $possiblePaths = [
                                // New format: uploads/uid/enrollment_id_title/
                                realpath(__DIR__ . '/../uploads') . DIRECTORY_SEPARATOR . $enrollment->uid . DIRECTORY_SEPARATOR . $enrollment->id . '_' . $enrollment->title,
                                // Original format: uploads/uid/title/
                                realpath(__DIR__ . '/../uploads') . DIRECTORY_SEPARATOR . $enrollment->uid . DIRECTORY_SEPARATOR . $enrollment->title,
                                // Category format: uploads/uid/category/
                                realpath(__DIR__ . '/../uploads') . DIRECTORY_SEPARATOR . $enrollment->uid . DIRECTORY_SEPARATOR . $enrollment->title,
                            ];

                            $foundFiles = false;

                            foreach ($possiblePaths as $path) {
                                if (is_dir($path)) {
                                    display_files_admin($path);
                                    $foundFiles = true;
                                    break;
                                }
                            }

                            if (!$foundFiles) {
                                echo '<p>No files found for this enrollment.</p>';
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- END CONTAINER -->
<?php include('inc/footer.php'); ?>
<!-- BEGIN JAVASCRIPTS(Load javascripts at bottom, this will reduce page load time) -->
<!-- BEGIN CORE PLUGINS -->
<!--[if lt IE 9]>
<script src="assets/global/plugins/respond.min.js"></script>
<script src="assets/global/plugins/excanvas.min.js"></script>
<![endif]-->
<script src="assets/global/plugins/jquery.min.js" type="text/javascript"></script>
<script src="assets/global/plugins/jquery-migrate.min.js" type="text/javascript"></script>
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
</html>