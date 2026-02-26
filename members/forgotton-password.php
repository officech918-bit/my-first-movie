<?php declare(strict_types=1);

use App\Mailer;

/**
 * Forgot Password Page.
 *
 * Allows users to request a password reset link.
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
$error = [];
$show_msg = '';
$user_email = '';

if ($user->check_session()) {
    header('Location: dashboard.php');
    exit;
}

$company_name = $user->get_company_name();
$from_email = $user->get_from_email();
$to_email = $user->get_to_email();

$mailer = new Mailer();

if (isset($_POST['reset_pass'])) {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $is_error = true;
        $show_msg = 'CSRF token validation failed. Please try again.';
    } else {
        $user_email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);

        if (!$user_email) {
            $is_error = true;
            $error['email'] = 'A valid email is required.';
        }

        if (!$is_error) {
            $query = 'SELECT uid, first_name, email FROM web_users WHERE email = ?';
            $stmt = $database->db->prepare($query);
            $stmt->execute([$user_email]);
            $resultArray = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$resultArray) {
                // To prevent user enumeration, we'll proceed as if the user exists,
                // but without sending an email. The final message will be the same.
                $show_msg = 'A Password Reset mail has been sent to your registered mail id.';
            } else {
                // $resultArray is already set from the fetch call above

                $uid = $resultArray['uid'];
                $first_name = $resultArray['first_name'];

                // Generate a cryptographically secure token.
                $token = bin2hex(random_bytes(32));
                $token_hash = hash('sha256', $token);
                $reset_expire_time = date('Y-m-d H:i:s', strtotime('+1 hour'));

                // The reset link should not expose the user's email.
                $reset_link = $members_path . 'reset-password.php?token=' . $token;

                $query1 = 'UPDATE web_users SET reset_req_id=?, reset_expire_time=? WHERE uid = ? AND email = ?';
                $stmt1 = $database->db->prepare($query1);

                if ($stmt1->execute([$token_hash, $reset_expire_time, $uid, $user_email])) { // If the database update is successful
                    $email_subject = "Password Reset Request - {$company_name}";
                    $template_data = [
                        'first_name' => $first_name,
                        'reset_link' => $reset_link,
                        'company_name' => $company_name,
                        'sitename' => $sitename,
                    ];
                    $email_body = $mailer->renderTemplate('password_reset', $template_data);

                    if ($mailer->send($user_email, $email_subject, $email_body)) {
                        $show_msg = 'A Password Reset mail has been sent to your registered mail id.';
                    } else {
                        $is_error = true;
                        $show_msg = 'Due to a technical error, the reset mail could not be sent. Please try again later.';
                    }
                } else {
                    // Do not expose database errors to the user.
                    error_log('Database error on password reset token update: ' . $database->db->error);
                    $is_error = true;
                    $show_msg = 'An unexpected error occurred. Please try again later.';
                }
            }
        }
    }
    // Set the success message here to ensure it's always shown, preventing user enumeration.
    if (!$is_error) {
        $show_msg = 'If an account with that email exists, a password reset link has been sent.';
    }
}
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
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
<title>Forgot Your Password? |<?php echo htmlspecialchars($company_name, ENT_QUOTES, 'UTF-8'); ?></title>
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
<body class="corporate">

<!-- BEGIN HEADER -->
<?php include('inc/header.php'); ?>
<!-- Header END -->

<div class="main">
  <div class="container">
    <ul class="breadcrumb">
      <li><a href="<?php echo htmlspecialchars($members_path, ENT_QUOTES, 'UTF-8'); ?>index.php"><?php echo htmlspecialchars($company_name, ENT_QUOTES, 'UTF-8'); ?></a></li>
      <li class="active">Forgot Your Password?</li>
    </ul>
    <!-- BEGIN SIDEBAR & CONTENT -->
    <div class="row margin-bottom-40"> 
      <!-- BEGIN SIDEBAR -->
      <div class="sidebar col-md-2 col-sm-2"> </div>
      <!-- END SIDEBAR --> 
      
      <!-- BEGIN CONTENT -->
      <div class="col-md-10 col-sm-10">
        <h1>Forgot Your Password?</h1>
        <p>Get a reset password now!</p>
        <div class="content-form-page">
          <div class="row">
            <div class="col-md-7 col-sm-7">
              <?php if ($show_msg !== '' && !$is_error) {
                  echo '<div class="alert alert-success"><strong>Success!</strong> ' . $show_msg . '</div>';
              } elseif ($show_msg !== '' && $is_error) {
                  echo '<div class="alert alert-danger"><strong>Error!</strong> ' . htmlspecialchars($show_msg, ENT_QUOTES, 'UTF-8') . '</div>';
              }
              ?>
              <form class="form-horizontal form-without-legend" role="form" name="reset_password" id="reset_password" method="post" action="">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                <div class="form-group <?php if (!empty($error['email'])) echo 'has-error' ?>">
                  <label for="email" class="col-lg-4 control-label">Email</label>
                  <div class="col-lg-8">
                    <input type="email" class="form-control validate[required,custom[email]]" id="email" name="email" value="<?php echo htmlspecialchars($user_email, ENT_QUOTES, 'UTF-8'); ?>">
                    <?php if (!empty($error['email'])) echo '<span class="help-block">' . htmlspecialchars($error['email'], ENT_QUOTES, 'UTF-8') . '</span>' ?>
                  </div>
                </div>
                <div class="row">
                  <div class="col-lg-8 col-md-offset-4 padding-left-0 padding-top-5">
                    <button type="submit" name="reset_pass" class="btn btn-primary">Reset My Password</button>
                  </div>
                </div>
              </form>
              <div class="row">
                <div class="col-lg-8 col-md-offset-4 padding-left-0 padding-top-10 padding-right-30">
                  <hr>
                  <div class="login-socio">
                    <h2>Already Registered! <a href="index.php" class="btn btn-default">Login Now</a></h2>
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
			jQuery("#reset_password").validationEngine();
		});
	</script>
</body>
<!-- END BODY -->
</html>