<?php

/**
 * Account Activation Page.
 *
 * This page handles the activation of a new user account. It allows users
 * to enter an activation code sent to their email. It also provides an
 * option to resend the activation code if needed.
 *
 * @package MFM
 * @subpackage Members
 */

declare(strict_types=1);

require_once __DIR__ . '/inc/requires.php';
require_once __DIR__ . '/../classes/Mailer.php'; // Include the Mailer class file

use App\Mailer; // Use the Mailer namespace

// Instantiate Mailer
$mailer = new Mailer();

// Immediately redirect if the user is not logged in or is already active.
if (!$user->check_session()) {
    header("Location: index.php");
    exit();
}

if ($user->isActive()) {
    header("Location: dashboard.php");
    exit();
}

$user_id = (int) $_SESSION['uid'];
$user_email = $_SESSION['email'] ?? '';
$show_msg = '';
$is_error = false;
$is_local = in_array($_SERVER['SERVER_NAME'], ['localhost', '127.0.0.1'], true) || strpos($_SERVER['HTTP_HOST'], 'localhost') !== false;

// Display activation message if set in session
if (isset($_SESSION['activation_message'])) {
    $show_msg = $_SESSION['activation_message'];
    unset($_SESSION['activation_message']); // Clear the message after displaying
}
$company_name = $user->get_company_name();
$path = $user->get_path();

/**
 * Generates a new activation code and sends it to the user.
 *
 * @param database $database The database object.
 * @param web_user $user The user object.
 * @param int $user_id The user's ID.
 * @param bool $is_local Whether the environment is local.
 * @return array An array containing the message and error status.
 */
function resend_activation_code(MySQLDB $database, web_user $user, Mailer $mailer, int $user_id, bool $is_local): array
{
    $new_code = (string) random_int(100000, 999999);
    $new_expire = date('Y-m-d H:i:s', time() + 86400); // 24 hours

    $stmt = $database->db->prepare("UPDATE web_users SET activation_code = ?, activation_expire_time = ? WHERE uid = ?");
    $stmt->execute([$new_code, $new_expire, $user_id]);

    // Fetch user details for the email template
    $stmt_user = $database->db->prepare("SELECT first_name, last_name, email FROM web_users WHERE uid = ?");
    $stmt_user->execute([$user_id]);
    $user_data = $stmt_user->fetch(PDO::FETCH_ASSOC);

    if (!$user_data) {
        return [
            'message' => 'User not found for sending activation email.',
            'is_error' => true
        ];
    }

    $sitename = $user->get_sitename();
    $company_name = $user->get_company_name();
    $activation_link = $sitename . '/members/activate.php?email=' . urlencode($user_data['email']) . '&code=' . $new_code;

    $email_subject = $company_name . " - Account Activation Code";
    $template_data = [
        'first_name' => $user_data['first_name'],
        'last_name' => $user_data['last_name'],
        'activation_code' => $new_code,
        'activation_link' => $activation_link,
        'company_name' => $company_name,
        'subject' => $email_subject
    ];

    $email_body = $mailer->renderTemplate('activation_code', $template_data);
    $isSent = $mailer->send($user_data['email'], $email_subject, $email_body);

    if (!$isSent) {
        // Log email sending failure for debugging
        error_log("Activation email failed to send to {$user_data['email']}.");
        return [
            'message' => 'Failed to send activation email. Please try again later.',
            'is_error' => true
        ];
    }

    return [
        'message' => 'A new activation code has been sent to your registered email address.',
        'is_error' => false
    ];
}

