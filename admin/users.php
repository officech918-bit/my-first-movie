<?php
	date_default_timezone_set('Asia/Kolkata');
	$date = date("Y-m-d H:i:s", time());
	
	//get class files
	include('inc/requires.php');
	include('classes/class.admin.php');
	include('classes/class.webmaster.php');

	//create objects
	$database = new MySQLDB();
	$user = new visitor();

	if (empty($_SESSION['csrf_token'])) {
	    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
	}
	$csrf_token = $_SESSION['csrf_token'];

	$menu = 'inc/left-menu-user.php';

	//check if the user is not logged in
	if(!$user->check_session())
	{	
		header("location: index.php"); 
		exit();
	} else if ($_SESSION['user_type'] == 'webmaster'){
		$user = new webmaster();
		$menu = 'inc/left-menu-webmaster.php';		
		$wm_first_name = $user->get_wm_first_name();
		$wm_last_name = $user->get_wm_last_name();
	} else if ($_SESSION['user_type'] == 'admin'){
		$user = new admin();
		$menu = 'inc/left-menu-admin.php';
	} else {
		header("location: dashboard.php"); 
		exit();
	}
	
	$sitename = $user->get_sitename();
	$sub_location = $user->get_sub_location();
	$admin_location = $user->get_admin_location();
	
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

    $is_error = false;
    $error = [];

	if(isset($_POST['submit_access'])) {
        if (isset($_POST['csrf_token']) && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            $user_id_for_access = $_POST['user_id_for_access'];
            $access_controls = isset($_POST['access']) ? $_POST['access'] : [];
            
            $final_access_control = [];
            $final_access_keys = [];

            $all_access_keys = ['testimonials','categories','seasons','behind_the_scenes','web_users', 'enrollments', 'winners', 'sorting', 'panelists', 'news', 'core_team', 'admin'];

            foreach($all_access_keys as $key) {
                $permissions = [0,0,0]; // View, Edit, Delete
                if(isset($access_controls[$key])) {
                    $posted_permissions = $access_controls[$key];
                    $permissions[0] = isset($posted_permissions[0]) && $posted_permissions[0] == '1' ? 1 : 0;
                    $permissions[1] = isset($posted_permissions[1]) && $posted_permissions[1] == '1' ? 1 : 0;
                    $permissions[2] = isset($posted_permissions[2]) && $posted_permissions[2] == '1' ? 1 : 0;
                }
                $final_access_control[] = implode(',', $permissions);
                $final_access_keys[] = $key;
            }

            $access_control_str = implode(";", $final_access_control);
							$access_keys_str = implode(",", $final_access_keys);

							$update_query = "UPDATE users SET access_control = ?, access_control_keys = ? WHERE user_id = ?";
							$stmt = $database->db->prepare($update_query);
							if($stmt->execute([$access_control_str, $access_keys_str, $user_id_for_access])){
								$_SESSION['msg'] = 'Access controls updated successfully.';
                                $_SESSION['msg_type'] = 1;
							} else {
                $is_error = true;
                $error['access'] = 'Failed to update access controls.';
            }
        } else {
            $is_error = true;
            $error['access'] = 'CSRF token mismatch.';
        }
    }
?>
<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8 no-js"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9 no-js"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en">
<!--<![endif]-->
<!-- BEGIN HEAD -->
<head>
<meta charset="utf-8"/>
<title>Users</title>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<meta http-equiv="Content-type" content="text/html; charset=utf-8">
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

<!-- BEGIN PAGE LEVEL STYLES -->
<link rel="stylesheet" type="text/css" href="assets/global/plugins/bootstrap-datepicker/css/datepicker3.css"/>
<link rel="stylesheet" type="text/css" href="assets/global/plugins/bootstrap-select/bootstrap-select.min.css"/>
<link rel="stylesheet" type="text/css" href="assets/global/plugins/select2/select2.css"/>
<link rel="stylesheet" type="text/css" href="assets/global/plugins/jquery-multi-select/css/multi-select.css"/>
<!-- END PAGE LEVEL SCRIPTS -->

<!-- BEGIN THEME STYLES -->
<link href="assets/global/css/components.css" id="style_components" rel="stylesheet" type="text/css"/>
<link href="assets/global/css/plugins.css" rel="stylesheet" type="text/css"/>
<link href="assets/admin/layout/css/layout.css" rel="stylesheet" type="text/css"/>
<link id="style_color" href="assets/admin/layout/css/themes/darkblue.css" rel="stylesheet" type="text/css"/>
<link href="assets/admin/layout/css/custom.css" rel="stylesheet" type="text/css"/>
<!-- END THEME STYLES -->

<link rel="stylesheet" href="assets/admin/layout/css/validationEngine.jquery.css" type="text/css"/>

 <!-- zebra modal box -->
<link rel="stylesheet" href="assets/admin/layout/css/zebra_dialog.css" type="text/css">

