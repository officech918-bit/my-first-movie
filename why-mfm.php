<?php

/**
 * Why MFM Page
 *
 * This is a static informational page explaining the mission and value
 * proposition of MyFirstMovie.
 *
 * @package     MyFirstMovie
 * @subpackage  Pages
 * @since       1.0.0
 */

declare(strict_types=1);

require_once 'inc/requires.php';

$user = new visitor();
$database = new MySQLDB();
$company_name = $user->get_company_name();

if ($user->check_session()) {
    $user = new web_user();
}
?>

<!DOCTYPE html>
<html dir="ltr" lang="en-US">
<head>
<title>Why MFM | My First Movie</title>
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
<!--[if lt IE 9]>
		<script src="https://css3-mediaqueries-js.googlecode.com/svn/trunk/css3-mediaqueries.js"></script>
	<![endif]-->

<!-- External JavaScripts
	============================================= -->
<script type="text/javascript" src="js/jquery.js" nonce="<?= $nonce ?>"></script>
<script type="text/javascript" src="js/plugins.js"nonce="<?= $nonce ?>"></script>

<!-- Document Title
	============================================= -->
<?php include('inc/before_head_close.php'); ?> 
</head>

<body class="stretched">

<!-- Document Wrapper
	============================================= -->
<div id="wrapper" class="clearfix">
	<?php include('inc/after_body_start.php'); ?>  
  <!-- Top Bar
		============================================= --> 
  <!-- #top-bar end -->
  
  <?php include('inc/header.php'); ?>
  
		<section id="page-title">

			<div class="container clearfix">
				<h1>Why MFM</h1>
				<span>My First Movie</span>
				<ol class="breadcrumb">
					<li><a href="index.php">Home</a></li>
					<li class="active">Why MFM</li>
				</ol>
			</div>

		</section>
  
		<section id="content">

			<div class="content-wrap">

				<div class="container clearfix">

					<div class="col_full">

						<div class="heading-block center">
							<h2>Why MFM</h2>
							<span>It's an old saying "If your don't build your dreams, someone else will hire you to build their's". There is a rebel hiding inside us for a very long time, wanting to get out and tell stories, share experiences, give a message but just don't know how to do it. We at MFM are here to help you build your dreams. We are here to give you a platform to tell your stories and get recognized for it.</span>
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
<script type="text/javascript" src="js/functions.js"></script>
<?php include('inc/before_body_close.php'); ?>
</body>	
</html>