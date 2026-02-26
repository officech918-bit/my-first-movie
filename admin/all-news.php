<?php

// Ensure the Composer autoloader and Eloquent are loaded.
if (!class_exists('App\Models\News')) {
    require_once __DIR__ . '/../vendor/autoload.php';
    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/inc/requires.php';
}

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

use App\Models\News;

// Auth Guard: Block access if user is not an authorized admin or webmaster.
if (!isset($_SESSION['user_type']) || !in_array($_SESSION['user_type'], ['webmaster', 'admin'])) {
    http_response_code(403);
    exit('Forbidden: You do not have permission to access this page.');
}

// Fetch all news articles, ordering by the most recent
$newsItems = News::where('is_admin_news', 1)->orderBy('create_date', 'desc')->get();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

$success_message = $_SESSION['success_message'] ?? null;
unset($_SESSION['success_message']);

$menu = '';
if($_SESSION['user_type'] == 'admin'){
    $menu = 'inc/left-menu-admin.php';
}
elseif($_SESSION['user_type'] == 'webmaster') {
    $menu = 'inc/left-menu-webmaster.php';
}
else {
    $menu = 'inc/left-menu-regular.php';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<title>All News | MyFirstMovie</title>
<base href="<?php echo $admin_path; ?>">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<meta content="MyFirstMovie" name="description"/>
<meta content="MyFirstMovie" name="author"/>
<!-- BEGIN GLOBAL MANDATORY STYLES -->
<link href="<?php echo $admin_path; ?>assets/global/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css"/>
<link href="<?php echo $admin_path; ?>assets/global/plugins/simple-line-icons/simple-line-icons.min.css" rel="stylesheet" type="text/css"/>
<link href="<?php echo $admin_path; ?>assets/global/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
<link href="<?php echo $admin_path; ?>assets/global/plugins/uniform/css/uniform.default.css" rel="stylesheet" type="text/css"/>
<!-- END GLOBAL MANDATORY STYLES -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- END GLOBAL MANDATORY STYLES -->
<!-- BEGIN THEME STYLES -->
<link href="<?php echo $admin_path; ?>assets/global/css/components.css" id="style_components" rel="stylesheet" type="text/css"/>
<link href="<?php echo $admin_path; ?>assets/global/css/plugins.css" rel="stylesheet" type="text/css"/>
<link href="<?php echo $admin_path; ?>assets/admin/layout/css/layout.css" rel="stylesheet" type="text/css"/>
<link id="style_color" href="<?php echo $admin_path; ?>assets/admin/layout/css/themes/darkblue.css" rel="stylesheet" type="text/css"/>
<link href="<?php echo $admin_path; ?>assets/admin/layout/css/custom.css" rel="stylesheet" type="text/css"/>
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
	<!-- BEGIN CONTENT -->
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
                        <a href="#">News</a>
                        <i class="fa fa-angle-right"></i>
                    </li>
                    <li>
                        <a href="all-news.php">All News</a>
                    </li>
                </ul>
            </div>
            <h3 class="page-title">
                All News
            </h3>
			<?php if ($success_message): ?>
				<div class="alert alert-success">
					<?= htmlspecialchars($success_message) ?>
				</div>
			<?php endif; ?>

			<div class="row">
				<div class="col-md-12">
                    <div class="portlet box blue">
                        <div class="portlet-title">
                            <div class="caption">
                                <i class="fa fa-newspaper-o"></i>News Articles
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
                                            <a href="news/create" class="btn green">
                                                Add New <i class="fa fa-plus"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <table class="table table-striped table-bordered table-hover" id="sample_1">
                                <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Headline</th>
                                    <th>Image</th>
                                    <th>Status</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php if (!$newsItems->isEmpty()): ?>
                                    <?php foreach ($newsItems as $item): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($item->id) ?></td>
                                            <td><?= htmlspecialchars($item->headline) ?></td>
                                            <td>
                                            <?php if ($item->image): ?>
                                                <?php
                                                // Check if it's an S3 URL or local path
                                                $imagePath = $item->image;
                                                if (strpos($imagePath, 'http') === 0) {
                                                    // S3 URL or full URL
                                                    $imageUrl = $imagePath;
                                                } else {
                                                    // Local path - construct proper URL
                                                    $imageUrl = $correct_base_path . "/uploads/news/" . $imagePath;
                                                }
                                                ?>
                                                <img src="<?php echo htmlspecialchars($imageUrl); ?>" width="50" onerror="this.src='<?php echo $admin_path; ?>assets/admin/layout/img/no-image.png';" />
                                            <?php endif; ?>
                                        </td>
                                            <td>
                                                <span class="label label-sm <?= $item->status ? 'label-success' : 'label-warning' ?>">
                                                    <?= $item->status ? 'Active' : 'Inactive' ?>
                                                </span>
                                            </td>
                                            <td><?= htmlspecialchars($item->create_date->format('Y-m-d H:i:s')) ?></td>
                                            <td>
                                                <a href="news/<?= $item->id ?>/edit" class="btn btn-xs blue"><i class="fa fa-edit"></i> Edit</a>
                                                <form method="POST" action="<?= $admin_path ?>news/<?= $item->id ?>/delete" style="display:inline;" class="delete-form">
                                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                                                    <button type="submit" class="btn btn-xs red"><i class="fa fa-trash-o"></i> Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
				</div>
			</div>
		</div>
	</div>
	<!-- END CONTENT -->
</div>
<!-- END CONTAINER -->
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
<!-- Zebra Dialog for delete confirmation -->
<script src="assets/admin/layout/scripts/zebra_dialog.js" type="text/javascript"></script>
<script src="assets/admin/layout/scripts/zebra_dialog.js" type="text/javascript"></script>
<script>
    jQuery(document).ready(function() {
        Metronic.init(); // init metronic core components
        Layout.init(); // init current layout
        $('#sample_1').DataTable({
            language: { emptyTable: 'No news articles found.' }
        });

        $('.delete-form').on('submit', function(e) {
            e.preventDefault(); // Stop the form from submitting immediately
            const form = this;
            console.log('Delete form submitted'); // Debug log
            console.log('Form action:', form.action); // Debug log
            
            // Use Zebra Dialog instead of SweetAlert2 to avoid CSP issues
            new $.Zebra_Dialog('Are you sure?', {
                type: 'confirmation',
                buttons: ['Yes', 'Cancel'],
                onClose: function(caption) {
                    console.log('Zebra Dialog result:', caption); // Debug log
                    if (caption === 'Yes') {
                        console.log('Submitting form to:', form.action); // Debug log
                        form.submit(); // If confirmed, submit the form
                    } else {
                        console.log('Delete cancelled'); // Debug log
                    }
                }
            });
        });
    });
</script>
<!-- END JAVASCRIPTS -->
</body>
</html>