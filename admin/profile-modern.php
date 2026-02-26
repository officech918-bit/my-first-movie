<?php
use App\Models\WebUser;
require_once __DIR__ . '/inc/requires.php';
date_default_timezone_set('Asia/Kolkata');
header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: no-referrer-when-downgrade');
header("Content-Security-Policy: default-src 'self' data: https://fonts.googleapis.com https://fonts.gstatic.com http://fonts.googleapis.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; script-src 'self' 'unsafe-inline'; img-src 'self' data:");

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

if (!isset($_SESSION['uid'])) { header("Location: index.php"); exit(); }
if (empty($_SESSION['csrf_token'])) { $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); }
$csrf_token = $_SESSION['csrf_token'];
if (!isset($database) || !$database instanceof MySQLDB) { $database = new MySQLDB(); }
$user_id = $_SESSION['uid'];
$current_user = WebUser::find($user_id);
if (!$current_user) {
    $stmt = $database->db->prepare("SELECT * FROM web_users WHERE uid = ?");
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res ? $res->fetch_assoc() : null;
        $stmt->close();
        if ($row) { $current_user = (object)$row; }
    }
}
if (!$current_user && isset($_SESSION['email'])) {
    $email = $_SESSION['email'];
    $stmt = $database->db->prepare("SELECT * FROM web_users WHERE email = ?");
    if ($stmt) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res ? $res->fetch_assoc() : null;
        $stmt->close();
        if ($row) { $current_user = (object)$row; $user_id = (int)$row['uid']; }
    }
}
if (!$current_user) { header("Location: index.php"); exit(); }
function val($o,$k,$d=''){ return (is_object($o)&&isset($o->$k)&&$o->$k!==null)?$o->$k:$d; }
$first_name = (string)val($current_user,'first_name','');
$last_name = (string)val($current_user,'last_name','');
$email = (string)val($current_user,'email','');
$contact = (string)val($current_user,'contact','');
$avPath = (string)val($current_user,'avatar_path','');
$avThumb = (string)val($current_user,'avatar_thumb','');
$avFull = (string)val($current_user,'avatar','');

// Process avatar URLs for S3/local support
if (!empty($avFull)) {
    // Check if it's an S3 URL or local path
    if (strpos($avFull, 'http') === 0) {
        // S3 URL or full URL
        $avatar_large = $avFull;
    } else {
        // Local path - construct proper URL using correct base path
        $avatar_large = $correct_base_path . "/" . $avPath . $avFull;
    }
} else {
    // Use default avatar with correct base path
    $avatar_large = $admin_path . "assets/admin/layout/img/avatar.png";
}

