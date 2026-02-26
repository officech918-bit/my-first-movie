<?php
// Load Composer's autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Load environment variables from .env file
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

date_default_timezone_set('Asia/Kolkata');

//get class files
include('inc/requires.php');

/**
 * Fetches the row count for a given table in a secure and efficient way.
 *
 * @param PDO $db The PDO database connection object.
 * @param string $tableName The name of the table to count rows from.
 * @return int The number of rows in the table.
 */
function get_table_count(PDO $db, string $tableName): int {
    // A whitelist of table names adds a layer of security, preventing this function
    // from being used to query arbitrary tables.
    $allowed_tables = ['categories', 'web_users', 'regions', 'movies'];
    if (!in_array($tableName, $allowed_tables)) {
        return 0;
    }

    try {
        // Using query() is safe here as the table name is validated from a whitelist
        // and no user input is part of the query. fetchColumn() is highly efficient
        // for retrieving a single value from a query.
        $stmt = $db->query("SELECT COUNT(*) FROM `{$tableName}`");
        return (int) $stmt->fetchColumn();
    } catch (PDOException $e) {
        // In a production environment, it's good practice to log database errors.
        // For example: error_log("Count query failed for table {$tableName}: " . $e->getMessage());
        return 0; // Return 0 on failure to avoid breaking the dashboard display.
    }
}
	
	//create objects
	$database = new MySQLDB();
	$user = new visitor();

	// Rigorous session and role check
	if(!$user->check_session() || !isset($_SESSION['user_type']) || ($_SESSION['user_type'] !== 'admin' && $_SESSION['user_type'] !== 'webmaster'))
	{	
		header("location: index.php"); 
		exit();
	}
	
	$date = date('d-m-Y');
	$time = date("d-m-Y H:i", time());

	// Set the correct menu based on user type
	
	if ($_SESSION['user_type'] == 'webmaster'){
		$menu = 'inc/left-menu-webmaster.php';		
	} else {
		$menu = 'inc/left-menu-admin.php';
	}
	
	$path = "";
	$direct_path = "";
    $sitename = $user->get_company_name();
    $sub_location = "";
	if($sub_location != ""){
		$path = $sitename.'/'.$sub_location.'/';
		$direct_path = $_SERVER['DOCUMENT_ROOT'].'/'.$sub_location.'/';		
	}
	else {
		$path = $sitename.'/';
		$direct_path = $_SERVER['DOCUMENT_ROOT'].'/';
	}

?>
<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8 no-js"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9 no-js"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en" class="no-js">
<!--<![endif]-->
<!-- BEGIN HEAD -->
<head>
<meta charset="utf-8"/>
<title>Dashboard</title>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta content="width=device-width, initial-scale=1" name="viewport"/>
<meta content="" name="description"/>
<meta content="" name="author"/>
<!-- BEGIN GLOBAL MANDATORY STYLES -->
<link href="http://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700&subset=all" rel="stylesheet" type="text/css"/>
<link href="assets/global/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css"/>
<link href="assets/global/plugins/simple-line-icons/simple-line-icons.min.css" rel="stylesheet" type="text/css"/>
<link href="assets/global/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
<link href="assets/global/plugins/uniform/css/uniform.default.css" rel="stylesheet" type="text/css"/>
<link href="assets/global/plugins/bootstrap-switch/css/bootstrap-switch.min.css" rel="stylesheet" type="text/css"/>
<!-- END GLOBAL MANDATORY STYLES -->
<!-- BEGIN PAGE LEVEL PLUGIN STYLES -->
<link href="assets/global/plugins/bootstrap-daterangepicker/daterangepicker-bs3.css" rel="stylesheet" type="text/css"/>
<link href="assets/global/plugins/fullcalendar/fullcalendar.min.css" rel="stylesheet" type="text/css"/>
<link href="assets/global/plugins/jqvmap/jqvmap/jqvmap.css" rel="stylesheet" type="text/css"/>
<!-- END PAGE LEVEL PLUGIN STYLES -->
<!-- BEGIN PAGE STYLES -->
<link href="assets/admin/pages/css/tasks.css" rel="stylesheet" type="text/css"/>
<!-- END PAGE STYLES -->
<!-- BEGIN THEME STYLES -->
<!-- DOC: To use 'rounded corners' style just load 'components-rounded.css' stylesheet instead of 'components.css' in the below style tag -->
<link href="assets/global/css/components.css" id="style_components" rel="stylesheet" type="text/css"/>
<link href="assets/global/css/plugins.css" rel="stylesheet" type="text/css"/>
<link href="assets/admin/layout/css/layout.css" rel="stylesheet" type="text/css"/>
<link href="assets/admin/layout/css/themes/darkblue.css" rel="stylesheet" type="text/css" id="style_color"/>
<link href="assets/admin/layout/css/custom.css" rel="stylesheet" type="text/css"/>
<!-- END THEME STYLES -->
<link rel="shortcut icon" href="favicon.ico"/>
</head>
<!-- END HEAD -->
<!-- BEGIN BODY -->
<body class="page-header-fixed page-quick-sidebar-over-content page-style-square"> 
<!-- BEGIN HEADER -->
<?php include('inc/header.php'); ?>
<!-- END HEADER -->
<div class="clearfix">
</div>
<!-- BEGIN CONTAINER -->
<div class="page-container">
	<!-- BEGIN SIDEBAR -->
	<div class="page-sidebar-wrapper">
		<div class="page-sidebar navbar-collapse collapse">
			<!-- BEGIN SIDEBAR MENU -->
			<?php include($menu); ?>
			<!-- END SIDEBAR MENU -->
		</div>
	</div>
	<!-- END SIDEBAR -->
	<!-- BEGIN CONTENT -->
	<div class="page-content-wrapper">
		<div class="page-content">
			<!-- BEGIN PAGE HEADER-->
			<h3 class="page-title">
			Dashboard <small>My First Movie</small>
			</h3>
			<div class="page-bar">
				<ul class="page-breadcrumb">
					<li>
						<i class="fa fa-home"></i>
						<a href="index.php">Home</a>
						<i class="fa fa-angle-right"></i>
					</li>
					<li>
						<a href="#">Dashboard</a>
					</li>
				</ul>
				
			</div>
			<!-- END PAGE HEADER-->
            <?php 
            if (isset($_SESSION['msg1'])) {
                // Display the message, escaping it with htmlspecialchars to prevent XSS attacks.
                echo '<div class="alert alert-success"><strong>Sorry!</strong> ' . htmlspecialchars($_SESSION['msg1'], ENT_QUOTES, 'UTF-8') . '</div>';
                // Unset the message so it doesn't show again on subsequent page loads (flash message pattern).
                unset($_SESSION['msg1']);
            }
            ?>
			<!-- BEGIN DASHBOARD STATS -->
			<div class="row">
				<div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
					<div class="dashboard-stat blue-madison">
						<div class="visual">
							<i class="fa fa-comments"></i>
						</div>
						<div class="details">
							<div class="number">
                            	<?= get_table_count($database->db, 'categories') ?>
							</div>
							<div class="desc">
								 Total Categories
							</div>
						</div>
						<a class="more" href="all-categories.php">
						View All <i class="m-icon-swapright m-icon-white"></i>
						</a>
					</div>
				</div>
				<div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
					<div class="dashboard-stat red-intense">
						<div class="visual">
							<i class="fa fa-bar-chart-o"></i>
						</div>
						<div class="details">
							<div class="number">
								 <?= get_table_count($database->db, 'web_users') ?>
							</div>
							<div class="desc">
								 Total Web Users
							</div>
						</div>
						<a class="more" href="web-users.php">
						View All <i class="m-icon-swapright m-icon-white"></i>
						</a>
					</div>
				</div>
				<div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
					<div class="dashboard-stat green-haze">
						<div class="visual">
							<i class="fa fa-shopping-cart"></i>
						</div>
						<div class="details">
							<div class="number">
								 <?= get_table_count($database->db, 'regions') ?>
							</div>
							<div class="desc">
								 Total Seasons
							</div>
						</div>
						<a class="more" href="all-seasons.php">
						View All <i class="m-icon-swapright m-icon-white"></i>
						</a>
					</div>
				</div>
				<div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
					<div class="dashboard-stat purple-plum">
						<div class="visual">
							<i class="fa fa-globe"></i>
						</div>
						<div class="details">
							<div class="number">
                            	+1
							</div>
							<div class="desc">
								 Add a New BtS
							</div>
						</div>
						<a class="more" href="bts.php">
						View more <i class="m-icon-swapright m-icon-white"></i>
						</a>
					</div>
				</div>
			</div>
			<!-- END DASHBOARD STATS -->
			<div class="clearfix">
			</div>			
		</div>
	</div>
	<!-- END CONTENT -->
