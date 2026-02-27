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

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$is_edit = false;
$errors = [];
$testimonial = new App\Models\Testimonial();

if (isset($_GET['id'])) {
    $testimonial = App\Models\Testimonial::find($_GET['id']);
    if ($testimonial) {
        // IDOR Check: Ensure only authorized users can edit.
        if (!in_array($_SESSION['user_type'], ['webmaster', 'admin'])) {
            http_response_code(403);
            exit('Unauthorized: You do not have permission to edit this testimonial.');
        }
        $is_edit = true;
    } else {
        // Redirect if testimonial not found
        header('Location: all-testimonials.php');
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // CSRF Protection
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        http_response_code(403);
        exit('Invalid CSRF token');
    }

    $testimonial->client_name = $_POST['client_name'] ?? '';
    $testimonial->company = $_POST['company'] ?? '';
    $testimonial->testimonial = $_POST['testimonial'] ?? '';
    $testimonial->short_order = (int)($_POST['short_order'] ?? 0);
    $testimonial->status = (int)($_POST['status'] ?? 0);

    // Basic validation
    if (empty($testimonial->client_name)) {
        $errors[] = 'Client name is required.';
    }
    if (empty($testimonial->testimonial)) {
        $errors[] = 'Testimonial content is required.';
    }

    // Handle secure file upload
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        
        // Check file size (2MB limit)
        if ($_FILES['logo']['size'] > 2 * 1024 * 1024) {
            $errors[] = 'Logo must be under 2MB.';
        } else {
            // Verify MIME type
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $_FILES['logo']['tmp_name']);
            finfo_close($finfo);

            $allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
            if (!in_array($mime, $allowed_types, true)) {
                $errors[] = 'Invalid image type. Only JPG, PNG, and WEBP are allowed.';
            } else {
                // Generate a secure, random filename
                $ext = match ($mime) {
                    'image/jpeg' => '.jpg',
                    'image/png'  => '.png',
                    'image/webp' => '.webp',
                };
                $new_filename = 'testimonial_' . bin2hex(random_bytes(8)) . $ext;
                
                // Check if S3 is enabled via environment variable
                $useS3 = !empty($_ENV['S3_BASE_URL']);
                
                if ($useS3) {
                    // S3 upload logic - store full S3 URL
                    $s3BaseUrl = rtrim($_ENV['S3_BASE_URL'], '/');
                    $s3Url = $s3BaseUrl . '/testimonials/' . $new_filename;
                    $testimonial->logo = $s3Url;
                    $testimonial->logo_thumb = $s3Url;
                    
                    // For now, still store locally as backup until S3 upload is implemented
                    $upload_dir = '../uploads/testimonials/';
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0755, true);
                    }
                    $target_file = $upload_dir . $new_filename;
                    move_uploaded_file($_FILES['logo']['tmp_name'], $target_file);
                    
                } else {
                    // Local upload logic
                    $upload_dir = '../uploads/testimonials/';
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0755, true);
                    }
                    $target_file = $upload_dir . $new_filename;

                    if (move_uploaded_file($_FILES['logo']['tmp_name'], $target_file)) {
                        // Delete old logo if it exists
                        if ($is_edit && !empty($testimonial->logo) && file_exists('../' . $testimonial->logo)) {
                            unlink('../' . $testimonial->logo);
                        }
                        if ($is_edit && !empty($testimonial->logo_thumb) && file_exists('../' . $testimonial->logo_thumb)) {
                             unlink('../' . $testimonial->logo_thumb);
                        }

                        // Store relative path for database
                        $testimonial->logo = 'uploads/testimonials/' . $new_filename;
                        $testimonial->logo_thumb = 'uploads/testimonials/' . $new_filename;
                    } else {
                        $errors[] = 'Failed to upload logo.';
                    }
                }
            }
        }
    }

    if (empty($errors)) {
        $testimonial->save();
        header('Location: all-testimonials.php');
        exit();
    }
}

