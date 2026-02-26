<?php declare(strict_types=1);

use App\Mailer;

/**
 * Reset Password Page.
 *
 * Handles password reset process after a user clicks on the link from the
 * forgot password email.
 *
 * @package MFM
 * @subpackage Members
 */

require_once __DIR__ . '/inc/requires.php';

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

// Get members path dynamically for URL generation
$scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$requestUri = $_SERVER['REQUEST_URI'];

// Extract the actual path from the current request
$uriParts = explode('/', trim($requestUri, '/'));
$membersIndex = array_search('members', $uriParts);

if ($membersIndex !== false) {
    $members_path = $scheme . '://' . $host . '/' . implode('/', array_slice($uriParts, 0, $membersIndex + 1)) . '/';
} else {
    $members_path = $scheme . '://' . $host . '/members/'; // fallback
}

$is_error = false;
$mailer = new Mailer();
$error = [];
$show_msg = '';

if ($user->check_session()) {
    header('Location: dashboard.php');
    exit;
}

$company_name = $user->get_company_name();
$from_email = $user->get_from_email();
$to_email = $user->get_to_email();
$sitename = $user->get_sitename();

$token = filter_input(INPUT_GET, 'token', FILTER_SANITIZE_STRING);

if (!$token) {
    $is_error = true;
    $show_msg = 'Invalid reset link. Please try the reset process again.';
} else {
    $token_hash = hash('sha256', $token);
    
    // We will look up the user by the hashed token.
    $query = 'SELECT uid, first_name, reset_expire_time FROM web_users WHERE reset_req_id = ?';
    $stmt = $database->db->prepare($query);
    $stmt->execute([$token_hash]);
    $resultArray = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$resultArray) {
        $is_error = true;
        $show_msg = 'Invalid or expired reset link. Please try the reset process again.';
    } else {
        $user_data = $resultArray;
        if (strtotime('now') > strtotime($user_data['reset_expire_time'])) {
            $is_error = true;
            $show_msg = 'Reset link has expired. Please request a new one.';
        }
    }
}