if (!empty($avThumb)) {
    // Check if it's an S3 URL or local path
    if (strpos($avThumb, 'http') === 0) {
        // S3 URL or full URL
        $avatar = $avThumb;
    } else {
        // Local path - construct proper URL using correct base path
        $avatar = $correct_base_path . "/" . $avPath . $avThumb;
    }
} else {
    // Use default avatar with correct base path
    $avatar = $admin_path . "assets/admin/layout/img/avatar.png";
}
$menu = 'inc/left-menu-user.php';
if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'webmaster') { $menu = 'inc/left-menu-webmaster.php'; }
elseif (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin') { $menu = 'inc/left-menu-admin.php'; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<title>Profile</title>
<base href="<?= $admin_path ?>">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<meta http-equiv="Content-type" content="text/html; charset=utf-8">
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
<!-- BEGIN PAGE LEVEL STYLES -->
<link href="assets/global/plugins/bootstrap-fileinput/bootstrap-fileinput.css" rel="stylesheet" type="text/css"/>
<link href="assets/admin/pages/css/profile.css" rel="stylesheet" type="text/css"/>
<link href="assets/admin/pages/css/tasks.css" rel="stylesheet" type="text/css"/>
<!-- END PAGE LEVEL STYLES -->
<!-- BEGIN THEME STYLES -->
<link href="assets/global/css/components.css" id="style_components" rel="stylesheet" type="text/css"/>
<link href="assets/global/css/plugins.css" rel="stylesheet" type="text/css"/>
<link href="assets/admin/layout/css/layout.css" rel="stylesheet" type="text/css"/>
<link id="style_color" href="assets/admin/layout/css/themes/darkblue.css" rel="stylesheet" type="text/css"/>
<link href="assets/admin/layout/css/custom.css" rel="stylesheet" type="text/css"/>
<!-- END THEME STYLES -->
<link rel="shortcut icon" href="favicon.ico"/>
</head>
<!-- END HEAD -->
<body class="page-header-fixed page-quick-sidebar-over-content page-style-square">
<!-- BEGIN HEADER -->

<!-- END HEADER -->
<div class="clearfix">
</div>
<!-- BEGIN CONTAINER -->
<div class="page-container">
	<!-- BEGIN SIDEBAR -->
	<div class="page-sidebar-wrapper">
		<!-- DOC: Set data-auto-scroll="false" to disable the sidebar from auto scrolling/focusing -->
		<!-- DOC: Change data-auto-speed="200" to adjust the sub menu slide up/down speed -->
		<div class="page-sidebar navbar-collapse collapse">
			<!-- BEGIN SIDEBAR MENU -->
			<?php include($menu); ?>
			<!-- END SIDEBAR MENU -->
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
            <a href="profile-modern.php">User Profile</a>
        </li>
    </ul>
</div>
<div class="row">
  <div class="col-md-12">
    <div class="portlet box blue">
      <div class="portlet-title">
        <div class="caption">
          <i class="fa fa-user"></i>User Profile
        </div>
      </div>
      <div class="portlet-body">
        <div class="row">
            <div class="col-md-3">
                <ul class="list-group">
                    <li class="list-group-item">
                        <img src="<?= htmlspecialchars($avatar_large) ?>" alt="Avatar" style="width:100%;border-radius:6px">
                    </li>
                    <li class="list-group-item text-center">
                        <h4 style="margin:0;"><?= htmlspecialchars($first_name.' '.$last_name) ?></h4>
                        <p><?= htmlspecialchars($email) ?></p>
                    </li>
                </ul>
            </div>
            <div class="col-md-9">
                <div class="tab-container">
                  <ul class="nav nav-tabs">
                    <li class="active"><a href="#overview" data-toggle="tab">Overview</a></li>
                    <li><a href="#edit" data-toggle="tab">Edit Info</a></li>
                    <li><a href="#avatar" data-toggle="tab">Change Avatar</a></li>
                    <li><a href="#password" data-toggle="tab">Change Password</a></li>
                  </ul>
                  <div class="tab-content">
                    <div class="tab-pane active" id="overview">
                      <div class="well" style="margin-top:15px;">
                        <p><strong>Name:</strong> <span id="ov_name"><?= htmlspecialchars($first_name.' '.$last_name) ?></span></p>
                        <p><strong>Email:</strong> <span id="ov_email"><?= htmlspecialchars($email) ?></span></p>
                        <p><strong>Contact:</strong> <span id="ov_contact"><?= htmlspecialchars($contact) ?></span></p>
                      </div>
                    </div>
                    <div class="tab-pane" id="edit">
                      <form class="form-horizontal" method="post" action="profile.php" style="margin-top:15px">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                        <input type="hidden" name="submit" value="personal_info">
                        <div class="form-group">
                          <label class="col-md-3 control-label">First Name</label>
                          <div class="col-md-6"><input type="text" class="form-control" name="first_name" value="<?= htmlspecialchars($first_name) ?>"></div>
                        </div>
                        <div class="form-group">
                          <label class="col-md-3 control-label">Last Name</label>
                          <div class="col-md-6"><input type="text" class="form-control" name="last_name" value="<?= htmlspecialchars($last_name) ?>"></div>
                        </div>
                        <div class="form-group">
                          <label class="col-md-3 control-label">Email</label>
                          <div class="col-md-6"><input type="email" class="form-control" name="email" value="<?= htmlspecialchars($email) ?>"></div>
                        </div>
                        <div class="form-group">
                          <label class="col-md-3 control-label">Contact</label>
                          <div class="col-md-6"><input type="text" class="form-control" name="contact" value="<?= htmlspecialchars($contact) ?>"></div>
                        </div>
                        <div class="form-group">
                          <div class="col-md-offset-3 col-md-6">
                            <button type="submit" class="btn btn-primary">Update</button>
                          </div>
                        </div>
                      </form>
                    </div>
                    <div class="tab-pane" id="avatar">
                      <form class="form-horizontal" method="post" action="profile.php" enctype="multipart/form-data" style="margin-top:15px">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                        <input type="hidden" name="update_user_avatar" value="1">
                        <div class="form-group">
                          <label class="col-md-3 control-label">Avatar</label>
                          <div class="col-md-6">
                            <input type="file" class="form-control" name="avatar" accept=".jpg,.jpeg,.png,.gif">
                          </div>
                        </div>
                        <div class="form-group">
                          <div class="col-md-offset-3 col-md-6">
                            <button type="submit" class="btn btn-primary">Update</button>
                          </div>
                        </div>
                      </form>
                    </div>
                    <div class="tab-pane" id="password">
                      <form class="form-horizontal" method="post" action="profile.php" style="margin-top:15px">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                        <input type="hidden" name="update_user_pass" value="1">
                        <div class="form-group">
                          <label class="col-md-3 control-label">Current Password</label>
                          <div class="col-md-6"><input type="password" class="form-control" name="password"></div>
                        </div>
                        <div class="form-group">
                          <label class="col-md-3 control-label">New Password</label>
                          <div class="col-md-6"><input type="password" class="form-control" name="password_new1"></div>
                        </div>
                        <div class="form-group">
                          <label class="col-md-3 control-label">Confirm Password</label>
                          <div class="col-md-6"><input type="password" class="form-control" name="password_new2"></div>
                        </div>
                        <div class="form-group">
                          <div class="col-md-offset-3 col-md-6">
                            <button type="submit" class="btn btn-primary">Change Password</button>
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
  </div>
</div>
</div>
</div>
<?php include('inc/footer.php'); ?>
<!-- END FOOTER -->
<!-- BEGIN JAVASCRIPTS(Load javascripts at bottom, this will reduce page load time) -->
<!-- BEGIN CORE PLUGINS -->
<script src="assets/global/plugins/jquery.min.js" type="text/javascript"></script>
<script src="assets/global/plugins/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
<script src="assets/global/plugins/jquery-slimscroll/jquery.slimscroll.min.js" type="text/javascript"></script>
<!-- END CORE PLUGINS -->
<!-- BEGIN PAGE LEVEL PLUGINS -->
<script src="assets/global/plugins/bootstrap-fileinput/bootstrap-fileinput.js" type="text/javascript"></script>
<!-- END PAGE LEVEL PLUGINS -->
<!-- BEGIN PAGE LEVEL SCRIPTS -->
<script src="assets/global/scripts/metronic.js" type="text/javascript"></script>
<script src="assets/admin/layout/scripts/layout.js" type="text/javascript"></script>
<!-- END PAGE LEVEL SCRIPTS -->
<script>
(function(){
  Metronic.init(); // init metronic core components
  Layout.init(); // init current layout

  function setText(id, v){ var el=document.getElementById(id); if(el) el.textContent=v||''; }
  $.getJSON('profile.php?format=json').done(function(d){
    setText('ov_name', (d.first_name||'')+' '+(d.last_name||''));
    setText('ov_email', d.email||'');
    setText('ov_contact', d.contact||'');
  });
})();
</script>
</body>
</html>