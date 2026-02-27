<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_type']) || !in_array($_SESSION['user_type'], ['webmaster', 'admin'])) {
    http_response_code(403);
    exit('Forbidden');
}
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/database.php';
if (!function_exists('create_thumbnail')) {
    include('inc/requires.php');
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

use App\Models\Season;
use App\Models\Category;
use App\Models\WebUser;
use App\Models\Winner;
use App\Models\Enrollment;

// Get admin config for dynamic paths
// include('config.php');
define('BASE_URL', $admin_path);
$menu = 'inc/left-menu-admin.php';
if (isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'webmaster') {
    $menu = 'inc/left-menu-webmaster.php';
}
$is_edit = false;
if (isset($vars['id'])) {
    $is_edit = true;
    $winner_id = (int)$vars['id'];
    $winner = Winner::find($winner_id);
    if (!$winner) {
        http_response_code(404);
        echo 'Winner not found.';
        exit;
    }
} else {
    $winner = new Winner();
}
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];
$error = [];
$success_msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("CSRF token validation failed.");
    }
    $is_error = false;
    if (empty($_POST['season_id']) || !is_numeric($_POST['season_id'])) {
        $is_error = true;
        $error['season'] = "Season is required";
    }
    if (empty($_POST['category_id']) || !is_numeric($_POST['category_id'])) {
        $is_error = true;
        $error['category'] = "Category is required";
    }
    if (empty($_POST['user_id']) || !is_numeric($_POST['user_id'])) {
        $is_error = true;
        $error['user'] = "User is required";
    }
    if (!empty($_POST['announcement_date'])) {
        $d = date_create_from_format('Y-m-d', $_POST['announcement_date']);
        if (!$d) {
            $is_error = true;
            $error['announcement_date'] = "Invalid date format (YYYY-MM-DD)";
        }
    }
    if (!$is_error) {
        $winner->season_id = (int)$_POST['season_id'];
        $winner->category_id = (int)$_POST['category_id'];
        $winner->user_id = (int)$_POST['user_id'];
        $winner->announcement_date = !empty($_POST['announcement_date']) ? $_POST['announcement_date'] : null;
        $winner->description = $_POST['description'] ?? null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
            if ($_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $fileTmpPath = $_FILES['image']['tmp_name'];
                $fileName = $_FILES['image']['name'];
                $fileSize = $_FILES['image']['size'];
                if ($fileSize > 5 * 1024 * 1024) {
                    $error['file'] = 'File size must be less than 5MB.';
                } else {
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mime = finfo_file($finfo, $fileTmpPath);
                    finfo_close($finfo);
                    $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                    if (!in_array($mime, $allowedMimeTypes)) {
                        $error['file'] = 'Invalid file type.';
                    } elseif (getimagesize($fileTmpPath) === false) {
                        $error['file'] = 'Invalid image.';
                    } else {
                        $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
                        $newFileName = 'winner_' . bin2hex(random_bytes(8)) . '.' . $fileExtension;
                        
                        // Check if S3 is enabled via environment variable
                        $useS3 = !empty($_ENV['S3_BASE_URL']);
                        
                        if ($useS3) {
                            // S3 upload logic - store full S3 URL
                            $s3BaseUrl = rtrim($_ENV['S3_BASE_URL'], '/');
                            $s3Url = $s3BaseUrl . '/winners/' . $newFileName;
                            $winner->image = $s3Url;
                            $winner->image_thumb = $s3Url;
                            $winner->winner_photo = $s3Url;
                            
                            // For now, still store locally as backup until S3 upload is implemented
                            $uploadDir = 'uploads/winners/';
                            if (!is_dir($uploadDir)) {
                                mkdir($uploadDir, 0755, true);
                            }
                            $dest = $uploadDir . $newFileName;
                            move_uploaded_file($fileTmpPath, $dest);
                            
                        } else {
                            // Local upload logic
                            $uploadDir = 'uploads/winners/';
                            if (!is_dir($uploadDir)) {
                                mkdir($uploadDir, 0755, true);
                            }
                            $dest = $uploadDir . $newFileName;
                            
                            if (move_uploaded_file($fileTmpPath, $dest)) {
                                if ($is_edit && $winner->image && file_exists($winner->image)) {
                                    unlink($winner->image); 
                                }
                                // Store relative path for database
                                $winner->image = $dest;
                                $winner->image_thumb = $dest;
                                $winner->winner_photo = $dest;
                            } else {
                                $error['file'] = 'Error moving uploaded file.';
                            }
                        }
                    }
                }
            } else {
                $error['file'] = 'Upload error.';
            }
        }
        if (!$is_edit) {
            $winner->created_at = date('Y-m-d H:i:s');
        }
        if (!isset($error['file'])) {
            $winner->rank_position = isset($_POST['rank_position']) ? (int)$_POST['rank_position'] : ($winner->rank_position ?? 1);
            $winner->title = $_POST['title'] ?? $winner->title;
            $winner->save();
            if ($is_edit) {
                $success_msg = "Winner updated successfully!";
            } else {
                header('Location: ' . BASE_URL . 'winners/' . $winner->id . '/edit?created=true');
                exit;
            }
        }
    }
}
if (isset($_GET['created'])) {
    $success_msg = "Winner created successfully!";
}
$seasons = Season::orderBy('short_order')->get();
$categories = Category::where('status', 1)->orderBy('short_order')->get();
$users = WebUser::where('status', 1)->orderBy('first_name')->get();
try {
    $selectedSeasonId = $is_edit ? ($winner->season_id ?? null) : null;
    $selectedCategoryId = $is_edit ? ($winner->category_id ?? null) : null;
    if (!$selectedSeasonId && isset($_POST['season_id'])) { $selectedSeasonId = (int)$_POST['season_id']; }
    if (!$selectedCategoryId && isset($_POST['category_id'])) { $selectedCategoryId = (int)$_POST['category_id']; }
    if ($selectedSeasonId && $selectedCategoryId) {
        $enrolled = Enrollment::where('season_id', $selectedSeasonId)->where('category_id', $selectedCategoryId)->get();
        $enrolledUids = $enrolled->pluck('uid')->unique()->all();
        if (!empty($enrolledUids)) {
            $users = WebUser::whereIn('uid', $enrolledUids)->orderBy('first_name')->get();
        }
    }
} catch (\Throwable $e) {
    // Fallback: keep all active users if enrollments don't have season/category fields yet
}
?>
<!DOCTYPE html>
<html lang="en" class="no-js">
<head>
<meta charset="utf-8"/>
<title><?php echo $is_edit ? 'Edit' : 'Add'; ?> Winner | My First Movie</title>
<base href="<?php echo BASE_URL; ?>">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta content="width=device-width, initial-scale=1" name="viewport"/>
<meta content="" name="description"/>
<meta content="" name="author"/>
<link href="http://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700&subset=all" rel="stylesheet" type="text/css"/>
<link href="assets/global/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css"/>
<link href="assets/global/plugins/simple-line-icons/simple-line-icons.min.css" rel="stylesheet" type="text/css"/>
<link href="assets/global/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
<link href="assets/global/plugins/uniform/css/uniform.default.css" rel="stylesheet" type="text/css"/>
<link href="assets/global/css/components.css" id="style_components" rel="stylesheet" type="text/css"/>
<link href="assets/global/css/plugins.css" rel="stylesheet" type="text/css"/>
<link href="assets/admin/layout/css/layout.css" rel="stylesheet" type="text/css"/>
<link href="assets/admin/layout/css/themes/darkblue.css" rel="stylesheet" type="text/css" id="style_color"/>
<link href="assets/admin/layout/css/custom.css" rel="stylesheet" type="text/css"/>
<link rel="shortcut icon" href="favicon.ico"/>
</head>
<body class="page-header-fixed page-quick-sidebar-over-content page-style-square">
<?php include('inc/header.php'); ?>
<div class="page-container">
	<div class="page-sidebar-wrapper">
		<div class="page-sidebar navbar-collapse collapse">
			<?php include($menu); ?>
		</div>
	</div>
	<div class="page-content-wrapper">
		<div class="page-content">
			<h3 class="page-title"><?php echo $is_edit ? 'Edit Winner' : 'Add New Winner'; ?></h3>
            <div class="page-bar">
                <ul class="page-breadcrumb">
                    <li><a href="dashboard.php">Home</a></li>
                    <li><a href="all-winners.php">All Winners</a></li>
                    <li><a href="#"><?php echo $is_edit ? 'Edit' : 'Add'; ?></a></li>
                </ul>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <form class="form-horizontal form-row-seperated" action="" method="POST" enctype="multipart/form-data">
                        <div class="portlet">
                            <div class="portlet-title">
                                <div class="caption"><?php echo htmlspecialchars($winner->title ?? 'New Winner'); ?></div>
                                <div class="actions btn-set">
                                    <a href="all-winners.php" class="btn default">Back</a>
                                    <button type="submit" name="submit" class="btn green">Save</button>
                                </div>
                            </div>
                            <div class="portlet-body">
                                <?php if ($success_msg): ?>
                                <div class="alert alert-success"><?php echo $success_msg; ?></div>
                                <?php endif; ?>
                                <div class="form-body">
                                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>"/>
                                    <div class="form-group">
                                        <label class="col-md-2 control-label">Season: <span class="required">*</span></label>
                                        <div class="col-md-10">
                                            <select class="form-control" name="season_id">
                                                <option value="">Select Season</option>
                                                <?php foreach ($seasons as $season): ?>
                                                    <option value="<?php echo (int)$season->id; ?>" <?php echo ($winner->season_id ?? null) == $season->id ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($season->title); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <?php if (isset($error['season'])): ?><span class="help-block" style="color: red;"><?php echo $error['season']; ?></span><?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-2 control-label">Category: <span class="required">*</span></label>
                                        <div class="col-md-10">
                                            <select class="form-control" name="category_id">
                                                <option value="">Select Category</option>
                                                <?php foreach ($categories as $category): ?>
                                                    <option value="<?php echo (int)$category->id; ?>" <?php echo ($winner->category_id ?? null) == $category->id ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($category->title); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <?php if (isset($error['category'])): ?><span class="help-block" style="color: red;"><?php echo $error['category']; ?></span><?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-2 control-label">User: <span class="required">*</span></label>
                                        <div class="col-md-10">
                                            <select class="form-control" name="user_id">
                                                <option value="">Select User</option>
                                                <?php foreach ($users as $u): ?>
                                                    <option 
                                                        value="<?php echo (int)$u->uid; ?>" 
                                                        data-uid="<?php echo (int)$u->uid; ?>"
                                                        data-email="<?php echo htmlspecialchars($u->email); ?>"
                                                        data-name="<?php echo htmlspecialchars(($u->first_name ?? '') . ' ' . ($u->last_name ?? '')); ?>"
                                                        <?php echo ($winner->user_id ?? null) == $u->uid ? 'selected' : ''; ?>
                                                    >
                                                        <?php echo htmlspecialchars(($u->first_name ?? '') . ' ' . ($u->last_name ?? '')); ?> (<?php echo htmlspecialchars($u->email); ?>) [ID: <?php echo (int)$u->uid; ?>]
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <?php if (isset($error['user'])): ?><span class="help-block" style="color: red;"><?php echo $error['user']; ?></span><?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-2 control-label">Rank Position:</label>
                                        <div class="col-md-10">
                                            <select class="form-control" name="rank_position">
                                                <?php $rp = (int)($winner->rank_position ?? 1); ?>
                                                <option value="1" <?php echo $rp===1?'selected':''; ?>>1 - Winner (Gold)</option>
                                                <option value="2" <?php echo $rp===2?'selected':''; ?>>2 - Runner-up (Silver)</option>
                                                <option value="3" <?php echo $rp===3?'selected':''; ?>>3 - Third (Bronze)</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-2 control-label">Title:</label>
                                        <div class="col-md-10">
                                            <input type="text" class="form-control" name="title" value="<?php echo htmlspecialchars($winner->title ?? ''); ?>" placeholder="e.g., Best Singer 2026">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-2 control-label">Announcement Date:</label>
                                        <div class="col-md-10">
                                            <input type="date" class="form-control" name="announcement_date" value="<?php echo htmlspecialchars($winner->announcement_date ?? ''); ?>">
                                            <?php if (isset($error['announcement_date'])): ?><span class="help-block" style="color: red;"><?php echo $error['announcement_date']; ?></span><?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-2 control-label">Description:</label>
                                        <div class="col-md-10">
                                            <textarea class="form-control" name="description"><?php echo htmlspecialchars($winner->description ?? ''); ?></textarea>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-2 control-label">Winner Image:</label>
                                        <div class="col-md-10">
                                            <input type="file" name="image" accept="image/*">
                                            <?php if ($is_edit && $winner->image): ?>
                                                <p class="help-block">Current image: 
                                                    <?php 
                                                    // Check if it's an S3 URL or local path
                                                    $imagePath = $winner->image;
                                                    if (strpos($imagePath, 'http') === 0) {
                                                        // S3 URL or full URL
                                                        $imageUrl = $imagePath;
                                                    } else {
                                                        // Local path - construct proper URL
                                                        $imageUrl = BASE_URL . ltrim($imagePath, '/');
                                                    }
                                                    ?>
                                                    <img src="<?php echo htmlspecialchars($imageUrl); ?>" width="80" alt=""
                                                         onerror="this.src='<?php echo BASE_URL; ?>assets/admin/layout/img/no-image.png';" />
                                                </p>
                                            <?php endif; ?>
                                            <?php if (isset($error['file'])): ?><span class="help-block" style="color: red;"><?php echo $error['file']; ?></span><?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
		</div>
	</div>
