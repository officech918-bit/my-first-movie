<?php
declare(strict_types=1);

use App\Models\Order;
use App\Models\WebUser;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/database.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_type']) || !in_array($_SESSION['user_type'], ['webmaster', 'admin'])) {
    http_response_code(403);
    exit('Forbidden');
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];
$menu = $_SESSION['user_type'] === 'webmaster' ? 'inc/left-menu-webmaster.php' : 'inc/left-menu-admin.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: ' . $admin_path . 'orders');
    exit;
}

$order = Order::with('user')->find($id);
if (!$order) {
    $_SESSION['error_message'] = 'Order not found';
    header('Location: ' . $admin_path . 'orders');
    exit;
}

$statuses = ['Success', 'Aborted', 'Failure', 'Pending', 'Cancelled', 'Refunded'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $_SESSION['error_message'] = 'Invalid CSRF token.';
        header('Location: ' . $admin_path . 'orders/' . $id . '/edit');
        exit;
    }
    $new_total = isset($_POST['amount']) ? trim($_POST['amount']) : '';
    $new_status = isset($_POST['order_status']) ? trim($_POST['order_status']) : '';
    $errors = [];
    if (!is_numeric($new_total)) {
        $errors['total'] = 'Total must be numeric.';
    }
    if (!in_array($new_status, $statuses, true)) {
        $errors['status'] = 'Invalid status.';
    }
    if ($errors) {
        $_SESSION['form_errors'] = $errors;
        header('Location: ' . $admin_path . 'orders/' . $id . '/edit');
        exit;
    }
    $order->amount = (float)$new_total;
    $order->order_status = $new_status;
    $order->save();
    $_SESSION['success_message'] = 'Order updated.';
    header('Location: ' . $admin_path . 'orders');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en" class="no-js">
<head>
    <meta charset="utf-8"/>
    <title>Edit Order #<?php echo (int)$order->id; ?> | My First Movie</title>
    <base href="<?php echo $admin_path; ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="width=device-width, initial-scale=1" name="viewport"/>
    <meta content="" name="description"/>
    <meta content="" name="author"/>
    <link href="http://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700&subset=all" rel="stylesheet" type="text/css"/>
    <link href="assets/global/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css"/>
    <link href="assets/global/plugins/simple-line-icons/simple-line-icons.min.css" rel="stylesheet" type="text/css"/>
    <link href="assets/global/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
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
            <h3 class="page-title">Edit Order #<?php echo (int)$order->id; ?></h3>
            <div class="portlet">
                <div class="portlet-body">
                    <form method="post" class="form-horizontal">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                        <div class="form-group">
                            <label class="col-md-2 control-label">User</label>
                            <div class="col-md-6">
                                <p class="form-control-static">
                                    <?php echo htmlspecialchars(optional($order->user)->first_name . ' ' . optional($order->user)->last_name); ?>
                                    (<?php echo htmlspecialchars(optional($order->user)->email); ?>)
                                </p>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-2 control-label">Amount</label>
                            <div class="col-md-4">
                                <input type="text" name="amount" class="form-control" value="<?php echo htmlspecialchars((string)$order->amount); ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-2 control-label">Order Status</label>
                            <div class="col-md-4">
                                <select name="order_status" class="form-control">
                                    <?php foreach ($statuses as $st): ?>
                                        <option value="<?php echo htmlspecialchars($st); ?>" <?php echo $order->order_status === $st ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($st); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-2 control-label">Date</label>
                            <div class="col-md-6">
                                <p class="form-control-static"><?php echo htmlspecialchars((string)$order->date); ?></p>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-offset-2 col-md-6">
                                <button type="submit" class="btn btn-primary">Save</button>
                                <a href="orders" class="btn btn-default">Back</a>
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
<script src="assets/global/plugins/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
</body>
</html>