// $menu = 'testimonials'; // Set the active menu item for the sidebar

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
<title><?= $is_edit ? 'Edit' : 'Add' ?> Testimonial | My First Movie</title>
<base href="<?php echo $admin_path; ?>">
<!-- DEBUG: admin_path = <?php echo htmlspecialchars($admin_path); ?> -->
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
                    <li><a href="all-testimonials.php">Testimonials</a><i class="fa fa-circle"></i></li>
                    <li><span><?= $is_edit ? 'Edit' : 'Add' ?> Testimonial</span></li>
                </ul>
            </div>
            <h3 class="page-title"><?= $is_edit ? 'Edit' : 'Add New' ?> Testimonial</h3>
            <div class="row">
                <div class="col-md-12">
                    <div class="portlet light bordered">
                        <div class="portlet-title">
                            <div class="caption">
                                <i class="icon-speech font-green"></i>
                                <span class="caption-subject font-green bold uppercase">Testimonial Details</span>
                            </div>
                        </div>
                        <div class="portlet-body form">
                            <form action="testimonials.php<?= $is_edit ? '?id=' . $testimonial->id : '' ?>" method="POST" enctype="multipart/form-data" class="form-horizontal">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                <div class="form-body">
                                    <?php if (!empty($errors)): ?>
                                        <div class="alert alert-danger">
                                            <strong>Errors:</strong>
                                            <ul>
                                                <?php foreach ($errors as $error): ?>
                                                    <li><?= htmlspecialchars($error) ?></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    <?php endif; ?>

                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Client Name</label>
                                        <div class="col-md-4">
                                            <input type="text" name="client_name" class="form-control" value="<?= htmlspecialchars($testimonial->client_name ?? '') ?>" required>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Company</label>
                                        <div class="col-md-4">
                                            <input type="text" name="company" class="form-control" value="<?= htmlspecialchars($testimonial->company ?? '') ?>">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Testimonial</label>
                                        <div class="col-md-9">
                                            <textarea name="testimonial" class="form-control" rows="5" required><?= htmlspecialchars($testimonial->testimonial ?? '') ?></textarea>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Logo</label>
                                        <div class="col-md-4">
                                            <input type="file" name="logo" class="form-control">
                                            <?php if ($is_edit && $testimonial->logo_thumb): ?>
                                                <p class="help-block">Current logo: 
                                                    <?php 
                                                    // Check if it's an S3 URL or local path
                                                    $imagePath = $testimonial->logo_thumb;
                                                    if (strpos($imagePath, 'http') === 0) {
                                                        // S3 URL or full URL
                                                        $imageUrl = $imagePath;
                                                    } else {
                                                        // Local path - construct proper URL
                                                        $imageUrl = '../' . ltrim($imagePath, '/');
                                                    }
                                                    ?>
                                                    <img src="<?php echo htmlspecialchars($imageUrl); ?>" alt="Logo" style="max-width: 100px; max-height: 100px;"
                                                         onerror="this.src='<?php echo $admin_path; ?>assets/admin/layout/img/no-image.png';" />
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Sort Order</label>
                                        <div class="col-md-2">
                                            <input type="number" name="short_order" class="form-control" value="<?= htmlspecialchars($testimonial->short_order ?? 0) ?>">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Status</label>
                                        <div class="col-md-4">
                                            <select name="status" class="form-control">
                                                <option value="1" <?= ($testimonial->status ?? 1) == 1 ? 'selected' : '' ?>>Active</option>
                                                <option value="0" <?= ($testimonial->status ?? 1) == 0 ? 'selected' : '' ?>>Inactive</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-actions">
                                    <div class="row">
                                        <div class="col-md-offset-3 col-md-9">
                                            <button type="submit" class="btn green">Submit</button>
                                            <a href="all-testimonials.php" class="btn default">Cancel</a>
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