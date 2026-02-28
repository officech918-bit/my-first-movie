<?php

/**
 * The Core Team Page
 *
 * This page displays the members of the core team, fetching their details
 * from the database and presenting them in a grid format.
 *
 * @package     MyFirstMovie
 * @subpackage  Pages
 * @since       1.0.0
 */

declare(strict_types=1);

require_once 'inc/requires.php';

// Load composer autoloader first (before anything else)
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

// Load environment variables from .env file
if (class_exists('Dotenv\Dotenv')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/');
    $dotenv->load();
}

// Load S3Uploader for environment detection
if (file_exists(__DIR__ . '/classes/S3Uploader.php')) {
    require_once __DIR__ . '/classes/S3Uploader.php';
    $s3Uploader = new S3Uploader();
}

/**
 * Get image URL based on environment
 */
function getImageUrl(string $imagePath, string $uploadPath = ''): string {
    if (empty($imagePath)) {
        return 'https://via.placeholder.com/400x300?text=No+Image+Available';
    }
    
    global $s3Uploader;
    if ($s3Uploader && $s3Uploader->isS3Enabled()) {
        // In production, assume S3 URLs are stored
        return $imagePath;
    } else {
        // In local development, convert relative paths to full URLs
        if (strpos($imagePath, 'http') === 0) {
            return $imagePath; // Already a full URL
        }
        global $correct_base_path;
        
        // Handle different upload paths (core_team, etc.)
        if (!empty($uploadPath) && strpos($imagePath, $uploadPath) !== 0) {
            return $correct_base_path . "/uploads/" . $uploadPath . "/" . $imagePath;
        }
        
        return $correct_base_path . "/" . $imagePath; // Convert to relative path
    }
}

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

$database = new MySQLDB();
$user = new visitor(); // First, create a base visitor
if ($user->check_session()) { // Then, check the session
    $user = new web_user(); // If logged in, elevate to web_user
}
$company_name = $user->get_company_name();

if ($user->check_session()) {
    $user = new web_user();
}



?>

<!DOCTYPE html>
<html dir="ltr" lang="en-US">
<head>
<title>The Core Team | My First Movie</title>
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
<script type="text/javascript" src="js/plugins.js" nonce="<?= $nonce ?>"></script>
<script type="text/javascript" src="js/functions.js" nonce="<?= $nonce ?>"></script> 
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
				<h1>The Core Team</h1>
				<span>My First Movie</span>
				<ol class="breadcrumb">
					<li><a href="index.php">Home</a></li>
					<li class="active">The Core Team</li>
				</ol>
			</div>

		</section>
  
		<section id="content">

			<div class="content-wrap">

				<div class="container clearfix">

					<div class="heading-block center">
						<h2>The Core Team</h2>
						<span>We define team as Together Everyone Achieves More. Our strength comes from the team and it takes a combined effort to execute a process as complex as movie making.</span>
					</div>

					<div class="row">
						<?php
						$stmt = $database->db->prepare("SELECT * FROM core_team WHERE status='1' ORDER BY display_order ASC");
						$stmt->execute();
						$team_members = $stmt->fetchAll(PDO::FETCH_ASSOC);

						if (count($team_members) > 0) {
							foreach ($team_members as $member) {
						?>
						<div class="col-md-4 col-sm-6 bottommargin">
							<div class="team">
								<div class="team-image">
									<?php
										if (!empty($member['image'])) {
											// Use the getImageUrl function for environment-aware URLs
											$imageUrl = getImageUrl($member['image'], 'core_team');
											echo lazy_image($imageUrl, htmlspecialchars($member['name'], ENT_QUOTES, 'UTF-8'));
										} else {
											// Use default image
											echo lazy_image($correct_base_path . "/images/others/1.jpg", htmlspecialchars($member['name'], ENT_QUOTES, 'UTF-8'));
										}
									?>
								</div>
								<div class="team-desc">
									<div class="team-title"><h4><?php echo htmlspecialchars($member['name'], ENT_QUOTES, 'UTF-8'); ?></h4></div>
									<div class="team-content">
										<p><?php echo htmlspecialchars($member['intro'], ENT_QUOTES, 'UTF-8'); ?></p>
									</div>
								</div>
							</div>
						</div>
						<?php
							}
						} else {
							echo '<div class="col_full center"><p>No team members found.</p></div>';
						}
						?>
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
<script type="text/javascript" src="js/jquery.js"></script>
<script type="text/javascript" src="js/plugins.js"></script>
<script type="text/javascript" src="js/functions.js" nonce="<?= $nonce ?>"></script>
<?php include('inc/before_body_close.php'); ?>
</body>
</html>