// Handle form submissions (resend or activate)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $show_msg = 'A security error occurred. Please try again.';
        $is_error = true;
    } else {
        if (isset($_POST['resend'])) {
            $result = resend_activation_code($database, $user, $mailer, $user_id, $is_local);
            $show_msg = $result['message'];
            $is_error = $result['is_error'];
        } elseif (isset($_POST['activate'])) {
            $submitted_code = trim($_POST['activation_code'] ?? '');

            if (empty($submitted_code)) {
                $show_msg = 'Please enter your activation code.';
                $is_error = true;
            } else {
                $stmt = $database->db->prepare("SELECT activation_code, activation_expire_time FROM web_users WHERE uid = ?");
                $stmt->execute([$user_id]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$result || !hash_equals($result['activation_code'], $submitted_code)) {
                    $show_msg = 'The activation code is incorrect.';
                    $is_error = true;
                } elseif (time() > strtotime($result['activation_expire_time'])) {
                    $show_msg = 'Your activation code has expired. Please request a new one.';
                    $is_error = true;
                } else {
                    // Activate the user
                    $update_stmt = $database->db->prepare("UPDATE web_users SET activation_status = 1, activation_time = NOW() WHERE uid = ?");
                    $update_stmt->execute([$user_id]);

                    header("Location: dashboard.php");
                    exit();
                }
            }
        }
    }
}

// Generate an initial activation code if one doesn't exist or has expired.
$stmt = $database->db->prepare("SELECT activation_code, activation_expire_time, activation_status FROM web_users WHERE uid = ?");
$stmt->execute([$user_id]);
$user_data = $stmt->fetch(PDO::FETCH_ASSOC);

