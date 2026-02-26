<?php
declare(strict_types=1);

use App\Mailer;

$show_msg = ''; // Initialize $show_msg to prevent undefined variable warnings
$is_error = false; // Initialize $is_error to prevent undefined variable warnings
/**
 * Member Change Password Page.
 *
 * @package MFM
 * @subpackage Members
 */

require_once __DIR__ . '/inc/requires.php';

if (!$user->check_session() || !$user->isActive()) {
    $_SESSION['activation_message'] = 'You need to activate your account to change your password.';
    header('Location: index.php');
    exit();
}

$show_msg = '';

$company_name = $user->get_company_name();
$mailer = new Mailer();

if (isset($_POST['change_pass'])) {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $is_error = true;
        $show_msg = 'A security error occurred. Please try again.';
    } else {
        // Get user data from session and database
        $user_id = (int)$_SESSION['uid'];
        $session_email = $_SESSION['email'];
        $first_name = $user->get('first_name');
        $db_hash_code = $user->get('hash_code');
        $db_salt = $user->get('salt');

        // Get form variables
        $current_password = $_POST['currentPassword'] ?? '';
        $password_1 = $_POST['password1'] ?? '';
        $password_2 = $_POST['password2'] ?? '';

        if (empty($current_password)) {
            $is_error = true;
            $error['currentPassword'] = 'Please enter your current password.';
        } else {
            $hash_code = $user->generateHash($current_password);
            if (!password_verify($current_password, $db_hash_code)) {
                $is_error = true;
                $error['currentPassword'] = 'Invalid current password!';
            }
        }

        if (empty($password_1)) {
            $is_error = true;
            $error['password1'] = 'Please enter your new password.';
        } elseif (strlen($password_1) < 6) {
            $is_error = true;
            $error['password1'] = 'Password length must be at least 6 characters.';
        } elseif (strlen($password_1) > 30) {
            $is_error = true;
            $error['password1'] = 'Password cannot be more than 30 characters.';
        }

        if ($password_1 !== $password_2) {
            $is_error = true;
            $error['password2'] = 'Passwords do not match.';
        }

        // Check if new and old passwords are the same
        if (!$is_error && !empty($password_1)) {
            $hash_code_new = $user->generateHash($password_1, $db_salt);
            if ($hash_code_new === $db_hash_code) {
                $is_error = true;
                $error['password1'] = 'Your new password cannot be the same as your old password.';
            }
        }

        // Process changes
        if (!$is_error) {
            $email_subject = "Password Changed at {$company_name}";
            $template_data = [
                'first_name' => $first_name,
                'company_name' => $company_name,
                'sitename' => $user->get_sitename(),
            ];
            $email_body = $mailer->renderTemplate('password_changed', $template_data);

            if ($mailer->send($session_email, $email_subject, $email_body)) {
                $stmt = $database->db->prepare("UPDATE web_users SET hash_code = ? WHERE uid = ?");
                if ($stmt->execute([$hash_code_new, $user_id])) {
                    $show_msg = 'You have changed your password successfully.';
                    // Optionally, you might want to log the user out or regenerate session for security
                } else {
                    $is_error = true;
                    $show_msg = 'Unable to update your password in the database. Please contact support.';
                }
            } else {
                $is_error = true;
                $show_msg = 'Unable to send a confirmation email. Please contact support.';
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
  <title>Change Password | <?= e($company_name) ?></title>
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
</head>
<!-- Head END -->

<!-- Body BEGIN -->
<body class="ecommerce">
    <!-- BEGIN HEADER -->
    <?php include 'inc/header.php'; ?>
    <!-- Header END -->
    
    <div class="main">
	<div class="page-head">
    <div class="container"> 
      <!-- BEGIN PAGE TITLE -->
      <div class="page-title">
        <h1>Change Password</h1>
      </div>
      <ul class="page-breadcrumb breadcrumb pull-right">
        <li><a href="dashboard.php"><?= e($company_name) ?></a></li>
      <li class="active">Change Password</li>
      </ul>
      <!-- END PAGE TITLE --> 
    </div>
  </div>
      <div class="container">
        
        <!-- BEGIN SIDEBAR & CONTENT -->
        <div class="row margin-bottom-40">
          <!-- BEGIN SIDEBAR -->
          <div class="sidebar col-md-3 col-sm-3">
            <?php include 'inc/left-menu.php'; ?>
          </div>
          <!-- END SIDEBAR -->

          <!-- BEGIN CONTENT -->
          <div class="col-md-9 col-sm-7 user_right_area">
          	<div class="portlet light bordered">
            	<div class="portlet-title tabbable-line">
                    <div class="caption font-green-sharp"> 
                    	<i class="icon-speech font-green-sharp"></i>
                    	<span class="caption-subject bold uppercase"> Change Your Passowrd</span>
								<span class="caption-helper">keep it to you only...</span>
                     </div>
                    <?php include 'inc/top-menu.php'; ?>
                    
                  </div>
						
						<div class="portlet-body" style="padding:20px;">
							<?php if ($show_msg && !$is_error) : ?>
                                <div class="alert alert-success"><strong>Success!</strong> <?= e($show_msg) ?></div>
                            <?php elseif ($show_msg && $is_error) : ?>
                                <div class="alert alert-danger"><strong>Error!</strong> <?= e($show_msg) ?></div>
                            <?php endif; ?>
                     <form class="form-horizontal form-without-legend" name="change_password" id="change_password" role="form" action="" method="post">
                      <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                      <div class="form-group <?= isset($error['currentPassword']) ? 'has-error' : '' ?>">
                        <label for="password">Current Password <span class="require">*</span></label>
                        <input type="password" id="currentPassword" name="currentPassword" class="form-control validate[required]">
                        <?php if (isset($error['currentPassword'])) : ?><span class="help-block"><?= e($error['currentPassword']) ?></span><?php endif; ?>
                      </div>
                      <div class="form-group <?= isset($error['password1']) ? 'has-error' : '' ?>">
                        <label for="password">New Password <span class="require">*</span></label>
                        <input type="password" id="password1" name="password1" class="form-control validate[required,minSize[6],maxSize[30]]">
                        <?php if (isset($error['password1'])) : ?><span class="help-block"><?= e($error['password1']) ?></span><?php endif; ?>
                      </div>
                      <div class="form-group <?= isset($error['password2']) ? 'has-error' : '' ?>">
                        <label for="password-confirm">New Password Confirm <span class="require">*</span></label>
                        <input type="password" id="password2" name="password2" class="form-control validate[required,equals[password1]]">
                        <?php if (isset($error['password2'])) : ?><span class="help-block"><?= e($error['password2']) ?></span><?php endif; ?>
                      </div>
                      <div class="form-group">
                      	<input type="submit" class="btn btn-primary  pull-right" name="change_pass" value=" Change Password " />
                     </div>
                    </form>
						</div>
					</div>
            
          </div>
          <!-- END CONTENT -->
        </div>
        <!-- END SIDEBAR & CONTENT -->
      </div>
    </div>


    <!-- BEGIN PRE-FOOTER -->
    <?php include 'inc/footer.php'; ?>
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
    <script src="assets/frontend/layout/scripts/change-password-validation.js" type="text/javascript"></script>
</body>
<!-- END BODY -->
</html>