<link rel="shortcut icon" href="favicon.ico"/>
</head>
<!-- END HEAD -->
<!-- BEGIN BODY -->
<body class="page-header-fixed page-quick-sidebar-over-content ">
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
			Manage Users <small></small>
			</h3>
			<div class="page-bar">
				<ul class="page-breadcrumb">
					<li>
						<i class="fa fa-home"></i>
						<a href="dashboard.php">Dashboard</a>
						<i class="fa fa-angle-right"></i>
					</li>
					<li>
						<a href="#">Manage Users</a>
					</li>
				</ul>
				
			</div>
			<!-- END PAGE HEADER-->
			<!-- BEGIN PAGE CONTENT-->
			<div class="row">
				<div class="col-md-12">
					<!-- BEGIN SEARCH FORM-->
					<div class="portlet box blue">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-search"></i> Search User
							</div>
							<div class="tools">
								<a href="" class="collapse"></a>
							</div>
						</div>
						<div class="portlet-body form">
							<form class="form-horizontal" role="form" action="users.php" method="post">
								<div class="form-body">
									<div class="row">
										<div class="col-lg-12">
											<div class="form-group">
												<label class="col-lg-2 control-label">Search by Name or Email</label>
												<div class="col-lg-6">
													<input type="text" id="search_query" name="search_query" class="form-control" value="<?php echo isset($_POST['search_query']) ? htmlspecialchars($_POST['search_query']) : ''; ?>" />
												</div>
												<div class="col-lg-2">
													<button type="submit" name="search_user" class="btn btn-primary">Search</button>
												</div>
											</div>
										</div>
									</div>
								</div>
							</form>
						</div>
					</div>
					<!-- END SEARCH FORM-->

					<!-- BEGIN USER LIST-->
					<div class="portlet box green">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-users"></i> User List
							</div>
						</div>
						<div class="portlet-body">
							<div class="table-responsive">
								<table class="table table-striped table-bordered table-hover">
									<thead>
										<tr>
											<th>Name</th>
											<th>Email</th>
											<th>User Type</th>
											<th>Status</th>
											<th>Actions</th>
										</tr>
									</thead>
									<tbody>
										<?php
										$search_query = "";
										if(isset($_POST['search_user']) && !empty($_POST['search_query'])) {
											$search_query = $_POST['search_query'];
											$search_param = "%".$search_query."%";
											$query = "SELECT * FROM users WHERE first_name LIKE ? OR last_name LIKE ? OR email LIKE ?";
											$stmt = $database->db->prepare($query);
											$stmt->execute([$search_param, $search_param, $search_param]);
										} else {
											$query = "SELECT * FROM users";
											$stmt = $database->db->prepare($query);
											$stmt->execute();
										}
										$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
										if(count($result) > 0) {
											foreach($result as $row) {
												echo '<tr>';
												echo '<td>'.htmlspecialchars($row['first_name']).' '.htmlspecialchars($row['last_name']).'</td>';
												echo '<td>'.htmlspecialchars($row['email']).'</td>';
												echo '<td>'.htmlspecialchars($row['user_type']).'</td>';
												echo '<td>'.($row['status'] == 1 ? 'Active' : 'Inactive').'</td>';
												echo '<td><a href="users.php?id='.$row['user_id'].'" class="btn btn-xs blue">Manage Access</a></td>';
												echo '</tr>';
											}
										} else {
											echo '<tr><td colspan="5">No users found.</td></tr>';
										}
										?>
									</tbody>
								</table>
							</div>
						</div>
					</div>
					<!-- END USER LIST-->

					<?php 
					if(isset($_GET['id'])) { 
						$user_id_to_manage = $_GET['id'];
						$user_to_manage_q = $database->db->prepare("SELECT * FROM users WHERE user_id = ?");
						$user_to_manage_q->execute([$user_id_to_manage]);
						$user_to_manage = $user_to_manage_q->fetch(PDO::FETCH_ASSOC);
						if($user_to_manage) {
							
							// Parsing the access control from the database
							$user_access_keys = !empty($user_to_manage['access_control_keys']) ? explode(',', $user_to_manage['access_control_keys']) : [];
							$user_access_permissions_str = !empty($user_to_manage['access_control']) ? explode(';', $user_to_manage['access_control']) : [];
							
							$permissions_map = [];
							foreach($user_access_keys as $index => $key) {
								if(isset($user_access_permissions_str[$index])) {
									$permissions_map[$key] = explode(',', $user_access_permissions_str[$index]);
								}
							}
					?>
                    <!-- BEGIN MANAGE ACCESS FORM-->
					<div class="portlet box blue">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-key"></i> Manage Access for <?php echo htmlspecialchars($user_to_manage['first_name'].' '.$user_to_manage['last_name']); ?>
							</div>
						</div>
						<div class="portlet-body form">
                            <?php
                            if (isset($_SESSION['msg'])) {
                                echo '<div class="alert alert-' . ($_SESSION['msg_type'] == 1 ? 'success' : 'danger') . '"><strong>' . ($_SESSION['msg_type'] == 1 ? 'Success!' : 'Error!') . '</strong> ' . $_SESSION['msg'] . '</div>';
                                unset($_SESSION['msg']);
                                unset($_SESSION['msg_type']);
                            }
                            if ($is_error && isset($error['access'])) {
                                echo '<div class="alert alert-danger"><strong>Error!</strong> ' . $error['access'] . '</div>';
                            }
                            ?>
							<form class="form-horizontal" role="form" action="users.php?id=<?php echo $user_id_to_manage; ?>" method="post">
								<input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
								<input type="hidden" name="user_id_for_access" value="<?php echo $user_id_to_manage; ?>">
								<div class="form-body">
									<?php
										$all_access_keys = ['testimonials','categories','seasons','behind_the_scenes','web_users', 'enrollments', 'winners', 'sorting', 'panelists', 'news', 'core_team', 'admin'];
										foreach($all_access_keys as $key) {
											$permissions = isset($permissions_map[$key]) ? $permissions_map[$key] : [0,0,0];
									?>
									<div class="form-group">
										<label class="col-md-3 control-label"><?php echo ucfirst(str_replace('_', ' ', $key)); ?></label>
										<div class="col-md-9">
											<label class="checkbox-inline">
												<input type="checkbox" name="access[<?php echo $key; ?>][0]" value="1" <?php if(isset($permissions[0]) && $permissions[0] == 1) echo 'checked'; ?>> View
											</label>
											<label class="checkbox-inline">
												<input type="checkbox" name="access[<?php echo $key; ?>][1]" value="1" <?php if(isset($permissions[1]) && $permissions[1] == 1) echo 'checked'; ?>> Edit
											</label>
											<label class="checkbox-inline">
												<input type="checkbox" name="access[<?php echo $key; ?>][2]" value="1" <?php if(isset($permissions[2]) && $permissions[2] == 1) echo 'checked'; ?>> Delete
											</label>
										</div>
									</div>
									<?php } ?>
								</div>
								<div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
											<button type="submit" name="submit_access" class="btn btn-circle blue">Submit</button>
										</div>
									</div>
								</div>
							</form>
						</div>
					</div>
                    <!-- END MANAGE ACCESS FORM-->
					<?php 
							} else {
								echo '<div class="alert alert-danger">User not found.</div>';
							}
						}
					?>
				</div>
			</div>
			<!-- END PAGE CONTENT-->
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
<!-- IMPORTANT! Load jquery-ui.min.js before bootstrap.min.js to fix bootstrap tooltip conflict with jquery ui tooltip -->
<script src="assets/global/plugins/jquery-ui/jquery-ui.min.js" type="text/javascript"></script>
<script src="assets/global/plugins/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
<script src="assets/global/plugins/bootstrap-hover-dropdown/bootstrap-hover-dropdown.min.js" type="text/javascript"></script>
<script src="assets/global/plugins/jquery-slimscroll/jquery.slimscroll.min.js" type="text/javascript"></script>
<script src="assets/global/plugins/jquery.blockui.min.js" type="text/javascript"></script>
<script src="assets/global/plugins/jquery.cokie.min.js" type="text/javascript"></script>
<script src="assets/global/plugins/uniform/jquery.uniform.min.js" type="text/javascript"></script>
<script src="assets/global/plugins/bootstrap-switch/js/bootstrap-switch.min.js" type="text/javascript"></script>
<!-- END CORE PLUGINS -->