if (
    !$user_data ||
    $user_data['activation_status'] != 1 &&
    (empty($user_data['activation_code']) || time() > strtotime($user_data['activation_expire_time']))
) {
    // Ensure the Mailer object is passed here as well
    $result = resend_activation_code($database, $user, $mailer, $user_id, $is_local);
    if (empty($show_msg)) { // Don't overwrite POST handling messages
        $show_msg = $result['message'];
        $is_error = $result['is_error'];
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Account Activation | <?php echo e($company_name); ?></title>
<link rel="shortcut icon" href="favicon.ico">

<!-- Fonts START -->
<link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700|PT+Sans+Narrow|Source+Sans+Pro:200,300,400,600,700,900&amp;subset=all" rel="stylesheet" type="text/css">
<!-- Fonts END -->

<!-- Global styles START -->
<link href="assets/global/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet">
<link href="assets/global/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet">
<!-- Global styles END -->

<!-- Page level plugin styles START -->
<link href="assets/global/plugins/uniform/css/uniform.default.css" rel="stylesheet" type="text/css">
<!-- Page level plugin styles END -->

<!-- Theme styles START -->
<link href="assets/global/css/components.css" rel="stylesheet">
<link href="assets/frontend/layout/css/style.css" rel="stylesheet">
<link href="assets/frontend/layout/css/style-responsive.css" rel="stylesheet">
<link href="assets/frontend/layout/css/themes/red.css" rel="stylesheet" id="style-color">
<link href="assets/frontend/layout/css/custom.css" rel="stylesheet">
<!-- Theme styles END -->
</head>
<body class="corporate">

<?php require_once __DIR__ . '/inc/header.php'; ?>
<!-- Header END -->

<div class="main">
  <div class="container">
    <ul class="breadcrumb">
      <li><a href="../index.php"><?php echo e($company_name); ?></a></li>
      <li class="active">Account Activation</li>
    </ul>
    <!-- BEGIN SIDEBAR & CONTENT -->
    <div class="row margin-bottom-40"> 
      <!-- BEGIN SIDEBAR -->
      <div class="sidebar col-md-2 col-sm-2"> </div>
      <!-- END SIDEBAR --> 
      
      <!-- BEGIN CONTENT -->
      <div class="col-md-10 col-sm-10">
        <h1>Account Activation</h1>
        <div class="content-form-page">
          <div class="row">
            <div class="col-md-7 col-sm-7">
              <?php if (!empty($show_msg)) : ?>
                <div class="alert alert-<?php echo $is_error ? 'danger' : 'success'; ?>">
                  <strong><?php echo $is_error ? 'Error!' : 'Success!'; ?></strong> <?php echo e($show_msg); ?>
                </div>
              <?php endif; ?>
              <p>Hi <?php echo e(isset($user) && $user->get('first_name') !== null ? $user->get('first_name') : ''); ?> <?php echo e(isset($user) && $user->get('last_name') !== null ? $user->get('last_name') : ''); ?>, we have sent a 6-digit activation code to your email address (<strong><?php echo e($user_email); ?></strong>). Please check your inbox/spam folder and enter it below to continue.</p>
              <form class="form-horizontal form-without-legend" role="form" action="" method="post">
                <input type="hidden" name="csrf_token" value="<?php echo e($_SESSION['csrf_token']); ?>">
                <div class="form-group <?php if ($is_error && !empty($error['activation_code'])) {
                    echo 'has-error';
                } ?>">
                  <label for="activation_code" class="col-lg-4 control-label">Activation Code</label>
                  <div class="col-lg-8">
                    <input type="text" class="form-control" name="activation_code" id="activation_code" value="" placeholder="Enter Code">
                    <?php if ($is_error && !empty($error['activation_code'])) {
                        echo '<span class="help-block">' . e($error['activation_code']) . '</span>';
                    } ?>
                  </div>
                </div>
                <div class="row">
                  <div class="col-lg-8 col-md-offset-4 padding-left-0 padding-top-5">
                    <button type="submit" name="activate" class="btn btn-primary">Continue</button>
                    <button type="submit" name="resend" class="btn btn-default pull-right">Resend Code</button>
                  </div>
                </div>
              </form>
              <div class="row">
                <div class="col-lg-8 col-md-offset-4 padding-left-0 padding-top-10 padding-right-30">
                  <hr>
                  <div class="login-socio">
                    <h2>Facing trouble? <a href="../../contact-us/" class="btn btn-default">Technical Support</a></h2>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-4 col-sm-4 pull-right"> </div>
          </div>
        </div>
      </div>
      <!-- END CONTENT --> 
    </div>
    <!-- END SIDEBAR & CONTENT --> 
  </div>
</div>

<!-- BEGIN FOOTER -->
<?php include('inc/footer.php'); ?>
<!-- END FOOTER --> 

<!-- Load javascripts at bottom, this will reduce page load time --> 
<!-- BEGIN CORE PLUGINS (REQUIRED FOR ALL PAGES) --> 
<!--[if lt IE 9]>
    <script src="assets/global/plugins/respond.min.js"></script>
    <![endif]--> 
<script src="assets/global/plugins/jquery.min.js" type="text/javascript"></script> 
<script src="assets/global/plugins/jquery-migrate.min.js" type="text/javascript"></script> 
<script src="assets/global/plugins/bootstrap/js/bootstrap.min.js" type="text/javascript"></script> 
<script src="assets/frontend/layout/scripts/back-to-top.js" type="text/javascript"></script> 
<script src="assets/global/plugins/jquery-slimscroll/jquery.slimscroll.min.js" type="text/javascript"></script> 
<!-- END CORE PLUGINS --> 

<!-- BEGIN PAGE LEVEL JAVASCRIPTS (REQUIRED ONLY FOR CURRENT PAGE) --> 
<script src="assets/global/plugins/fancybox/source/jquery.fancybox.pack.js" type="text/javascript"></script><!-- pop up --> 
<script src="assets/global/plugins/uniform/jquery.uniform.min.js" type="text/javascript"></script> 
<script src="assets/frontend/layout/scripts/layout.js" type="text/javascript"></script> 
<script type="text/javascript">
        jQuery(document).ready(function() {
            Layout.init();
            Layout.initUniform();
            Layout.initFixHeaderWithPreHeader(); /* Switch On Header Fixing (only if you have pre-header) */
        });
    </script> 
<!-- END PAGE LEVEL JAVASCRIPTS -->
</body>
<!-- END BODY -->
</html>