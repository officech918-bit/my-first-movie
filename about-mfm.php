<?php
declare(strict_types=1);
/**
 * About MFM Page
 *
 * This file renders the 'About Us' page, providing information about the
 * My First Movie platform, its mission, and its vision. It is a static
 * content page.
 *
 * @package     MyFirstMovie
 * @subpackage  Pages
 * @since       1.0.0
 */

require_once 'inc/requires.php';

// The following variables are needed for the header and footer includes.

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$database = new MYSQLDB();
$user = new visitor(); // First, create a base visitor
if ($user->check_session()) { // Then, check the session
    $user = new web_user(); // If logged in, elevate to web_user
}


$sitename = $user->get_sitename();
$sub_location = $user->get_sub_location();
$company_name = $user->get_company_name();
?>

<!DOCTYPE html>
<html dir="ltr" lang="en-US">
<head>
<title>My First Movie | About</title>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />

<!-- Stylesheets
	============================================= -->
<link href="https://fonts.googleapis.com/css?family=Lato:300,400,400italic,600,700|Raleway:300,400,500,600,700|Crete+Round:400italic" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="css/bootstrap.css" type="text/css" />
<link rel="stylesheet" href="style.css" type="text/css" />
<link rel="stylesheet" href="css/dark.css" type="text/css" />
<link rel="stylesheet" href="css/font-icons.css" type="text/css" />
<link rel="stylesheet" href="css/animate.css" type="text/css" />
<link rel="stylesheet" href="css/magnific-popup.css" type="text/css" />
<link rel="stylesheet" href="css/responsive.css" type="text/css" />
<meta name="viewport" content="width=device-width, initial-scale=1" />

<!-- External JavaScripts
	============================================= -->
<script type="text/javascript" src="js/jquery.js" nonce="<?= $nonce ?>"></script>
<script type="text/javascript" src="js/plugins.js" nonce="<?= $nonce ?>"></script>

</head>

<body class="stretched">

<!-- Document Wrapper
	============================================= -->
<div id="wrapper" class="clearfix">
  <!-- Header
		============================================= -->
  <?php include('inc/header.php'); ?> 
  <!-- #header end -->
  
  <section id="content">
    <div class="content-wrap">
      <div class="container clearfix">
        <div class="postcontent">
          <div class="single-post nobottommargin">
            <div class="entry-header">
              <div class="entry-title">
                <h1>About My First Movie</h1>
              </div>
            </div>
            <div class="entry-content notopmargin">
              <p>Welcome to My First Movie - A unique platform for storytellers and filmmakers.</p>
              
              <h3>Our Mission</h3>
              <p>To provide a platform where common people with uncommon stories can share their narratives through film with the world.</p>
              
              <h3>What We Do</h3>
              <p>My First Movie is India's first contest designed specifically for individuals who have a story to tell but may not have professional filmmaking experience. We believe that compelling storytelling transcends technical expertise.</p>
              
              <h3>Our Vision</h3>
              <p>We envision a world where meaningful stories are not gatekept by the film industry, but are accessible to anyone with passion and a message to share.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
  
  <?php include('inc/footer.php'); ?>
</div>
<!-- #wrapper end -->

<!-- Go To Top
	============================================= -->
<div id="gotoTop" class="icon-angle-up"></div>

<!-- Footer Scripts
	============================================= -->
<script type="text/javascript" src="js/functions.js" nonce="<?= $nonce ?>"></script>
<script type="text/javascript" src="js/jquery.pulsate.js" nonce="<?= $nonce ?>"></script>

</body>
</html>