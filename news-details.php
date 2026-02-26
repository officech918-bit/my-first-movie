<?php 
session_start();
date_default_timezone_set('Asia/Kolkata');
$date = date("Y-m-d H:i:s", time());
$time = date("d-m-Y H:i", time());

//get class files
include('inc/requires.php');

// Load environment variables from .env file
if (class_exists('Dotenv\Dotenv')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/');
    $dotenv->load();
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

//create objects
$database = new MySQLDB();
$user = new visitor();
$is_error = false;
	
if($user->check_session())
{	
	$user = new web_user();
} 

$sitename = $user->get_sitename();
$sub_location = $user->get_sub_location();
$admin_location = $user->get_admin_location();

$from_email = $user->get_from_email();
$to_email = $user->get_to_email();
$company_name = $user->get_company_name();

$path = "";
$direct_path = "";
if($sub_location != ""){
	$path = $sitename.'/'.$sub_location.'/';
	$direct_path = $_SERVER['DOCUMENT_ROOT'].'/'.$sub_location.'/';		
}
else {
	$path = $sitename.'/';
	$direct_path = $_SERVER['DOCUMENT_ROOT'].'/';
}

$news_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$news_id) {
    header('Location: index.php');
    exit;
}

try {
    $stmt = $database->db->prepare("SELECT * FROM industry_news WHERE id = :id");
    $stmt->execute(['id' => $news_id]);
    $news = $stmt->fetch();
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    header('Location: index.php');
    exit;
}

if (!$news) {
    header('Location: index.php');
    exit;
}

// Process image URL for S3/local support
if (!empty($news['image'])) {
    // Check if it's an S3 URL or local path
    $imagePath = $news['image'];
    if (strpos($imagePath, 'http') === 0) {
        // S3 URL or full URL
        $imageUrl = $imagePath;
    } else {
        // Local path - construct proper URL using correct base path
        $imageUrl = $correct_base_path . "/uploads/news/" . $imagePath;
    }
    $image = $imageUrl;
} else {
    // Use default image with correct base path
    $image = $correct_base_path . "/images/default.jpg";
}
$headline = htmlspecialchars($news['headline'], ENT_QUOTES, 'UTF-8');
$content = nl2br(htmlspecialchars($news['content'], ENT_QUOTES, 'UTF-8'));

?>

<!DOCTYPE html>
<html dir="ltr" lang="en-US">
<head>
<title><?php echo $headline; ?> | My First Movie</title>
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
<script type="text/javascript" src="js/jquery.js"></script>
<script type="text/javascript" src="js/plugins.js"></script>

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
				
				<ol class="breadcrumb">
					<li><a href="index.php">Home</a></li>
					<li><a href="index.php#industry-news">Industry News</a></li>
					<li class="active"><?php echo $headline; ?></li>
				</ol>
			</div>

		</section>
  
		<section id="content">

			<div class="content-wrap">

				<div class="container clearfix">
                    <h1><?php echo $headline; ?></h1>

					<div class="single-post nobottommargin">

						<!-- Single Post
						============================================= -->
						<div class="entry clearfix">

							<!-- Entry Image
							============================================= -->
							<div class="entry-image">
								<img src="<?php echo $image; ?>" alt="<?php echo $headline; ?>" style="max-width: 500px; display: block; margin: 0 auto; height: auto;">
							</div><!-- .entry-image end -->

							<!-- Entry Content
							============================================= -->
							<div style="border: 1px solid #e0e0e0; padding: 25px; border-radius: 8px; background-color: #f9f9f9; margin-top: 20px; overflow-wrap: break-word;">
    <?php echo $content; ?>
</div>
						</div><!-- .entry end -->

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