<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>
<script type="text/javascript" src="assets/global/plugins/bootstrap-select/bootstrap-select.min.js"></script>
<script type="text/javascript" src="assets/global/plugins/select2/select2.min.js"></script>
<script type="text/javascript" src="assets/global/plugins/jquery-multi-select/js/jquery.multi-select.js"></script>
<!-- END PAGE LEVEL PLUGINS -->


<!-- BEGIN PAGE LEVEL SCRIPTS -->
<script src="assets/global/scripts/metronic.js" type="text/javascript"></script>
<script src="assets/admin/layout/scripts/layout.js" type="text/javascript"></script>
<script src="assets/admin/layout/scripts/quick-sidebar.js" type="text/javascript"></script>
<script src="assets/admin/layout/scripts/demo.js" type="text/javascript"></script>
<script src="assets/admin/pages/scripts/form-samples.js"></script>
<script src="assets/admin/pages/scripts/components-pickers.js"></script>
<script src="assets/admin/pages/scripts/components-dropdowns.js"></script>

<!-- form validation -->
<script type="text/javascript" src="assets/global/plugins/jquery-validation/js/jquery.validate.min.js"></script>
<script type="text/javascript" src="assets/global/plugins/jquery-validation/js/additional-methods.min.js"></script>

<!-- zebra modal box -->
<script type="text/javascript" src="assets/admin/layout/scripts/zebra_dialog.js"></script>


<!-- END PAGE LEVEL SCRIPTS -->
<script>
jQuery(document).ready(function() {    
   // initiate layout and plugins
   Metronic.init(); // init metronic core components
Layout.init(); // init current layout
QuickSidebar.init(); // init quick sidebar
Demo.init(); // init demo features
   FormSamples.init();
   ComponentsPickers.init();
   ComponentsDropdowns.init();
});
</script>
<!-- END JAVASCRIPTS -->
</body>
<!-- END BODY -->
</html>