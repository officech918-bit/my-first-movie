<?php

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

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

$newsItem = new News();
$error = [];
$is_edit = false;

if (isset($_GET['id'])) {
    $newsItem = News::find((int)$_GET['id']);
    if (!$newsItem) {
        exit('News article not found.');
    }
    $is_edit = true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($csrf_token, $_POST['csrf_token'])) {
        die('Invalid CSRF token');
    }

    $headline = trim($_POST['headline'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $status = isset($_POST['status']) ? 1 : 0;

    if (empty($headline)) {
        $error['headline'] = 'Headline is required.';
    }
    if (empty($content)) {
        $error['content'] = 'Content is required.';
    }

    // File validation
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['image']['tmp_name'];
        $fileSize = $_FILES['image']['size'];
        $fileName = basename($_FILES['image']['name']);
        $uploadFileDir = realpath(__DIR__ . '/../uploads/news');

        if ($fileSize > 2 * 1024 * 1024) { // 2MB limit
            $error['image'] = 'File size must be less than 2MB.';
        } else {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $fileTmpPath);
            finfo_close($finfo);
            $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

            if (!in_array($mime, $allowedMimeTypes)) {
                $error['image'] = 'Invalid file type. Only JPG, PNG, GIF, and WEBP are allowed.';
            } elseif (getimagesize($fileTmpPath) === false) {
                $error['image'] = 'The uploaded file is not a valid image.';
            }
        }
    } elseif (!$is_edit) { // Image is required for new articles
        $error['image'] = 'Image is required.';
    }

    if (empty($error)) {
        $newsItem->headline = $headline;
        $newsItem->content = $content;
        $newsItem->status = $status;
        $newsItem->is_admin_news = 1; // As per original logic

        if (isset($fileTmpPath)) {
            $newFileName = uniqid() . '-' . $fileName;
            $dest_path = $uploadFileDir . '/' . $newFileName;
            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                chmod($dest_path, 0644);
                // Optionally delete old image
                if ($is_edit && $newsItem->image && file_exists($uploadFileDir . '/' . $newsItem->image)) {
                    unlink($uploadFileDir . '/' . $newsItem->image);
                }
                $newsItem->image = $newFileName;
            } else {
                $error['image'] = 'Failed to move uploaded file.';
            }
        }

        if (empty($error)) {
            $newsItem->save();
            $_SESSION['success_message'] = 'News article saved successfully!';
            header('Location: ' . $admin_path . 'all-news');
            exit();
        }
    }
}

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
<title><?= $is_edit ? 'Edit' : 'Add' ?> News Article | MyFirstMovie</title>
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
                        <a href="all-news">News</a>
                        <i class="fa fa-angle-right"></i>
                    </li>
                    <li>
                        <a href="#"><?= $is_edit ? 'Edit' : 'Add' ?> Article</a>
                    </li>
                </ul>
            </div>
            <h3 class="page-title">
                <?= $is_edit ? 'Edit' : 'Add' ?> News Article
            </h3>
            <div class="row">
                <div class="col-md-12">
                    <div class="portlet box blue">
                        <div class="portlet-title">
                            <div class="caption">
                                <i class="fa fa-newspaper-o"></i>Article Details
                            </div>
                        </div>
                        <div class="portlet-body form">
                            <form method="post" action="<?= $is_edit ? 'news/' . $newsItem->id . '/edit' : 'news/create' ?>" enctype="multipart/form-data" class="form-horizontal">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                                <div class="form-body">
                                    <div class="form-group">
                                        <label class="col-md-3 control-label" for="headline">Headline</label>
                                        <div class="col-md-9">
                                            <input type="text" name="headline" id="headline" class="form-control" value="<?= htmlspecialchars($newsItem->headline ?? '') ?>" required>
                                            <?php if (isset($error['headline'])): ?><span class="help-block text-danger"><?= $error['headline'] ?></span><?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label" for="content">Content</label>
                                        <div class="col-md-9">
                                            <textarea name="content" id="content" class="form-control" rows="10" required><?= htmlspecialchars($newsItem->content ?? '') ?></textarea>
                                            <?php if (isset($error['content'])): ?><span class="help-block text-danger"><?= $error['content'] ?></span><?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label" for="image">Image</label>
                                        <div class="col-md-9">
                                            <input type="file" name="image" id="image" class="form-control">
                                            <?php if ($is_edit && $newsItem->image): ?>
                                                <div class="mt-2">
                                                    <?php
                                                    // Check if it's an S3 URL or local path
                                                    $imagePath = $newsItem->image;
                                                    if (strpos($imagePath, 'http') === 0) {
                                                        // S3 URL or full URL
                                                        $imageUrl = $imagePath;
                                                    } else {
                                                        // Local path - construct proper URL
                                                        $imageUrl = $correct_base_path . "/uploads/news/" . $imagePath;
                                                    }
                                                    ?>
                                                    <img src="<?php echo htmlspecialchars($imageUrl); ?>" width="100" class="img-thumbnail" onerror="this.src='<?php echo $admin_path; ?>assets/admin/layout/img/no-image.png';" />
                                                    <p class="help-block">Current Image</p>
                                                </div>
                                            <?php endif; ?>
                                            <?php if (isset($error['image'])): ?><span class="help-block text-danger"><?= $error['image'] ?></span><?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Status</label>
                                        <div class="col-md-9">
                                            <div class="checkbox-list">
                                                <label>
                                                    <input type="checkbox" name="status" value="1" <?= $newsItem->status ? 'checked' : '' ?>> Active
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-actions">
                                    <div class="row">
                                        <div class="col-md-offset-3 col-md-9">
                                            <button type="submit" class="btn green"><?= $is_edit ? 'Update' : 'Submit' ?></button>
                                            <a href="all-news" class="btn default">Cancel</a>
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