</div>
<?php include('inc/footer.php'); ?>
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
<script src="assets/global/scripts/metronic.js" type="text/javascript"></script>
<script src="assets/admin/layout/scripts/layout.js" type="text/javascript"></script>
<script>
jQuery(document).ready(function() {
   Metronic.init();
   Layout.init();
   var $search = $('<input type="text" class="form-control" placeholder="Search user by name, email or ID" style="margin-bottom:10px;">');
   var $select = $('select[name="user_id"]');
   var allOptions = [];
   $select.find('option').each(function(){
     allOptions.push({
       value: $(this).attr('value'),
       text: $(this).text(),
       uid: ($(this).data('uid')||'').toString(),
       email: ($(this).data('email')||'').toString(),
       name: ($(this).data('name')||'').toString(),
       selected: $(this).is(':selected')
     });
   });
   $select.before($search);
   function rebuildOptions(query){
     var q = (query||'').toLowerCase();
     var currentVal = $select.val();
     $select.empty();
     var placeholder = $('<option value="">Select User</option>');
     $select.append(placeholder);
     allOptions.forEach(function(opt){
       var hay = (opt.text + ' ' + opt.uid + ' ' + opt.email + ' ' + opt.name).toLowerCase();
       if (q === '' || hay.indexOf(q) !== -1) {
         var $o = $('<option></option>').attr('value', opt.value).text(opt.text)
           .attr('data-uid', opt.uid).attr('data-email', opt.email).attr('data-name', opt.name);
         if (opt.value == currentVal || opt.selected) { $o.attr('selected', 'selected'); }
         $select.append($o);
       }
     });
   }
   rebuildOptions('');
   $search.on('input', function(){ rebuildOptions($(this).val()); });
});
</script>
</body>
</html>