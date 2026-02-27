<?php declare(strict_types=1); 
/**
 * User Registration Page.
 * @package MFM
 * @subpackage Members
 */
require_once __DIR__ . '/inc/requires.php';
require_once __DIR__ . '/../classes/database.class.php';
require_once __DIR__ . '/../classes/Mailer.php'; // Include the Mailer class file

use App\Mailer; // Use the Mailer namespace

// Instantiate database connection
$database = new MySQLDB();
$db = $database->db;

// Instantiate Mailer
$mailer = new Mailer();

if ($user->check_session()) {
    header('Location: dashboard.php');
    exit;
}


// Initialize form variables to prevent undefined variable warnings  fixed by aditya
$first_name = trim($_POST['first_name'] ?? '');
$last_name = trim($_POST['last_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$contact = trim($_POST['contact'] ?? '');
$gender = trim($_POST['gender'] ?? '');
$password1 = $_POST['password1'] ?? '';
$password2 = $_POST['password2'] ?? '';
$i_agree = isset($_POST['i_agree']);
$is_error = false; // Initialize $is_error to false to prevent undefined variable warnings

// Generate CSRF token if not already set
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

$error = [
    'first_name' => '',
    'last_name' => '',
    'email' => '',
    'contact' => '',
    'gender' => '',
    'password1' => '',
    'password2' => '',
    'captcha' => '',
    'i_agree' => ''
];


$sitename = $user->get_sitename();
$sub_location = $user->get_sub_location();
$admin_location = $user->get_admin_location();
$from_email = $user->get_from_email();
$to_email = $user->get_to_email();
$company_name = $user->get_company_name();

$activation_link = '';


if (isset($_POST['register'])) {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die('CSRF token validation failed. Please try again.');
    }

		
	
        if (empty($first_name)) { $is_error = true; $error['first_name'] = 'First Name can not be empty'; }
        else if (mb_strlen($first_name) > 30) { $is_error = true; $error['first_name'] = 'First Name can not be more than 30 characters'; }

        if (empty($last_name)) { $is_error = true; $error['last_name'] = 'Last Name can not be empty'; }
        else if (mb_strlen($last_name) > 30) { $is_error = true; $error['last_name'] = 'Last Name can not be more than 30 characters'; }

        if (empty($gender)) { $is_error = true; $error['gender'] = 'Gender is required'; }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $is_error = true; $error['email'] = 'Invalid Email ID';
        } else {
            //check through database if already exist
            $query = 'SELECT email FROM web_users WHERE email = ? LIMIT 1';
            if ($stmt = $db->prepare($query)) {
                $stmt->execute([$email]);
                if ($stmt->rowCount() > 0) {
                    $is_error = true;
                    $error['email'] = 'User with this mail ID already exists';
                }
            }
        }

        if (empty($password1)) {
            $is_error = true; $error['password1'] = 'Please enter your password';
        } else if (mb_strlen($password1) < 6) {
            $is_error = true; $error['password1'] = 'Password length must be equal or more than 6 characters';
        } else if (mb_strlen($password1) > 30) { $is_error = true; $error['password1'] = 'Password can not be more than 30 characters'; }
        if ($password1 !== $password2) { $is_error = true; $error['password2'] = 'Password does not match';}
		if (!$i_agree) { $is_error = true; $error['i_agree'] = 'Please click the above check box.'; }
		
		if(!$is_error) {
			
			$hash = password_hash($password1, PASSWORD_DEFAULT);
			$ip = $user->getRealIPAddr();
			$status = 1; // Use 1 for true
			$tnc_agreed_val = $i_agree ? 1 : 0; // Use 1 for true, 0 for false
			
			$query = 'INSERT INTO web_users(first_name, last_name, email, contact, gender, hash_code, tnc_agreed, ip, create_date, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
			
			if ($stmt = $db->prepare($query)) {
                $date = date('Y-m-d H:i:s');
				$stmt->execute([$first_name, $last_name, $email, $contact, $gender, $hash, $tnc_agreed_val, $ip, $date, $status]);
				$uid = $db->lastInsertId();
			} else {
				die('Unable to register data: ' . $db->errorInfo()[2]);
			}
			
			if($uid) { 
				
				
				// activation code (always write to DB; do NOT depend on mail() working)
				$activation_status = 0;
				$activation_code = rand(100000, 999999);
				$activation_expire_time = date('Y-m-d H:i:s', time()+3600*24);
                $activation_link = $sitename . '/members/activate.php?email=' . urlencode($email) . '&code=' . $activation_code;


				$query2 = "UPDATE web_users SET activation_code=?, activation_expire_time=?, activation_status=? WHERE uid=?";
				if ($stmt2 = $db->prepare($query2)) {
					$stmt2->execute([$activation_code, $activation_expire_time, $activation_status, $uid]);
				}
				
				$is_local = in_array($_SERVER['SERVER_NAME'], ['localhost', '127.00.1'], true) || strpos($_SERVER['HTTP_HOST'], 'localhost') !== false;

                $to_email_address = $email;
                $email_subject = $company_name . " - Account Activation";

                $template_data = [
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'activation_code' => $activation_code,
                    'activation_link' => $activation_link,
                    'company_name' => $company_name,
                    'subject' => $email_subject // Pass subject for potential use in template error message
                ];

                $email_body = $mailer->renderTemplate('activation_code', $template_data);
                $isSent = $mailer->send($to_email_address, $email_subject, $email_body);

				if (!$isSent && $is_local) {
					// Log email sending failure for debugging in local environment
					error_log("Activation email failed to send to {$to_email_address} in local environment.");
				}
				
				$user->create_session($uid, $email, 'user');
				
				
				header('Location: activate.php');
				exit();
				
				
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
<title>Create new account |<?php echo htmlspecialchars($company_name, ENT_QUOTES, 'UTF-8'); ?></title>
<meta content="width=device-width, initial-scale=1.0" name="viewport">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<link rel="shortcut icon" href="../favicon.ico">

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
<script src='https://www.google.com/recaptcha/api.js'></script>
<style>
.help-block {
	color: #F00;
}
</style>
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
      <li><a href="../index.php"><?php echo $company_name ?></a></li>
      <li class="active">Create new account</li>
    </ul>
    <!-- BEGIN SIDEBAR & CONTENT -->
    <div class="row margin-bottom-40"> 
      <!-- BEGIN CONTENT -->
      <div class="col-md-12 col-sm-12">
        <h1>Create an account</h1>
        <div class="content-form-page">
          <div class="row">
            <div class="col-md-6 col-sm-6">
              <form class="form-horizontal col-md-offset-1 padding-left-0" role="form" method="post" action="" name="register" id="register">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <fieldset>
                  <legend>Your personal details</legend>
                  <div class="form-group <?php if(($is_error) && $error['first_name'] != '') echo 'has-error' ?>">
                    <label for="firstname" class="col-lg-4 control-label">First Name <span class="require">*</span></label>
                    <div class="col-lg-8">
                      <input type="text" class="form-control validate[required,,maxSize[30]]" id="first_name" name="first_name" value="<?php echo $first_name ?>">
                      <?php if(($is_error) && $error['first_name'] != '') echo '<span class="help-block">'.$error['first_name'].'</span>' ?>
                    </div>
                  </div>
                  <div class="form-group <?php if(($is_error) && $error['last_name'] != '') echo 'has-error' ?>">
                    <label for="lastname" class="col-lg-4 control-label">Last Name <span class="require">*</span></label>
                    <div class="col-lg-8">
                      <input type="text" class="form-control validate[required,maxSize[30]]" id="last_name" name="last_name" value="<?php echo $last_name ?>">
                      <?php if(($is_error) && $error['last_name'] != '') echo '<span class="help-block">'.$error['last_name'].'</span>' ?>
                    </div>
                  </div>
                  <div class="form-group <?php if(($is_error) && $error['email'] != '') echo 'has-error' ?>">
                    <label for="email" class="col-lg-4 control-label">Email <span class="require">*</span></label>
                    <div class="col-lg-8">
                      <input type="text" class="form-control validate[required,custom[email]]" id="email" name="email" value="<?php echo $email ?>">
                      <?php if(($is_error) && $error['email'] != '') echo '<span class="help-block">'.$error['email'].'</span>' ?>
                    </div>
                  </div>
                  <div class="form-group <?php if(($is_error) && $error['contact'] != '') echo 'has-error' ?>">
                    <label for="contact" class="col-lg-4 control-label">Mobile <span class="require">*</span></label>
                    <div class="col-lg-8">
                      <input type="text" class="form-control validate[required,custom[integer], minSize[10], maxSize[12]]" id="contact" name="contact" value="<?php echo $contact ?>">
                      <?php if(($is_error) && $error['contact'] != '') echo '<span class="help-block">'.$error['contact'].'</span>' ?>
                    </div>
                  </div>
                  <div class="form-group <?php if(($is_error) && $error['gender'] != '') echo 'has-error' ?>">
                    <label for="gender" class="col-lg-4 control-label">Gender <span class="require">*</span></label>
                    <div class="col-lg-8">
                      <select class="form-control validate[required]" id="gender" name="gender">
                        <option value="">-- Select Gender --</option>
                        <option value="Male" <?php if($gender == 'Male') echo 'selected'; ?>>Male</option>
                        <option value="Female" <?php if($gender == 'Female') echo 'selected'; ?>>Female</option>
                        <option value="Other" <?php if($gender == 'Other') echo 'selected'; ?>>Other</option>
                      </select>
                      <?php if(($is_error) && $error['gender'] != '') echo '<span class="help-block">'.$error['gender'].'</span>' ?>
                    </div>
                  </div>
                  <div class="form-group <?php if(($is_error) && $error['password1'] != '') echo 'has-error' ?>">
                    <label for="password" class="col-lg-4 control-label">Password <span class="require">*</span></label>
                    <div class="col-lg-8">
                      <input type="password" class="form-control validate[required,minSize[6],maxSize[30]]" id="password1" name="password1">
                      <?php if(($is_error) && $error['password1'] != '') echo '<span class="help-block">'.$error['password1'].'</span>' ?>
                    </div>
                  </div>
                  <div class="form-group <?php if(($is_error) && $error['password2'] != '') echo 'has-error' ?>">
                    <label for="confirm-password" class="col-lg-4 control-label">Confirm password <span class="require">*</span></label>
                    <div class="col-lg-8">
                      <input type="password" class="form-control validate[required,equals[password1]]" id="password2" name="password2">
                      <?php if(($is_error) && $error['password2'] != '') echo '<span class="help-block">'.$error['password2'].'</span>' ?>
                    </div>
                  </div>
                  
                  <div class="form-group checkbox <?php if(($is_error) && $error['i_agree'] != '') echo 'has-error' ?>">
                    <label class="col-lg-4 control-label"></label>
                    <div class="col-lg-8">
                      <div class="g-recaptcha" data-sitekey="6LcsAZUUAAAAAOe3nSz7zQJv2JKmEgLN4gutkncZ"></div>
                      <?php if(($is_error) && $error['captcha'] != '') echo '<span class="help-block">'.$error['captcha'].'</span>' ?>
                    </div>
                  </div>
                  <div class="form-group checkbox <?php if(($is_error) && $error['i_agree'] != '') echo 'has-error' ?>" style="margin-top:10px;">
                    <label class="col-lg-4 control-label"></label>
                    <div class="col-lg-8">
                      <label>
                        <input type="checkbox" class="validate[required]" name="i_agree" id="i_agree">
                        I Agree to <a href="<?php echo $path ?>terms-and-conditions" target="_blank">Terms &amp; Conditions</a> of  <?php echo $company_name  ?></label>
                      <?php if(($is_error) && $error['i_agree'] != '') echo '<span class="help-block">'.$error['i_agree'].'</span>' ?>
                    </div>
                  </div>
                </fieldset>
                <div class="row">
                  <div class="col-lg-8 col-md-offset-4 padding-left-0 padding-top-20">
                    <button type="reset" name="register" class="btn btn-default">Reset</button>
                    <button type="submit" name="register" class="btn btn-primary">Create an account</button>
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
            <div class="col-md-6 col-sm-6"> </div>
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
<script src="assets/frontend/plugins/jquery.min.js" type="text/javascript"></script> 
<script src="assets/frontend/plugins/jquery-migrate.min.js" type="text/javascript"></script> 
<script src="assets/frontend/plugins/bootstrap/js/bootstrap.min.js" type="text/javascript"></script> 
<script src="assets/frontend/layout/scripts/back-to-top.js" type="text/javascript"></script> 
<script src="assets/global/plugins/jquery-slimscroll/jquery.slimscroll.min.js" type="text/javascript"></script> 
<!-- END CORE PLUGINS --> 

<!-- BEGIN PAGE LEVEL JAVASCRIPTS (REQUIRED ONLY FOR CURRENT PAGE) --> 
<script src="assets/global/plugins/fancybox/source/jquery.fancybox.pack.js" type="text/javascript"></script><!-- pop up --> 
<script src="assets/global/plugins/uniform/jquery.uniform.min.js" type="text/javascript"></script> 
<script src="assets/frontend/layout/scripts/layout.js" type="text/javascript"></script> 
<script type="text/javascript" nonce="<?php echo $nonce; ?>">
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
<script nonce="<?php echo $nonce; ?>">
		jQuery(document).ready(function(){
			// binds form submission and fields to the validation engine
			jQuery("#register").validationEngine();
		});
	</script>
</body>
<!-- END BODY -->
</html>