<?php declare(strict_types=1);

/**
 * Dashboard Page.
 *
 * @author   closemarketing
 * @license  https://www.closemarketing.com/
 * @version  1.0.0
 * @since    1.0.0
 * @package  MFM
 * @subpackage Members
 */

require_once __DIR__ . '/inc/requires.php';

// Load environment variables from .env file
if (class_exists('Dotenv\Dotenv')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
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

// Get members path dynamically for CSS/JS loading
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

if (!$user->check_session()) {
    header('Location: index.php');
    exit();
}
if (!$user->isActive()) {
    $_SESSION['activation_message'] = 'You need to activate your account to access the dashboard.';
    header('Location: activate.php');
    exit();
}

$company_name = $user->get_company_name();
$uid = (int)$_SESSION['uid'];

// Get total number of categories
$stmt = $database->db->prepare('SELECT COUNT(*) as total FROM categories');
$stmt->execute();
$total_categories = $stmt->fetchColumn() ?? 0;

// Get user's enrollments
$stmt = $database->db->prepare('SELECT title FROM enrollments WHERE uid = ?');
$stmt->execute([$uid]);
$user_enrollments = $stmt->fetchAll(PDO::FETCH_COLUMN);
$user_enrollment_count = count($user_enrollments);

// Get categories the user can still apply for
$stmt = $database->db->prepare('SELECT title FROM categories WHERE status=\'1\' AND title NOT IN (SELECT title FROM enrollments WHERE uid = ?)');
$stmt->execute([$uid]);
$available_categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
$available_category_count = count($available_categories);

$msg = "You have enrolled for <b>{$user_enrollment_count}</b> enrollment" . ($user_enrollment_count !== 1 ? 's' : '') . '.';
if ($user_enrollment_count === 0) {
    $msg = 'You have not enrolled in any categories yet. Click the <b>"CURRENT ENROLLMENTS"</b> tab to get started.';
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
<title>Dashboard | <?php echo e($company_name); ?></title>
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
<link href="assets/global/plugins/uniform/css/uniform.default.css" rel="stylesheet" type="text/css">
<link href="https://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css" rel="stylesheet" type="text/css">
<!-- Page level plugin styles END -->

<!-- Theme styles START -->
<link href="assets/global/css/components.css" rel="stylesheet">
<link href="assets/frontend/layout/css/style.css" rel="stylesheet">
<link href="assets/frontend/pages/css/style-shop.css" rel="stylesheet" type="text/css">
<link href="assets/frontend/layout/css/style-responsive.css" rel="stylesheet">
<link href="assets/frontend/layout/css/themes/red.css" rel="stylesheet" id="style-color">
<link href="assets/frontend/layout/css/custom.css" rel="stylesheet">
<!-- Theme styles END -->

<link href="assets/global/plugins/bootstrap-toastr/toastr.min.css" rel="stylesheet" type="text/css"/>
<?php include('inc/pre-body.php'); ?>
</head>
<!-- Head END -->

<!-- Body BEGIN -->
<body class="ecommerce">
<!-- BEGIN HEADER -->
<?php include('inc/header.php'); ?>
<!-- Header END -->

<div class="main">
<div class="page-head">
    <div class="container"> 
      <!-- BEGIN PAGE TITLE -->
      <div class="page-title">
        <h1>Dashboard</h1>
      </div>
      <ul class="page-breadcrumb breadcrumb pull-right">
        <li><a href="dashboard.php"><?php echo e($company_name); ?></a></li>
      <li class="active">My Dashboard</li>
      </ul>
      <!-- END PAGE TITLE --> 
    </div>
  </div>
  <div class="container">
    <!-- BEGIN SIDEBAR & CONTENT -->
    <div class="row margin-bottom-40"> 
      <!-- BEGIN SIDEBAR -->
      <div class="sidebar col-md-3 col-sm-3">
        <?php include('inc/left-menu.php'); ?>
      </div>
      <!-- END SIDEBAR --> 
      
      <!-- BEGIN CONTENT -->
      <div class="col-md-9 col-sm-8" style="min-height:350px;"> 
        <!-- BEGIN PRODUCT LIST -->
        <div class="portlet light bordered">
            	<div class="portlet-title tabbable-line">
                    <div class="caption font-green-sharp"> 
                    	<i class="icon-speech font-green-sharp"></i>
                    	<span class="caption-subject bold uppercase"> Dashboard</span>
								<span class="caption-helper">Welcome to MFM...</span>
                     </div>
                    <?php include('inc/top-menu.php'); ?>
                    
                  </div>
						
						<div class="portlet-body" style="padding:20px;">
    <div class="test_1">
        <p>Total Number of Enrollments: <b><?php echo e((string)$total_categories); ?></b></p>
        <p><?php echo $msg; ?></p>
        <p>&nbsp;</p>

        <?php if ($user_enrollment_count > 0) : ?>
            <p><b>Categories you have applied for:</b></p>
            <ol>
                <?php foreach ($user_enrollments as $enrollment) : ?>
                    <li><?php echo e($enrollment); ?></li>
                <?php endforeach; ?>
            </ol>
        <?php endif; ?>

        <p>&nbsp;</p>

        <?php if ($available_category_count > 0) : ?>
            <p><b>Categories you can still apply for:</b></p>
            <ol>
                <?php foreach ($available_categories as $category) : ?>
                    <li><?php echo e($category); ?></li>
                <?php endforeach; ?>
            </ol>
        <?php endif; ?>
    </div>
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
<script src="assets/global/plugins/bootstrap-toastr/toastr.min.js"></script> 
<script src="assets/admin/pages/scripts/ui-toastr.js"></script> 
<script src="assets/frontend/layout/scripts/layout.js" type="text/javascript"></script> 
<script type="text/javascript">
        jQuery(document).ready(function() {
            Layout.init();    
            Layout.initOWL();
			Layout.initUniform();
            Layout.initFixHeaderWithPreHeader(); /* Switch On Header Fixing (only if you have pre-header) */
        });
    </script> 
<!-- END PAGE LEVEL JAVASCRIPTS -->

</body>
<!-- END BODY -->
</html>