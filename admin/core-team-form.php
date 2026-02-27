<?php
declare(strict_types=1);

// bootstrap.php
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

$is_edit = false;
$member = new CoreTeam();
$form_action = 'team/create';

if (isset($_GET['id'])) {
    $is_edit = true;
    $member = CoreTeam::findOrFail($_GET['id']);
    $form_action = 'team/' . $member->id . '/edit';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die('Invalid CSRF token');
    }

    $member->name = $_POST['name'];
    $member->intro = $_POST['intro'];
    $member->display_order = (int)$_POST['display_order'];

    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $image_name = basename($_FILES["image"]["name"]);
        $target_dir = "../uploads/core_team/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $target_file = $target_dir . $image_name;
        move_uploaded_file($_FILES["image"]["tmp_name"], $target_file);
        $member->image = $image_name;
    }

    $member->save();

    header('Location: ' . $admin_path . 'team');
    exit;
}

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
<title><?php echo $is_edit ? 'Edit' : 'Add'; ?> Team Member | My First Movie</title>
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
                        <a href="team">Core Team</a>
                        <i class="fa fa-angle-right"></i>
                    </li>
                    <li>
                        <a href="#"><?= $is_edit ? 'Edit' : 'Add' ?> Member</a>
                    </li>
                </ul>
            </div>
            <h3 class="page-title">
                <?= $is_edit ? 'Edit' : 'Add' ?> Team Member
            </h3>
            <div class="row">
                <div class="col-md-12">
                    <div class="portlet box blue">
                        <div class="portlet-title">
                            <div class="caption">
                                <i class="fa fa-user"></i>Member Details
                            </div>
                        </div>
                        <div class="portlet-body form">
                            <form method="post" action="<?= htmlspecialchars($form_action) ?>" enctype="multipart/form-data" class="form-horizontal">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                                <div class="form-body">
                                    <div class="form-group">
                                        <label class="col-md-3 control-label" for="name">Name</label>
                                        <div class="col-md-9">
                                            <input type="text" name="name" id="name" class="form-control" value="<?= htmlspecialchars($member->name ?? '') ?>" required>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label" for="intro">Intro</label>
                                        <div class="col-md-9">
                                            <textarea name="intro" id="intro" class="form-control" rows="5" required><?= htmlspecialchars($member->intro ?? '') ?></textarea>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label" for="image">Image</label>
                                        <div class="col-md-9">
                                            <input type="file" name="image" id="image" class="form-control">
                                            <?php if($is_edit && $member->image): ?>
                                            <div class="mt-2">
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
                                                <img src="<?php echo htmlspecialchars($imageUrl); ?>" width="100" class="img-thumbnail" onerror="this.src='<?php echo $admin_path; ?>assets/admin/layout/img/no-image.png';" />
                                                <p class="help-block">Current Image</p>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label" for="display_order">Display Order</label>
                                        <div class="col-md-9">
                                            <input type="number" name="display_order" id="display_order" class="form-control" value="<?= htmlspecialchars((string)($member->display_order ?? '0')) ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-actions">
                                    <div class="row">
                                        <div class="col-md-offset-3 col-md-9">
                                            <button type="submit" class="btn green"><?= $is_edit ? 'Update' : 'Add' ?> Member</button>
                                            <a href="team" class="btn default">Cancel</a>
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