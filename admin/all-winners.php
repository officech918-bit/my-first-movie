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
define('BASE_URL', $admin_path);
$menu = 'inc/left-menu-admin.php';
if (isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'webmaster') {
    $menu = 'inc/left-menu-webmaster.php';
}
$seasons = Season::orderBy('short_order')->get();
$seasons = $seasons->sortBy(function($s){
    return (($s->status === 'ACTIVE') || ($s->status == 1)) ? 0 : 1;
})->values();
$categories = Category::where('status', 1)->orderBy('short_order')->get();
$users = WebUser::where('status', 1)->orderBy('first_name')->get();
$winners = Winner::orderBy('id', 'desc')->get();
?>
<!DOCTYPE html>
<html lang="en" class="no-js">
<head>
<meta charset="utf-8"/>
<title>All Winners | My First Movie</title>
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
			<h3 class="page-title">All Winners</h3>
            <div class="actions btn-set" style="margin-bottom:15px;">
                <a href="winners/create" class="btn green">Add Winner</a>
            </div>
            <?php
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
            <ul class="nav nav-tabs" role="tablist" style="margin-bottom:15px;">
                <li role="presentation" class="active"><a href="#season-all" aria-controls="season-all" role="tab" data-toggle="tab">All</a></li>
                <?php foreach ($seasons as $s): ?>
                    <li role="presentation"><a href="#season-<?php echo (int)$s->id; ?>" aria-controls="season-<?php echo (int)$s->id; ?>" role="tab" data-toggle="tab"><?php echo htmlspecialchars($s->title); ?></a></li>
                <?php endforeach; ?>
            </ul>
            <div class="portlet">
                <div class="portlet-body">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>User</th>
                                <th>Season</th>
                                <th>Category</th>
                                <th>Rank</th>
                                <th>Title</th>
                                <th>Announced</th>
                                <th>Image</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($winners as $winner): ?>
                            <?php 
                                $seasonTitle = optional($seasons->firstWhere('id', $winner->season_id))->title ?? '';
                                $categoryTitle = optional($categories->firstWhere('id', $winner->category_id))->title ?? '';
                                $u = $users->firstWhere('uid', $winner->user_id);
                                $userName = $u ? trim(($u->first_name ?? '') . ' ' . ($u->last_name ?? '')) : '';
                                $announced = $winner->announcement_date ?: $winner->created_at;
                                $rankLabel = $winner->rank_position == 1 ? 'Gold' : ($winner->rank_position == 2 ? 'Silver' : ($winner->rank_position == 3 ? 'Bronze' : ''));
                            ?>
                            <tr data-season-id="<?php echo (int)$winner->season_id; ?>">
                                <td><?php echo (int)$winner->id; ?></td>
                                <td><?php echo htmlspecialchars($userName); ?><div><span class="label label-default">UID: <?php echo (int)($u->uid ?? 0); ?></span></div></td>
                                <td><?php echo htmlspecialchars($seasonTitle); ?></td>
                                <td><?php echo htmlspecialchars($categoryTitle); ?></td>
                                <td>
                                  <?php if ($winner->rank_position): ?>
                                    <span class="label <?php echo $winner->rank_position==1?'label-warning':($winner->rank_position==2?'label-info':'label-default'); ?>">
                                      <?php echo (int)$winner->rank_position; ?> <?php echo $rankLabel; ?>
                                    </span>
                                  <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($winner->title ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($announced ?? ''); ?></td>
                                <td><?php if (!empty($winner->image)): ?>
                                                    <?php 
                                                    // Check if it's an S3 URL or local path
                                                    $imagePath = $winner->image;
                                                    if (strpos($imagePath, 'http') === 0) {
                                                        // S3 URL or full URL
                                                        $imageUrl = $imagePath;
                                                    } else {
                                                        // Local path - construct proper URL
                                                        $imageUrl = $admin_path . ltrim($imagePath, '/');
                                                    }
                                                    ?>
                                                    <img src="<?php echo htmlspecialchars($imageUrl); ?>" width="60" alt=""
                                                         onerror="this.src='<?php echo $admin_path; ?>assets/admin/layout/img/no-image.png';" />
                                                <?php endif; ?></td>
                                <td>
                                    <a href="winners/<?php echo (int)$winner->id; ?>/edit" class="btn btn-xs default">Edit</a>
                                    <form action="winners/<?php echo (int)$winner->id; ?>/delete" method="POST" style="display:inline-block;">
                                        <input type="hidden" name="id" value="<?php echo (int)$winner->id; ?>">
                                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                        <button type="submit" class="btn btn-xs btn-danger" onclick="return confirm('Delete this winner?');">Delete</button>
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
<script src="assets/global/scripts/metronic.js" type="text/javascript"></script>
<script src="assets/admin/layout/scripts/layout.js" type="text/javascript"></script>
<script>
jQuery(function($){
  function applySeasonFilter(seasonId){
    $('table tbody tr').each(function(){
      var match = seasonId === 'all' || String($(this).data('season-id')) === String(seasonId);
      $(this).toggle(match);
    });
  }
  $('.nav-tabs a[role="tab"]').on('shown.bs.tab', function (e) {
    var href = $(e.target).attr('href');
    var seasonId = href === '#season-all' ? 'all' : href.replace('#season-','');
    applySeasonFilter(seasonId);
  });
  applySeasonFilter('all');
  Metronic.init(); Layout.init();
});
</script>
</body>
</html>