</div>
<!-- END CONTAINER -->
<!-- BEGIN FOOTER -->
<?php include('inc/footer.php'); ?>
<!-- END FOOTER -->
<!-- BEGIN JAVASCRIPTS(Load javascripts at bottom, this will reduce page load time) -->
<!-- BEGIN CORE PLUGINS -->
<!--[if lt IE 9]>
<script src="assets/global/plugins/respond.min.js"></script>
<script src="assets/global/plugins/excanvas.min.js"></script> 
<![endif]-->
<script src="assets/global/plugins/jquery.min.js" type="text/javascript"></script>
<script src="assets/global/plugins/jquery-migrate.min.js" type="text/javascript"></script>
<!-- IMPORTANT! Load jquery-ui-1.10.3.custom.min.js before bootstrap.min.js to fix bootstrap tooltip conflict with jquery ui tooltip -->
<script src="assets/global/plugins/jquery-ui/jquery-ui-1.10.3.custom.min.js" type="text/javascript"></script>
<script src="assets/global/plugins/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
<script src="assets/global/plugins/bootstrap-hover-dropdown/bootstrap-hover-dropdown.min.js" type="text/javascript"></script>
<script src="assets/global/plugins/jquery-slimscroll/jquery.slimscroll.min.js" type="text/javascript"></script>
<script src="assets/global/plugins/jquery.blockui.min.js" type="text/javascript"></script>
<script src="assets/global/plugins/jquery.cokie.min.js" type="text/javascript"></script>
<script src="assets/global/plugins/uniform/jquery.uniform.min.js" type="text/javascript"></script>
<script src="assets/global/plugins/bootstrap-switch/js/bootstrap-switch.min.js" type="text/javascript"></script>
<!-- END CORE PLUGINS -->
<!-- BEGIN PAGE LEVEL PLUGINS -->

<!-- END PAGE LEVEL PLUGINS -->
<!-- BEGIN PAGE LEVEL SCRIPTS -->
<script src="assets/global/scripts/metronic.js" type="text/javascript"></script>
<script src="assets/admin/layout/scripts/layout.js" type="text/javascript"></script>

<script src="assets/admin/layout/scripts/demo.js" type="text/javascript"></script>
<script src="assets/admin/pages/scripts/index.js" type="text/javascript"></script>

<!-- END PAGE LEVEL SCRIPTS -->
<script>
jQuery(document).ready(function() {    
   Metronic.init(); // init metronic core componets
   Layout.init(); // init layout
   Index.init();   
   Tasks.initDashboardWidget();
});
</script>
<!-- END JAVASCRIPTS -->
<?php unset($_SESSION['msg1']); ?>
</body>
<!-- END BODY -->
</html>