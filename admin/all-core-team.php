<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!class_exists('App\\Models\\CoreTeam')) {
    require_once __DIR__ . '/../vendor/autoload.php';
    require_once __DIR__ . '/../config/database.php';
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

use App\Models\CoreTeam;

$team = CoreTeam::orderBy('display_order', 'asc')->get();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

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
<title>Manage Core Team | My First Movie</title>
<base href="<?php echo $admin_path; ?>">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<link href="http://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700&subset=all" rel="stylesheet" type="text/css"/>
<link href="assets/global/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css"/>
<link href="assets/global/plugins/simple-line-icons/simple-line-icons.min.css" rel="stylesheet" type="text/css"/>
<link href="assets/global/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
<link href="assets/global/plugins/uniform/css/uniform.default.css" rel="stylesheet" type="text/css"/>
<link href="assets/global/css/components.css" id="style_components" rel="stylesheet" type="text/css"/>
<link href="assets/global/css/plugins.css" rel="stylesheet" type="text/css"/>
<link href="assets/admin/layout/css/layout.css" rel="stylesheet" type="text/css"/>
<link id="style_color" href="assets/admin/layout/css/themes/darkblue.css" rel="stylesheet" type="text/css"/>
<link href="assets/admin/layout/css/custom.css" rel="stylesheet" type="text/css"/>
<link rel="shortcut icon" href="favicon.ico"/>
</head>
<body class="page-header-fixed page-quick-sidebar-over-content page-style-square"> 
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
            <div class="page-bar">
                <ul class="page-breadcrumb">
                    <li>
                        <i class="fa fa-home"></i>
                        <a href="dashboard.php">Home</a>
                        <i class="fa fa-angle-right"></i>
                    </li>
                    <li>
                        <a href="#">Core Team</a>
                        <i class="fa fa-angle-right"></i>
                    </li>
                    <li>
                        <a href="all-core-team.php">All Core Team</a>
                    </li>
                </ul>
            </div>
            <h3 class="page-title">
                All Core Team Members
            </h3>
            <div class="row">
                <div class="col-md-12">
                    <div class="portlet box blue">
                        <div class="portlet-title">
                            <div class="caption">
                                <i class="fa fa-users"></i>Core Team
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
                                            <a href="team/create" class="btn green">
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
                                        <th>Name</th>
                                        <th>Image</th>
                                        <th>Order</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($team as $i => $member): ?>
                                    <tr>
                                        <td><?php echo $i + 1; ?></td>
                                        <td><?php echo htmlspecialchars($member->name); ?></td>
                                        <td>
                                            <?php if ($member->image): ?>
                                                <?php
                                                // Check if it's an S3 URL or local path
                                                $imagePath = $member->image;
                                                if (strpos($imagePath, 'http') === 0) {
                                                    // S3 URL or full URL
                                                    $imageUrl = $imagePath;
                                                } else {
                                                    // Local path - construct proper URL
                                                    $imageUrl = $correct_base_path . "/uploads/core_team/" . $imagePath;
                                                }
                                                ?>
                                                <img src="<?php echo htmlspecialchars($imageUrl); ?>" width="50" onerror="this.src='<?php echo $admin_path; ?>assets/admin/layout/img/no-image.png';" />
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars((string)$member->display_order); ?></td>
                                        <td>
                                            <a href="team/<?php echo $member->id; ?>/edit" class="btn btn-xs btn-primary">Edit</a>
                                            <form method="POST" action="team/<?php echo $member->id; ?>/delete" style="display:inline;">
                                                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                                <button type="submit" onclick="return confirm('Are you sure?')" class="btn btn-xs btn-danger">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
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
<script src="assets/global/plugins/jquery-slimscroll/jquery.slimscroll.min.js" type="text/javascript"></script>
<script src="assets/global/scripts/metronic.js" type="text/javascript"></script>
<script src="assets/admin/layout/scripts/layout.js" type="text/javascript"></script>
<script>
    jQuery(document).ready(function() {
       Metronic.init(); // init metronic core components
       Layout.init(); // init current layout
    });
</script>
</body>
</html>