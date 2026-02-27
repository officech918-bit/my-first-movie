<?php
declare(strict_types=1);
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/database.php';

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
use App\Models\WebUser;
use Illuminate\Database\Capsule\Manager as Capsule;
if (!isset($_SESSION['user_type']) || !in_array($_SESSION['user_type'], ['webmaster','admin'])) { http_response_code(403); exit('Forbidden'); }
if (empty($_SESSION['csrf_token'])) { $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); }
$csrf = $_SESSION['csrf_token'];
if (!isset($vars['id']) || !is_numeric($vars['id'])) { http_response_code(400); exit('Invalid user id'); }
$user = WebUser::find((int)$vars['id']);
if (!$user) { http_response_code(404); exit('User not found'); }
$form_action = 'web-user/' . (int)$user->uid . '/edit';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!isset($_POST['csrf_token']) || !hash_equals($csrf, $_POST['csrf_token'])) { $_SESSION['error_message']='CSRF token validation failed.'; header('Location: '.$form_action); exit; }
  $fillable = [
    'first_name','last_name','contact','email','company','address','city','state','zip','country','region','status',
    'gender','billing_address','about_me','newsletter','user_type','admin_approved','activation_status','tnc_agreed',
    'avatar','last_update_on','activation_code','activation_time','activation_expire_time','activation_link',
    'reset_req_id','reset_time','reset_expire_time','last_login'
  ];
  $data = [];
  foreach ($fillable as $f) { $data[$f] = $_POST[$f] ?? $user->$f; }
  $hasPasswordColumn = Capsule::schema()->hasColumn('web_users','password');
  if (!empty($_POST['password'])) {
    if ($_POST['password'] !== ($_POST['password_confirmation'] ?? '')) {
      $_SESSION['error_message'] = 'Passwords do not match.'; header('Location: '.$form_action); exit;
    }
    $hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
    if ($hasPasswordColumn) {
        $data['password'] = $hash;
    }
    $data['hash_code'] = $hash;
  }
  $user->update($data);
  $_SESSION['success_message'] = 'User updated successfully!';
  header('Location: web-users'); exit;
}
$menu = 'inc/left-menu-admin.php';
if ($_SESSION['user_type'] == 'webmaster') { $menu = 'inc/left-menu-webmaster.php'; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <title>Edit Web User</title>
  <base href="<?php echo $admin_path; ?>">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
  <link href="http://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700&subset=all" rel="stylesheet" type="text/css"/>
  <link href="assets/global/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css"/>
  <link href="assets/global/plugins/simple-line-icons/simple-line-icons.min.css" rel="stylesheet" type="text/css"/>
  <link href="assets/global/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
  <link href="assets/global/css/components.css" id="style_components" rel="stylesheet" type="text/css"/>
  <link href="assets/global/css/plugins.css" rel="stylesheet" type="text/css"/>
  <link href="assets/admin/layout/css/layout.css" rel="stylesheet" type="text/css"/>
  <link id="style_color" href="assets/admin/layout/css/themes/darkblue.css" rel="stylesheet" type="text/css"/>
  <link href="assets/admin/layout/css/custom.css" rel="stylesheet" type="text/css"/>
  <link rel="shortcut icon" href="favicon.ico"/>
  <style>.help-block{color:red}</style>
  </head>
<body class="page-header-fixed page-quick-sidebar-over-content">
<?php include('inc/header.php'); ?>
<div class="clearfix"></div>
<div class="page-container">
  <div class="page-sidebar-wrapper">
    <div class="page-sidebar navbar-collapse collapse"><?php include($menu); ?></div>
  </div>
  <div class="page-content-wrapper">
    <div class="page-content">
      <h3 class="page-title">Edit Web User</h3>
      <div class="page-bar">
        <ul class="page-breadcrumb">
          <li><i class="fa fa-home"></i><a href="dashboard.php">Dashboard</a><i class="fa fa-angle-right"></i></li>
          <li><a href="web-users">Web Users</a><i class="fa fa-angle-right"></i></li>
          <li><span>Edit</span></li>
        </ul>
      </div>
      <?php if (!empty($_SESSION['error_message'])): ?><div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?></div><?php endif; ?>
      <?php if (!empty($_SESSION['success_message'])): ?><div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?></div><?php endif; ?>
      <div class="row">
        <div class="col-md-12">
          <div class="portlet box blue">
            <div class="portlet-title">
              <div class="caption"><i class="fa fa-users"></i>Web User Details</div>
              <div class="actions btn-set"><a href="web-users" class="btn default">Back</a></div>
            </div>
            <div class="portlet-body form">
              <form action="<?php echo $form_action; ?>" method="post" class="form-horizontal">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf; ?>">
                <div class="form-body">
                  <div class="form-group"><label class="col-md-3 control-label">First Name</label><div class="col-md-4"><input type="text" name="first_name" class="form-control" value="<?php echo htmlspecialchars((string)($user->first_name ?? '')); ?>" required></div></div>
                  <div class="form-group"><label class="col-md-3 control-label">Last Name</label><div class="col-md-4"><input type="text" name="last_name" class="form-control" value="<?php echo htmlspecialchars((string)($user->last_name ?? '')); ?>" required></div></div>
                  <div class="form-group"><label class="col-md-3 control-label">Email</label><div class="col-md-4"><input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars((string)($user->email ?? '')); ?>" required></div></div>
                  <div class="form-group"><label class="col-md-3 control-label">Gender</label><div class="col-md-4"><select name="gender" class="form-control"><option value="">Select Gender</option><option value="Male" <?php echo ($user->gender ?? '')=='Male'?'selected':''; ?>>Male</option><option value="Female" <?php echo ($user->gender ?? '')=='Female'?'selected':''; ?>>Female</option><option value="Other" <?php echo ($user->gender ?? '')=='Other'?'selected':''; ?>>Other</option></select></div></div>
                  <div class="form-group"><label class="col-md-3 control-label">Password</label><div class="col-md-4"><input type="password" name="password" class="form-control" placeholder="Leave blank to keep current"></div></div>
                  <div class="form-group"><label class="col-md-3 control-label">Confirm Password</label><div class="col-md-4"><input type="password" name="password_confirmation" class="form-control"></div></div>
                  <div class="form-group"><label class="col-md-3 control-label">Contact</label><div class="col-md-4"><input type="text" name="contact" class="form-control" value="<?php echo htmlspecialchars((string)($user->contact ?? '')); ?>"></div></div>
                  <div class="form-group"><label class="col-md-3 control-label">Company</label><div class="col-md-4"><input type="text" name="company" class="form-control" value="<?php echo htmlspecialchars((string)($user->company ?? '')); ?>"></div></div>
                  <div class="form-group"><label class="col-md-3 control-label">Address</label><div class="col-md-4"><input type="text" name="address" class="form-control" value="<?php echo htmlspecialchars((string)($user->address ?? '')); ?>"></div></div>
                  <div class="form-group"><label class="col-md-3 control-label">Billing Address</label><div class="col-md-4"><textarea name="billing_address" class="form-control" rows="3"><?php echo htmlspecialchars((string)($user->billing_address ?? '')); ?></textarea></div></div>
                  <div class="form-group"><label class="col-md-3 control-label">City</label><div class="col-md-4"><input type="text" name="city" class="form-control" value="<?php echo htmlspecialchars((string)($user->city ?? '')); ?>"></div></div>
                  <div class="form-group"><label class="col-md-3 control-label">State</label><div class="col-md-4"><input type="text" name="state" class="form-control" value="<?php echo htmlspecialchars((string)($user->state ?? '')); ?>"></div></div>
                  <div class="form-group"><label class="col-md-3 control-label">Zip</label><div class="col-md-4"><input type="text" name="zip" class="form-control" value="<?php echo htmlspecialchars((string)($user->zip ?? '')); ?>"></div></div>
                  <div class="form-group"><label class="col-md-3 control-label">Country</label><div class="col-md-4"><input type="text" name="country" class="form-control" value="<?php echo htmlspecialchars((string)($user->country ?? 'India')); ?>"></div></div>
                  <div class="form-group"><label class="col-md-3 control-label">About Me</label><div class="col-md-4"><textarea name="about_me" class="form-control" rows="4"><?php echo htmlspecialchars((string)($user->about_me ?? '')); ?></textarea></div></div>
                  <div class="form-group"><label class="col-md-3 control-label">Region</label><div class="col-md-4"><input type="text" name="region" class="form-control" value="<?php echo htmlspecialchars((string)($user->region ?? '')); ?>"></div></div>
                  <div class="form-group"><label class="col-md-3 control-label">User Type</label><div class="col-md-4"><select name="user_type" class="form-control"><option value="user" <?php echo ($user->user_type ?? 'user')=='user'?'selected':''; ?>>User</option><option value="webmaster" <?php echo ($user->user_type ?? 'user')=='webmaster'?'selected':''; ?>>Webmaster</option><option value="admin" <?php echo ($user->user_type ?? 'user')=='admin'?'selected':''; ?>>Admin</option></select></div></div>
                  <div class="form-group"><label class="col-md-3 control-label">Admin Approved</label><div class="col-md-4"><select name="admin_approved" class="form-control"><option value="1" <?php echo ($user->admin_approved ?? 1)==1?'selected':''; ?>>Approved</option><option value="0" <?php echo ($user->admin_approved ?? 1)==0?'selected':''; ?>>Pending</option></select></div></div>
                  <div class="form-group"><label class="col-md-3 control-label">Activation Status</label><div class="col-md-4"><select name="activation_status" class="form-control"><option value="1" <?php echo ($user->activation_status ?? 1)==1?'selected':''; ?>>Active</option><option value="0" <?php echo ($user->activation_status ?? 1)==0?'selected':''; ?>>Inactive</option></select></div></div>
                  <div class="form-group"><label class="col-md-3 control-label">Newsletter</label><div class="col-md-4"><select name="newsletter" class="form-control"><option value="1" <?php echo ($user->newsletter ?? 0)==1?'selected':''; ?>>Subscribed</option><option value="0" <?php echo ($user->newsletter ?? 0)==0?'selected':''; ?>>Not Subscribed</option></select></div></div>
                  <div class="form-group"><label class="col-md-3 control-label">T&C Agreed</label><div class="col-md-4"><select name="tnc_agreed" class="form-control"><option value="1" <?php echo ($user->tnc_agreed ?? 0)==1?'selected':''; ?>>Yes</option><option value="0" <?php echo ($user->tnc_agreed ?? 0)==0?'selected':''; ?>>No</option></select></div></div>
                  <div class="form-group"><label class="col-md-3 control-label">Avatar Path</label><div class="col-md-4"><input type="text" name="avatar" class="form-control" value="<?php echo htmlspecialchars((string)($user->avatar ?? '')); ?>"></div></div>
                  <div class="form-group"><label class="col-md-3 control-label">Status</label><div class="col-md-4"><select name="status" class="form-control"><option value="1" <?php echo ($user->status ?? 1)==1?'selected':''; ?>>Active</option><option value="0" <?php echo ($user->status ?? 1)==0?'selected':''; ?>>Inactive</option></select></div></div>
                  <h4 class="form-section">System & Account</h4>
                  <div class="form-group"><label class="col-md-3 control-label">User ID</label><div class="col-md-4"><p class="form-control-static"><?php echo (int)$user->uid; ?></p></div></div>
                  <div class="form-group"><label class="col-md-3 control-label">Registration IP</label><div class="col-md-4"><p class="form-control-static"><?php echo htmlspecialchars((string)($user->ip ?? 'N/A')); ?></p></div></div>
                  <div class="form-group"><label class="col-md-3 control-label">Creation Date</label><div class="col-md-4"><p class="form-control-static"><?php echo htmlspecialchars((string)($user->create_date ?? 'N/A')); ?></p></div></div>
                  <div class="form-group"><label class="col-md-3 control-label">Last Update On</label><div class="col-md-4"><input type="text" name="last_update_on" class="form-control" value="<?php echo htmlspecialchars((string)($user->last_update_on ?? '')); ?>"></div></div>
                  <div class="form-group"><label class="col-md-3 control-label">Last Login</label><div class="col-md-4"><input type="text" name="last_login" class="form-control" value="<?php echo htmlspecialchars((string)($user->last_login ?? '')); ?>"></div></div>
                  <div class="form-group"><label class="col-md-3 control-label">Activation Code</label><div class="col-md-4"><input type="text" name="activation_code" class="form-control" value="<?php echo htmlspecialchars((string)($user->activation_code ?? '')); ?>"></div></div>
                  <div class="form-group"><label class="col-md-3 control-label">Activation Time</label><div class="col-md-4"><input type="text" name="activation_time" class="form-control" value="<?php echo htmlspecialchars((string)($user->activation_time ?? '')); ?>"></div></div>
                  <div class="form-group"><label class="col-md-3 control-label">Activation Expire Time</label><div class="col-md-4"><input type="text" name="activation_expire_time" class="form-control" value="<?php echo htmlspecialchars((string)($user->activation_expire_time ?? '')); ?>"></div></div>
                  <div class="form-group"><label class="col-md-3 control-label">Activation Link</label><div class="col-md-4"><input type="text" name="activation_link" class="form-control" value="<?php echo htmlspecialchars((string)($user->activation_link ?? '')); ?>"></div></div>
                  <div class="form-group"><label class="col-md-3 control-label">Reset Request ID</label><div class="col-md-4"><input type="text" name="reset_req_id" class="form-control" value="<?php echo htmlspecialchars((string)($user->reset_req_id ?? '')); ?>"></div></div>
                  <div class="form-group"><label class="col-md-3 control-label">Reset Time</label><div class="col-md-4"><input type="text" name="reset_time" class="form-control" value="<?php echo htmlspecialchars((string)($user->reset_time ?? '')); ?>"></div></div>
                  <div class="form-group"><label class="col-md-3 control-label">Reset Expire Time</label><div class="col-md-4"><input type="text" name="reset_expire_time" class="form-control" value="<?php echo htmlspecialchars((string)($user->reset_expire_time ?? '')); ?>"></div></div>
                </div>
                <div class="form-actions">
                  <div class="row"><div class="col-md-offset-3 col-md-9"><button type="submit" class="btn green">Save</button><a href="web-users" class="btn default">Cancel</a></div></div>
                </div>
              </form>
              <div class="alert alert-warning" style="margin-top:10px;">Password is not displayed. Enter a new one above to change it.</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include('inc/footer.php'); ?>
<script src="assets/global/plugins/jquery.min.js" type="text/javascript"></script>
<script src="assets/global/plugins/jquery-migrate.min.js" type="text/javascript"></script>
<script src="assets/global/plugins/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
<script src="assets/global/scripts/metronic.js" type="text/javascript"></script>
<script src="assets/admin/layout/scripts/layout.js" type="text/javascript"></script>
</body>
</html>