if (isset($_POST['change_pass'])) {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $is_error = true;
        $show_msg = 'CSRF token validation failed. Please try again.';
    } else {
        $password_new = $_POST['password_new'] ?? '';
        $password_confirm = $_POST['password_confirm'] ?? '';
        $token = filter_input(INPUT_POST, 'token', FILTER_SANITIZE_STRING);
        
        if (!$token) {
            $is_error = true;
            $show_msg = 'Invalid session. Please start the password reset process again.';
        } else {
            $token_hash = hash('sha256', $token);
        }

        if (empty($password_new)) {
            $is_error = true;
            $error['password_new'] = 'Please enter your new password.';
        } elseif (strlen($password_new) < 8) {
            $is_error = true;
            $error['password_new'] = 'Password must be at least 8 characters long.';
        } elseif (strlen($password_new) > 30) {
            $is_error = true;
            $error['password_new'] = 'Password cannot be more than 30 characters long.';
        }

        if ($password_new !== $password_confirm) {
            $is_error = true;
            $error['password_confirm'] = 'Passwords do not match.';
        }

        if (!$is_error) {
            // Re-validate the token on POST to ensure it's still valid.
            $query = 'SELECT uid, first_name, email, reset_expire_time FROM web_users WHERE reset_req_id = ?';
            $stmt = $database->db->prepare($query);
            $stmt->execute([$token_hash]);
            $resultArray = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$resultArray) {
                $is_error = true;
                $show_msg = 'Invalid or expired reset URL. Please try the reset process again.';
            } else {
                $user_data = $resultArray;
                $reset_expire_time = $user_data['reset_expire_time'];
                $uid = $user_data['uid'];
                $first_name = $user_data['first_name'];
                $user_email = $user_data['email']; // Get email from the database

                if (strtotime('now') > strtotime($reset_expire_time)) {
                    $is_error = true;
                    $show_msg = 'Reset link has expired. Please request a new one.';
                } else {
                    $hash_code = password_hash($password_new, PASSWORD_DEFAULT);
                    $query_update = 'UPDATE web_users SET hash_code = ?, reset_req_id = NULL, reset_expire_time = NULL WHERE uid = ?';
                    $stmt_update = $database->db->prepare($query_update);
                    if ($stmt_update->execute([$hash_code, $uid])) { // If the database update is successful
                        $email_subject = "Password Changed at {$company_name}";
                        $template_data = [
                            'first_name' => $first_name,
                            'company_name' => $company_name,
                            'sitename' => $sitename,
                        ];
                        $email_body = $mailer->renderTemplate('password_changed', $template_data);

                        // Send the password changed confirmation email
                        if (!$mailer->send($user_email, $email_subject, $email_body)) {
                            error_log("Failed to send password changed email to {$user_email}");
                        }

                        $_SESSION['reset_msg'] = 'Password changed successfully. You can now log in.';
                        header('Location: index.php');
                        exit;
                    } else {
                        $is_error = true;
                        $show_msg = 'Database update failed. Please try again.';
                    }
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8 no-js"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9 no-js"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en">
<!--<![endif]-->

<!-- Head BEGIN -->
<head>
  <meta charset="utf-8">
  <title>Reset Password | <?php echo htmlspecialchars($company_name, ENT_QUOTES, 'UTF-8'); ?></title>
  <link rel="shortcut icon" href="favicon.ico">

  <!-- Fonts START -->
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700|PT+Sans+Narrow|Source+Sans+Pro:200,300,400,600,700,900&amp;subset=all" rel="stylesheet" type="text/css"> 
  <!-- Fonts END -->

  <!-- Global styles START -->          
  <link href="assets/global/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet">
  <link href="assets/global/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <!-- Global styles END --> 
   
  <!-- Page level plugin styles START -->
  <link href="assets/global/plugins/fancybox/source/jquery.fancybox.css" rel="stylesheet">
   <link href="assets/global/plugins/uniform/css/uniform.default.css" rel="stylesheet" type="text/css">
  <link href="assets/global/plugins/carousel-owl-carousel/owl-carousel/owl.carousel.css" rel="stylesheet">
  <!-- Page level plugin styles END -->

  <!-- Theme styles START -->
  <link href="assets/global/css/components.css" rel="stylesheet">
  <link href="assets/frontend/layout/css/style.css" rel="stylesheet">
  <link href="assets/frontend/pages/css/style-shop.css" rel="stylesheet" type="text/css">
  <link href="assets/frontend/layout/css/style-responsive.css" rel="stylesheet">
  <link href="assets/frontend/layout/css/themes/red.css" rel="stylesheet" id="style-color">
  <link href="assets/frontend/layout/css/custom.css" rel="stylesheet">
  <!-- Theme styles END -->
  
  <!-- validation -->
  <link href="assets/frontend/layout/css/validationEngine.jquery.css" rel="stylesheet">
  
  <?php include('inc/pre-body.php'); ?>
</head>
<!-- Head END -->

<!-- Body BEGIN -->
<body class="ecommerce">
    <!-- BEGIN TOP BAR -->
    <?php include('inc/header.php'); ?>
    <!-- Header END -->
    
    <div class="main">
      <div class="container">
        <ul class="breadcrumb">
            <li><a href="<?php echo htmlspecialchars($sitename, ENT_QUOTES, 'UTF-8'); ?>/index.php"><?php echo htmlspecialchars($company_name, ENT_QUOTES, 'UTF-8'); ?></a></li>
            <li class="active">Reset Password</li>
        </ul>
        <!-- BEGIN SIDEBAR & CONTENT -->
        <div class="row margin-bottom-40">
          <!-- BEGIN SIDEBAR -->
          <div class="sidebar col-md-3 col-sm-3">
            <?php //include('inc/left-menu.php'); ?>
          </div>
          <!-- END SIDEBAR -->

          <!-- BEGIN CONTENT -->
          <div class="col-md-9 col-sm-7">
            <div class="col-md-6 col-sm-6">
                <?php if ($show_msg): ?>
                    <div class="alert alert-<?php echo $is_error ? 'danger' : 'success'; ?>">
                        <strong><?php echo $is_error ? 'Error!' : 'Success!'; ?></strong> <?php echo htmlspecialchars($show_msg, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                <?php endif; ?>
                <h3>Your Password</h3>
                <form class="form-horizontal form-without-legend" name="change_password" id="change_password" role="form" action="" method="post">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token, ENT_QUOTES, 'UTF-8'); ?>" />
                    <div class="form-group <?php if (!empty($error['password_new'])) echo 'has-error'; ?>">
                        <label for="password_new">New Password <span class="require">*</span></label>
                        <input type="password" id="password_new" name="password_new" class="form-control validate[required,minSize[8],maxSize[30]]">
                        <?php if (!empty($error['password_new'])) echo '<span class="help-block">' . htmlspecialchars($error['password_new'], ENT_QUOTES, 'UTF-8') . '</span>'; ?>
                    </div>
                    <div class="form-group <?php if (!empty($error['password_confirm'])) echo 'has-error'; ?>">
                        <label for="password_confirm">New Password Confirm <span class="require">*</span></label>
                        <input type="password" id="password_confirm" name="password_confirm" class="form-control validate[required,equals[password_new]]">
                        <?php if (!empty($error['password_confirm'])) echo '<span class="help-block">' . htmlspecialchars($error['password_confirm'], ENT_QUOTES, 'UTF-8') . '</span>'; ?>
                    </div>
                    <div class="form-group">
                        <input type="submit" class="btn btn-primary  pull-right" name="change_pass" value=" Change Password " />
                    </div>
                </form>
            </div>
        </div>
          <!-- END CONTENT -->
        </div>
        <!-- END SIDEBAR & CONTENT -->
      </div>
    </div>


    <!-- BEGIN PRE-FOOTER -->
    <?php include('inc/footer.php'); ?>
    <!-- END PRE-FOOTER -->


    <!-- Load javascripts at bottom, this will reduce page load time -->
    <!-- BEGIN CORE PLUGINS(REQUIRED FOR ALL PAGES) -->
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
    <script src="assets/global/plugins/carousel-owl-carousel/owl-carousel/owl.carousel.min.js" type="text/javascript"></script><!-- slider for products -->
    <script src="assets/global/plugins/uniform/jquery.uniform.min.js" type="text/javascript"></script>

    <script src="assets/frontend/layout/scripts/layout.js" type="text/javascript"></script>
    <script type="text/javascript">
        jQuery(document).ready(function() {
            Layout.init();    
            Layout.initOWL();
			Layout.initUniform();
            //Layout.initFixHeaderWithPreHeader(); /* Switch On Header Fixing (only if you have pre-header) */
        });
    </script>
    <!-- END PAGE LEVEL JAVASCRIPTS -->
    
    <!-- validation -->
    <script src="assets/frontend/layout/scripts/jquery.validationEngine-en.js" type="text/javascript"></script>
    <script src="assets/frontend/layout/scripts/jquery.validationEngine.js" type="text/javascript"></script>
    <script>
		jQuery(document).ready(function(){
			// binds form submission and fields to the validation engine
			jQuery("#change_password").validationEngine();
		});
	</script>
</body>
<!-- END BODY -->
</html>