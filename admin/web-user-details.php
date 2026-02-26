<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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

if (!isset($_SESSION['user_type']) || !in_array($_SESSION['user_type'], ['webmaster', 'admin'])) {
    http_response_code(403);
    exit('Forbidden');
}

if (!isset($vars['id']) || !is_numeric($vars['id'])) {
    http_response_code(400);
    exit('Invalid user id');
}

$user = WebUser::find((int)$vars['id']);
if (!$user) {
    http_response_code(404);
    exit('User not found');
}

$menu = 'inc/left-menu-user.php';
if ($_SESSION['user_type'] == 'webmaster') {
    $menu = 'inc/left-menu-webmaster.php';
} else if ($_SESSION['user_type'] == 'admin') {
    $menu = 'inc/left-menu-admin.php';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <title>View Web User</title>
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
</head>
<body class="page-header-fixed page-quick-sidebar-over-content">
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
            <h3 class="page-title">Web User Details</h3>
            <div class="page-bar">
                <ul class="page-breadcrumb">
                    <li><i class="fa fa-home"></i><a href="dashboard.php">Dashboard</a><i class="fa fa-angle-right"></i></li>
                    <li><a href="web-users">Web Users</a><i class="fa fa-angle-right"></i></li>
                    <li><span>View</span></li>
                </ul>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="portlet box blue">
                        <div class="portlet-title">
                            <div class="caption"><i class="fa fa-user"></i>Profile</div>
                            <div class="actions btn-set">
                                <a href="web-user/<?php echo (int)$user->uid; ?>/edit" class="btn green">Edit</a>
                                <a href="web-users" class="btn default">Back</a>
                            </div>
                        </div>
                        <div class="portlet-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-striped">
                                        <tr><th>User ID</th><td><?php echo (int)$user->uid; ?></td></tr>
                                        <tr><th>Name</th><td><?php echo htmlspecialchars((string)(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''))); ?></td></tr>
                                        <tr><th>Email</th><td><?php echo htmlspecialchars((string)($user->email ?? '')); ?></td></tr>
                                        <tr><th>Contact</th><td><?php echo htmlspecialchars((string)($user->contact ?? '')); ?></td></tr>
                                        <tr><th>Company</th><td><?php echo htmlspecialchars((string)($user->company ?? '')); ?></td></tr>
                                        <tr><th>Gender</th><td><?php echo htmlspecialchars((string)($user->gender ?? '')); ?></td></tr>
                                        <tr><th>User Type</th><td><?php echo htmlspecialchars((string)($user->user_type ?? 'user')); ?></td></tr>
                                        <tr><th>Status</th><td><?php echo ($user->status ?? 1) ? 'Active' : 'Inactive'; ?></td></tr>
                                        <tr><th>Admin Approved</th><td><?php echo ($user->admin_approved ?? 1) ? 'Approved' : 'Pending'; ?></td></tr>
                                        <tr><th>Newsletter</th><td><?php echo ($user->newsletter ?? 0) ? 'Subscribed' : 'Not Subscribed'; ?></td></tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-striped">
                                        <tr><th>Address</th><td><?php echo htmlspecialchars((string)($user->address ?? '')); ?></td></tr>
                                        <tr><th>Billing Address</th><td><?php echo htmlspecialchars((string)($user->billing_address ?? '')); ?></td></tr>
                                        <tr><th>City</th><td><?php echo htmlspecialchars((string)($user->city ?? '')); ?></td></tr>
                                        <tr><th>State</th><td><?php echo htmlspecialchars((string)($user->state ?? '')); ?></td></tr>
                                        <tr><th>Zip</th><td><?php echo htmlspecialchars((string)($user->zip ?? '')); ?></td></tr>
                                        <tr><th>Country</th><td><?php echo htmlspecialchars((string)($user->country ?? '')); ?></td></tr>
                                        <tr><th>Region</th><td><?php echo htmlspecialchars((string)($user->region ?? '')); ?></td></tr>
                                        <tr><th>Avatar</th><td><?php echo htmlspecialchars((string)($user->avatar ?? '')); ?></td></tr>
                                    </table>
                                </div>
                            </div>
                            <h4>System & Account</h4>
                            <table class="table table-striped">
                                <tr><th>Registration IP</th><td><?php echo htmlspecialchars((string)($user->ip ?? 'N/A')); ?></td></tr>
                                <tr><th>Creation Date</th><td><?php echo htmlspecialchars((string)($user->create_date ?? 'N/A')); ?></td></tr>
                                <tr><th>Last Update On</th><td><?php echo htmlspecialchars((string)($user->last_update_on ?? '')); ?></td></tr>
                                <tr><th>Last Login</th><td><?php echo htmlspecialchars((string)($user->last_login ?? '')); ?></td></tr>
                                <tr><th>Activation Status</th><td><?php echo ($user->activation_status ?? 1) ? 'Active' : 'Inactive'; ?></td></tr>
                                <tr><th>Activation Code</th><td><?php echo htmlspecialchars((string)($user->activation_code ?? '')); ?></td></tr>
                                <tr><th>Activation Time</th><td><?php echo htmlspecialchars((string)($user->activation_time ?? '')); ?></td></tr>
                                <tr><th>Activation Expire Time</th><td><?php echo htmlspecialchars((string)($user->activation_expire_time ?? '')); ?></td></tr>
                                <tr><th>Activation Link</th><td><?php echo htmlspecialchars((string)($user->activation_link ?? '')); ?></td></tr>
                                <tr><th>Reset Request ID</th><td><?php echo htmlspecialchars((string)($user->reset_req_id ?? '')); ?></td></tr>
                                <tr><th>Reset Time</th><td><?php echo htmlspecialchars((string)($user->reset_time ?? '')); ?></td></tr>
                                <tr><th>Reset Expire Time</th><td><?php echo htmlspecialchars((string)($user->reset_expire_time ?? '')); ?></td></tr>
                            </table>
                            <div class="alert alert-warning">
                                Password is not displayed for security. Use the Edit button to set a new password if needed.
                            </div>
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
