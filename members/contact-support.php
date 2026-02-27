<?php
/**
 * Member Contact Support Page.
 *
 * @package MFM
 * @subpackage Members
 */
declare(strict_types=1);

require_once 'inc/requires.php';

$is_error = false;
$error = [];
$echo_message = '';

if (!$user->check_session()) {
    header('Location: index.php');
    exit();
}

if (!$user->isActive()) {
    header('Location: activate.php');
    exit();
}

$company_name = $user->get_company_name();
$from_email = $user->get_from_email();
$to_email = $user->get_to_email();

// Pre-fill form with user data
$name = $user->get('first_name') . ' ' . $user->get('last_name');
$email = $user->get('email');
$contact = $user->get('contact');
$message = '';

if (isset($_POST['submit_support'])) {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $is_error = true;
        $echo_message = 'A security error occurred. Please try again.';
    } else {
        // Get and sanitize form data
        $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
        $contact = filter_input(INPUT_POST, 'contact', FILTER_SANITIZE_STRING);
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING);

        // Validate form data
        if (empty($name)) {
            $is_error = true;
            $error['name'] = 'Name cannot be empty.';
        }
        if (empty($contact)) {
            $is_error = true;
            $error['contact'] = 'Contact number cannot be empty.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $is_error = true;
            $error['email'] = 'Invalid email address.';
        }
        if (empty($message)) {
            $is_error = true;
            $error['message'] = 'Message cannot be empty.';
        }

        // Process if no error found
        if (!$is_error) {
            $subject = "Support Request from " . $name;
            $headers = "From: " . $from_email . "\r\n";
            $headers .= "Reply-To: " . $email . "\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

            $mail_message = '<html><body>';
            $mail_message .= '<h3>Support Request Details</h3>';
            $mail_message .= '<p><strong>Name:</strong> ' . htmlspecialchars($name) . '</p>';
            $mail_message .= '<p><strong>Email:</strong> ' . htmlspecialchars($email) . '</p>';
            $mail_message .= '<p><strong>Contact:</strong> ' . htmlspecialchars($contact) . '</p>';
            $mail_message .= '<p><strong>Message:</strong><br>' . nl2br(htmlspecialchars($message)) . '</p>';
            $mail_message .= '</body></html>';

            if (mail($to_email, $subject, $mail_message, $headers)) {
                $echo_message = 'We have received your support request and will get back to you shortly.';
                // Clear form fields after successful submission
                $name = $user->get('first_name') . ' ' . $user->get('last_name');
                $email = $user->get('email');
                $contact = $user->get('contact');
                $message = '';
            } else {
                $is_error = true;
                $echo_message = 'There was an error sending your message. Please try again later.';
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
  <title>Contact Support | <?= e($company_name) ?></title>
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
        <h1>Contact Support</h1>
      </div>
      <ul class="page-breadcrumb breadcrumb pull-right">
        <li><a href="dashboard.php"><?= e($company_name) ?></a></li>
      <li class="active">Contact Support</li>
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
								<span class="caption-subject bold uppercase"> Contact Our Support Team</span>
								<span class="caption-helper">get instant help...</span>
                     </div>
                    <?php include 'inc/top-menu.php'; ?>
                    
                  </div>
						
						<div class="portlet-body" style="padding:20px;">
                        <?php if ($echo_message) : ?>
                            <div class="note note-<?= $is_error ? 'danger' : 'success' ?>">
                                <h4 class="block"><?= $is_error ? 'Error' : 'Request Submitted' ?></h4>
                                <p><?= e($echo_message) ?></p>
                            </div>
                        <?php endif; ?>
							<p>Please share your feedback, enquiry or concerns with us. One of our respective experts will get you back with possible assistance. We always welcome initiatives from you!</p>
                  
                  <!-- BEGIN FORM-->
                  <form action="" method="post" id="contact_support" name="contact_support" role="form">
                    <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                    <div class="form-group <?= isset($error['name']) ? 'has-error' : '' ?>">
                      <label for="contacts-name">Your Name:</label>
                      <input type="text" name="name" class="form-control validate[required]" value="<?= e($name) ?>" />
                      <?php if (isset($error['name'])) : ?><span class="help-block"><?= e($error['name']) ?></span><?php endif; ?>
                    </div>
                    <div class="form-group <?= isset($error['email']) ? 'has-error' : '' ?>">
                      <label for="contacts-email">Your Email:</label>
                      <input type="email" name="email" class="form-control validate[required,custom[email]]" value="<?= e($email) ?>" />
                      <?php if (isset($error['email'])) : ?><span class="help-block"><?= e($error['email']) ?></span><?php endif; ?>
                    </div>
                    <div class="form-group <?= isset($error['contact']) ? 'has-error' : '' ?>">
                      <label for="contacts-no">Contact No.:</label>
                      <input type="text" name="contact" class="form-control validate[required,custom[onlyNumber],minSize[10],maxSize[10]]" value="<?= e($contact) ?>" />
                      <?php if (isset($error['contact'])) : ?><span class="help-block"><?= e($error['contact']) ?></span><?php endif; ?>
                    </div>
                    <div class="form-group <?= isset($error['message']) ? 'has-error' : '' ?>">
                      <label for="contacts-message">Message:</label>
                      <textarea class="form-control validate[required]" name="message" rows="4" ><?= e($message) ?></textarea>
                      <?php if (isset($error['message'])) : ?><span class="help-block"><?= e($error['message']) ?></span><?php endif; ?>
                    </div>
                    <button type="submit" name="submit_support" class="btn btn-primary"><i class="icon-ok"></i> Send</button>
                    <button type="reset" class="btn btn-default">Cancel</button>
                  </form>
                  <!-- END FORM-->
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

    <script src="assets/frontend/layout/scripts/layout.js" type="text/javascript"></script>
    <script type="text/javascript">
        jQuery(document).ready(function() {
            Layout.init();    
            Layout.initOWL();
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
			jQuery("#contact_support").validationEngine();
		});
	</script>
</body>
<!-- END BODY -->
</html>