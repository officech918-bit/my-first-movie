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

                    <option value="0" <?php if($user_record['status'] == '0' || $user_record['status'] == NULL) echo  'selected="selected"'; ?>>Inactive</option>

                                          </select>



                                      </div>

                                  </div>
									<div class="form-group">
                                    
									<?php 
									  if(isset($user_record['access_control'])) 
									  {
										  $access = $user_record['access_control'];
										 // echo $access;
										 // echo "<br/>";
										  $testimonial = substr($access, 0, 5);
										  $testimonial1 = explode(",",$testimonial);
										  //print_r($testimonial1);
										 // echo "testimonial=".$testimonial;
										  $categories = substr($access, 6, 5);
										  $categories1 = explode(",",$categories);
										  //echo "categories=".$categories;
										  $seasons = substr($access, 12, 5);
										  $seasons1 = explode(",",$seasons);
										  //print_r($seasons1);
										  //echo "seasons=".$seasons;
										  $behind_the_scenes = substr($access, 18, 5);
										  $behind_the_scenes1 = explode(",",$behind_the_scenes);
										  // print_r($behind_the_scenes1);
										  $web_users = substr($access, 24, 5);
										  $web_users1 = explode(",",$web_users);
										 // print_r($web_users1);
										  $access_keys = $user_record['access_control_keys'];
										  $access_keys_array = explode(",",$access_keys);
										 // print_r($access_keys_array);
										  $new[0] = $testimonial1;
										  $new[1] = $categories1;
										  $new[2] = $seasons1;
										  $new[3] = $behind_the_scenes1;
										  $new[4] = $web_users1;
										  /*echo "<pre>";
										  print_r($new);
										  echo "</pre>";*/
										  foreach($access_keys_array as $key=>$value)
										  {
											  $final_access_array[$value] = $new[$key];
										  }
										  foreach($final_access_array as $key=>$value)
										  {
											  if($key == 'testimonials')
											  {
												  $testimonial2 = $final_access_array[$key];
											  }
											  elseif($key == 'categories')
											  {
												  $categories2 = $final_access_array[$key];
											  }
											  elseif($key == 'seasons')
											  {
												  $seasons2 = $final_access_array[$key];
											  }
											  elseif($key == 'behind_the_scenes')
											  {
												  $behind_the_scenes2 = $final_access_array[$key];
											  }
											  elseif($key == 'web_users')
											  {
												  $web_users2 = $final_access_array[$key];
											  }
										  }
										  /*echo "<pre>";
										  print_r($testimonials2);
										  echo "</pre>";
										  echo "<pre>";
										  print_r($categories2);
										  echo "</pre>";
										  echo "<pre>";
										  print_r($seasons2);
										  echo "</pre>";
										  echo "<pre>";
										  print_r($behind_the_scenes2);
										  echo "</pre>";
										  echo "<pre>";
										  print_r($web_users2);
										  echo "</pre>";*/
									  } 
									?>
                                      <label  class="col-lg-4 control-label">Access Control</label>

                                      <div class="col-lg-6">
                                      	<table border="1" style="width:100%">
											<tr>
                                        		<td>Category Name</td>
                                                <td>View</td>
                                                <td>Edit</td>
                                                <td>Delete</td>
                                             </tr>
                                             <tr>
                                        		<td>Testimonials</td>
                                                <?php if(isset($testimonial2)) { 
												$i=0;
												for($i=0;$i<count($testimonial2);$i++) { //echo $i; ?>
													<td><input type='checkbox' class='form-control' id='' name='access[testimonials][<?php echo $i; ?>]' value='1' <?php if($testimonial2[$i] == 1) { ?> checked=checked <?php } ?>>
                                                    </td> 
												<?php } } else { ?>
                                                <td><input type='checkbox' class='form-control' id='' name='access[testimonials][0]' value='1'>
                                                 </td>
                                                 <td><input type='checkbox' class='form-control' id='' name='access[testimonials][1]' value='1'>
                                                 </td>
                                                 <td><input type='checkbox' class='form-control' id='' name='access[testimonials][2]' value='1'>
                                                 </td>
                                                 <?php } ?>
                                             </tr>
                                             <tr>
                                        		<td>Categories</td>
                                                <?php if(isset($categories2)) { 
												$i=0;
												for($i=0;$i<count($categories2);$i++) { //echo $i; ?>
													<td><input type='checkbox' class='form-control' id='' name='access[categories][<?php echo $i; ?>]' value='1' <?php if($categories2[$i] == 1) { ?> checked=checked <?php } ?>>
                                                    </td> 
												<?php } } else { ?>
                                                <td><input type="checkbox" class="form-control" id="" name="access[categories][0]" value="1" ></td>
                                                <td><input type="checkbox" class="form-control" id="" name="access[categories][1]" value="1" ></td>
                                                <td><input type="checkbox" class="form-control" id="" name="access[categories][2]" value="1" ></td>
                                                <?php } ?>
                                             </tr>
                                             <tr>
                                        		<td>Seasons</td>
                                                <?php if(isset($seasons2)) { 
												$i=0;
												for($i=0;$i<count($seasons2);$i++) { //echo $i; ?>
													<td><input type='checkbox' class='form-control' id='' name='access[seasons][<?php echo $i; ?>]' value='1' <?php if($seasons2[$i] == 1) { ?> checked=checked <?php } ?>>
                                                    </td> 
												<?php } } else { ?>
                                                <td><input type="checkbox" class="form-control" id="" name="access[seasons][0]" value="1" ></td>
                                                <td><input type="checkbox" class="form-control" id="" name="access[seasons][1]" value="1" ></td>
                                                <td><input type="checkbox" class="form-control" id="" name="access[seasons][2]" value="1" ></td>
                                                <?php } ?>
                                             </tr>
                                             <tr>
                                        		<td>Behind the scenes</td>
                                                 <?php if(isset($behind_the_scenes2)) { 
												$i=0;
												for($i=0;$i<count($behind_the_scenes2);$i++) { //echo $i; ?>
													<td><input type='checkbox' class='form-control' id='' name='access[behind_the_scenes][<?php echo $i; ?>]' value='1' <?php if($behind_the_scenes2[$i] == 1) { ?> checked=checked <?php } ?>>
                                                    </td> 
												<?php } } else { ?>
                                                <td><input type="checkbox" class="form-control" id="" name="access[behind_the_scenes][0]" value="1" ></td>
                                                <td><input type="checkbox" class="form-control" id="" name="access[behind_the_scenes][1]" value="1" ></td>
                                                <td><input type="checkbox" class="form-control" id="" name="access[behind_the_scenes][2]" value="1" ></td>
                                                <?php } ?>
                                             </tr>
                                             <tr>
                                        		<td>Web users</td>
                                                 <?php if(isset($web_users2)) { 
												$i=0;
												for($i=0;$i<count($web_users2);$i++) { //echo $i; ?>
													<td><input type='checkbox' class='form-control' id='' name='access[web_users][<?php echo $i; ?>]' value='1' <?php if($web_users2[$i] == 1) { ?> checked=checked <?php } ?>>
                                                    </td> 
												<?php } } else { ?>
                                                <td><input type="checkbox" class="form-control" id="" name="access[web_users][0]" value="1" ></td>
                                                <td><input type="checkbox" class="form-control" id="" name="access[web_users][1]" value="1" ></td>
                                                <td><input type="checkbox" class="form-control" id="" name="access[web_users][2]" value="1" ></td>
                                                <?php } ?>
                                             </tr>
                                        </table>
                                          

                                      </div>

                                  </div>

                                  </div>

                                  </div>

						

                            	<div class="form-body">

                                	<h3 class="form-section">User Details</h3>

                                	<div class="row">

                			     <div class="col-lg-6">

                                  	<div class="form-group <?php if(($is_error) && $error['first_name'] != '') echo 'has-error' ?>">

                                      <label  class="col-lg-4 control-label">First Name</label>

                                      <div class="col-lg-6">

                                      	  <input type="text" id="firstname" name="firstname" value="" class="validate[required, maxSize[30]] text-input form-control" />

                                          <?php if(($is_error) && $error['first_name'] != '') echo '<span class="help-block">'.$error['first_name'].'</span>' ?>

                                         

                                      </div>

                                  </div>

                                  	<div class="form-group <?php if(($is_error) && $error['last_name'] != '') echo 'has-error' ?>">

                                      <label  class="col-lg-4 control-label">Last Name</label>

                                      <div class="col-lg-6">

                                      	  <input type="text" id="lastname" name="lastname" value="" class="validate[required, maxSize[30]] text-input form-control" />	

                                          <?php if(($is_error) && $error['last_name'] != '') echo '<span class="help-block">'.$error['last_name'].'</span>' ?>

                                      </div>

                                  </div>

                                  	<div class="form-group <?php if(($is_error) && $error['email'] != '') echo 'has-error' ?>">

                                      <label  class="col-lg-4 control-label">Email</label>

                                      <div class="col-lg-6">

                                      	  <input type="text" id="email" name="email" class="validate[required,custom[email]] form-control" value="" />

                                          <?php if(($is_error) && $error['email'] != '') echo '<span class="help-block">'.$error['email'].'</span>' ?>

                                      </div>

                                  </div>

                                  

                                  	<div class="form-group">

                                      <label  class="col-lg-4 control-label">Designation</label>

                                      <div class="col-lg-6">

                                          <input type="text" class="form-control" id="designation" name="designation" value="" >

                                      </div>

                                  </div>

                                  

                                  	

                                      

                                  </div>

                                  <div class="col-lg-6">

                                  	<div class="form-group <?php if(($is_error) && $error['user_type'] != '') echo 'has-error' ?>">

                                      <label class="col-sm-4 control-label col-lg-4" for="inputSuccess">User Type</label>

                                      <div class="col-lg-6">

                                      	  <select name="user_type" id="user_type" class="validate[required] form-control m-bot15" style="padding:5px;">

                                            <option value="">Select User Type</option>

                                            <option value="user">User</option>

                                            <option value="admin">Admin</option>

                                          </select>	 

                                          <?php if(($is_error) && $error['user_type'] != '') echo '<span class="help-block">'.$error['user_type'].'</span>' ?>

                                      </div>

                                  </div>

                                  	<!--<div class="form-group <?php if(($is_error) && $error['password'] != '') echo 'has-error' ?>">

                                          <label  class="col-lg-4 control-label">Password</label>

                                          <div class="col-lg-6">

                                          	  <input type="password" id="password" name="password" class="validate[required, minSize[6]] text-input form-control" /> 	 

                                              <?php if(($is_error) && $error['password'] != '') echo '<span class="help-block">'.$error['password'].'</span>' ?>

                                          </div>

                                      </div>

                                  	<div class="form-group <?php if(($is_error) && $error['password2'] != '') echo 'has-error' ?>">

                                          <label  class="col-lg-4 control-label">Re-type Password</label>

                                          <div class="col-lg-6">

                                          	  <input type="password" id="password2" name="password2" class="validate[required, minSize[6], equals[password]] text-input form-control" />

                                              <?php if(($is_error) && $error['password2'] != '') echo '<span class="help-block">'.$error['password2'].'</span>' ?>

                                          </div>

                                      </div> -->

                                  	

                                  	<div class="form-group">

                                      <label class="col-sm-4 control-label col-lg-4" for="">Status</label>

                                      <div class="col-lg-6">

                                          <select class="form-control m-bot15" name="status" id="status">

                                          	  <option value="1">Active</option>

                    <option value="0">Inactive</option>

                                          </select>



                                      </div>

                                  </div>

                                   <div class="form-group">

                                      <label class="col-sm-4 control-label col-lg-4" for="">Auto Inform</label>

                                      <div class="col-lg-6">

                                         <div class="checkbox">

                                              <label>

                                                  <input type="checkbox" value="" name="auto_inform">

                                                  Inform user with ID & Password

                                              </label>

                                          </div>

                                      </div>

                                  </div>
                                  
                                   <div class="form-group">

                                      <label  class="col-lg-4 control-label">Access Control</label>

                                      <div class="col-lg-6">
                                      	<table border="1" style="width:100%">
											<tr>
                                        		<td>Category Name</td>
                                                <td>View</td>
                                                <td>Edit</td>
                                                <td>Delete</td>
                                             </tr>
                                             <tr>
                                        		<td>Testimonials</td>
                                                <td><input type="checkbox" class="form-control" id="" name="access[testimonials][0]" value="1" ></td>
                                                <td><input type="checkbox" class="form-control" id="" name="access[testimonials][1]" value="1" ></td>
                                                <td><input type="checkbox" class="form-control" id="" name="access[testimonials][2]" value="1" ></td>
                                             </tr>
                                             <tr>
                                        		<td>Categories</td>
                                                <td><input type="checkbox" class="form-control" id="" name="access[categories][0]" value="1" ></td>
                                                <td><input type="checkbox" class="form-control" id="" name="access[categories][1]" value="1" ></td>
                                                <td><input type="checkbox" class="form-control" id="" name="access[categories][2]" value="1" ></td>
                                             </tr>
                                             <tr>
                                        		<td>Seasons</td>
                                                <td><input type="checkbox" class="form-control" id="" name="access[seasons][0]" value="1" ></td>
                                                <td><input type="checkbox" class="form-control" id="" name="access[seasons][1]" value="1" ></td>
                                                <td><input type="checkbox" class="form-control" id="" name="access[seasons][2]" value="1" ></td>
                                             </tr>
                                             <tr>
                                        		<td>Behind the scenes</td>
                                                <td><input type="checkbox" class="form-control" id="" name="access[behind_the_scenes][0]" value="1" ></td>
                                                <td><input type="checkbox" class="form-control" id="" name="access[behind_the_scenes][1]" value="1" ></td>
                                                <td><input type="checkbox" class="form-control" id="" name="access[behind_the_scenes][2]" value="1" ></td>
                                             </tr>
                                             <tr>
                                        		<td>Web users</td>
                                                <td><input type="checkbox" class="form-control" id="" name="access[web_users][0]" value="1" ></td>
                                                <td><input type="checkbox" class="form-control" id="" name="access[web_users][1]" value="1" ></td>
                                                <td><input type="checkbox" class="form-control" id="" name="access[web_users][2]" value="1" ></td>
                                             </tr>
                                        </table>
                                          

                                      </div>

                                  </div>
									 </div>

                                  </div>

									
                                    
                                   <div class="form-actions">
                                   	<input name="submit_user" type="submit" value="Save" class="btn blue" >
			   					</div>

                                  </div>
                              </form>
							
						</div>

                        

					</div>

					<!-- END SAMPLE FORM PORTLET-->

					<!-- BEGIN SAMPLE FORM PORTLET-->

					

					<!-- END SAMPLE FORM PORTLET-->

					<!-- BEGIN SAMPLE FORM PORTLET-->

					

					<!-- END SAMPLE FORM PORTLET-->

				</div>

				

			</div>

			<div class="row">

                  <div class="col-lg-12">

                      <section class="panel">

                          <header class="panel-heading">

                              All Existing Users

                          </header>

                          <div class="panel-body">

                              <section id="unseen">

                              	

                                <table class="table table-bordered table-striped table-condensed">

                                  <thead>

                                  <tr>

                                      <th>Sr. #</th>

                                      <th>First Name</th>

                                      <th>Last Name</th>

                                      <th>Email</th>

                                      <th>User Type</th>

                                      <th>Last Login</th>

                                      <th>Login IP</th>

                                      <th>Status</th>

                                      <th>Action</th>

                                  </tr>

                                  </thead>

                                  <tbody>
                                  
                                      <!-- <?php 
                                        $index = 1;
                                        try {
                                            // Use the correct PDO object from our database class and prepare the statement
                                            $query1 = "SELECT *, DATE_FORMAT(last_login, '%d %b %Y %h:%i %p') AS last_login_formatted FROM users";
                                            $stmt = $database->db->query($query1);

                                            // Fetch all results at once into an array. This is more efficient than fetching row by row.
                                            $profiles = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                            // A foreach loop is a clean way to iterate over the results.
                                            // If there are no results, the loop will simply not run.
                                            foreach ($profiles as $profile) {
                                                // Sanitize all output with htmlspecialchars to prevent XSS attacks.
                                                $status = (int)$profile['status'] === 1 ? 'Active' : 'Inactive';
                                                $id = htmlspecialchars((string)$profile['user_id'], ENT_QUOTES, 'UTF-8');
                                                $firstName = htmlspecialchars($profile['first_name'], ENT_QUOTES, 'UTF-8');
                                                $lastName = htmlspecialchars($profile['last_name'], ENT_QUOTES, 'UTF-8');
                                                $email = htmlspecialchars($profile['email'], ENT_QUOTES, 'UTF-8');
                                                $userType = htmlspecialchars($profile['user_type'], ENT_QUOTES, 'UTF-8');
                                                $lastLogin = htmlspecialchars($profile['last_login_formatted'] ?? 'Never', ENT_QUOTES, 'UTF-8');
                                                $ipAddress = htmlspecialchars($profile['ip_address'] ?? 'N/A', ENT_QUOTES, 'UTF-8');

                                                echo '<tr>
                                                    <td>' . $index++ . '</td>
                                                    <td>' . $firstName . '</td>
                                                    <td>' . $lastName . '</td>
                                                    <td>' . $email . '</td>
                                                    <td>' . $userType . '</td>
                                                    <td>' . $lastLogin . '</td>
                                                    <td>' . $ipAddress . '</td>
                                                    <td>' . $status . '</td>
                                                    <td>
                                                        <div class="btn-group" style="margin-bottom:0px !important;">
                                                            <a class="btn red" href="#" data-toggle="dropdown" style="padding:3px 7px !important;">
                                                                <i class="icon-user"></i> Options <i class="icon-angle-down"></i>
                                                            </a>
                                                            <ul class="dropdown-menu">
                                                                <li><a href="users.php?id=' . $id . '"><i class="icon-pencil"></i>Edit</a></li>
                                                                <li><a href="javascript:void(0)" class="example36" title="' . $firstName . ' | ' . $id . '"><i class="icon-trash"></i> Delete</a></li>
                                                                <li><a href="javascript:void(0)" class="example37" title="' . $firstName . ' | ' . $id . '"><i class="icon-pencil"></i> Reset Password</a></li>
                                                            </ul>
                                                        </div>
                                                    </td>
                                                </tr>';
                                            }
                                        } catch (PDOException $e) {
                                            // It's good practice to handle potential database errors.
                                            // In a real application, you would log this error.
                                            echo '<tr><td colspan="9">Error fetching users: ' . htmlspecialchars($e->getMessage()) . '</td></tr>';
                                        }
                                      ?> -->
                                  
                                  </tbody>
                              </table>
                              </section>

                          </div>

                      </section>

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

<script type="text/javascript" src="assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>

<script type="text/javascript" src="assets/global/plugins/bootstrap-select/bootstrap-select.min.js"></script>

<script type="text/javascript" src="assets/global/plugins/select2/select2.min.js"></script>

<script type="text/javascript" src="assets/global/plugins/jquery-multi-select/js/jquery.multi-select.js"></script>

<!-- END PAGE LEVEL PLUGINS -->



<!-- BEGIN PAGE LEVEL SCRIPTS -->

<script src="assets/global/scripts/metronic.js" type="text/javascript"></script>

<script src="assets/admin/layout/scripts/layout.js" type="text/javascript"></script>

<script src="assets/admin/layout/scripts/demo.js" type="text/javascript"></script>

<script src="assets/admin/pages/scripts/components-pickers.js"></script>

<script src="assets/admin/pages/scripts/components-dropdowns.js"></script>

<script src="assets/admin/pages/scripts/form-samples.js"></script>

<!-- END PAGE LEVEL SCRIPTS -->

    

<script>

jQuery(document).ready(function() {   

    // initiate layout and plugins

    Metronic.init(); // init metronic core components

	Layout.init(); // init current layout

	Demo.init(); // init demo features

	ComponentsPickers.init();

	FormSamples.init();

	ComponentsDropdowns.init();

});

</script>

<!-- END JAVASCRIPTS -->

<!-- validation --> 



<script src="assets/admin/layout/scripts/jquery.validationEngine-en.js" type="text/javascript" charset="utf-8"></script> 

<script src="assets/admin/layout/scripts/jquery.validationEngine.js" type="text/javascript" charset="utf-8"></script> 

<script>

        jQuery(document).ready(function(){

			// binds form submission and fields to the validation engine

            jQuery("#user_form").validationEngine();

        });

    </script> 



<script type="text/javascript" src="assets/admin/layout/scripts/highlight.js"></script>

<script type="text/javascript" src="assets/admin/layout/scripts/zebra_dialog.js"></script>

<script type="text/javascript">

    hljs.initHighlightingOnLoad();

</script>

<script type="text/javascript">

    $(document).ready(function() {

        $('.example36').bind('click', function(e) {

            var temp = this.title;

            var arr = temp.split('|');

            //var index = temp.indexOf("|");

            //var name = temp.substring(0, index);

            //var user_id = temp.substring(index+2);

			var name = arr[0];

            var user_id = arr[1];

			//alert(user_id);



            e.preventDefault();

            $.Zebra_Dialog('<strong>Are you sure</strong>, you want to delete ' + name, {

                'type':     'question',

                'title':    'Confirmation',

                'buttons':  ['Yes', 'No'],

                'onClose':  function(caption) {

                    if(caption == 'Yes'){

                        $.ajax({ url: 'delete_user.php',

                        data: {id: user_id},

                        type: 'post',

                        success: function(output) {

                            //alert(client_name + ' Deleted Successfully');

                            $.Zebra_Dialog(name + ' Deleted Successfully', {

                            'type':     'confirmation',

                            'title':    'Confirmation',

                             'onClose':  function() {

                               location.reload();

                            }

                        });

                            

                        }

                    });



                    }

                }

            });

        });

		$('.example37').bind('click', function(e) {

            var temp = this.title;

            var arr = temp.split('|');

            //var index = temp.indexOf("|");

            //var name = temp.substring(0, index);

            //var user_id = temp.substring(index+2);

			var name = arr[0];

            var user_id = arr[1];

			//alert(user_id);



            e.preventDefault();

            $.Zebra_Dialog('<strong>Are you sure</strong>, you want to reset password for ' + name, {

                'type':     'question',

                'title':    'Confirmation',

                'buttons':  ['Yes', 'No'],

                'onClose':  function(caption) {

                    if(caption == 'Yes'){

                        $.ajax({ url: 'reset_user_pass.php',

                        data: {id: user_id},

                        type: 'post',

                        success: function(output) {

                            //alert(client_name + ' Deleted Successfully');

                            $.Zebra_Dialog('Password of' + name + ' Reset Successfully. New Password is ' + output, {

                            'type':     'confirmation',

                            'title':    'Confirmation',

                             'onClose':  function() {

                               //location.reload();

                            }

                        });

                            

                        }

                    });



                    }

                }

            });

        });

    });

</script> 



<?php 

unset($_SESSION['msg_type']);

unset($_SESSION['msg']);

?>

</body>

<!-- END BODY -->

</html><?php
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

	$is_edit = false;
	$is_error = false;
	$error = array();

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

		$user = new user();

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

	

	

	if(isset($_POST['submit_user'])) {

		if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
			$is_error = true;
			$error['csrf'] = "CSRF token validation failed. Please try again.";
		}

		if(isset($_GET['id'])) {$is_edit = true; $user_id = $_GET['id'];}

		/*echo "<pre>";
		print_r($_POST);
		echo "</pre>";
		exit();*/
		$first_name = $_POST['firstname'];																				

		$last_name = $_POST['lastname'];

		$email = $_POST['email'];

		//$password = $_POST['password'];

		//$password2 = $_POST['password2'];

		$user_type = $_POST['user_type'];

		$designation = $_POST['designation'];

		$status = $_POST['status'];

		$auto_inform = false;

		if(isset($_POST['auto_inform'])) { $auto_inform = true; } 

		

		//validate

		if(!valid::hasValue($first_name)) { $is_error = true; $error['first_name'] = "First Name can not be empty"; }

		else if(valid::isTooLong($first_name, 30)) { $is_error = true; $error['first_name'] = "First Name can not more than 30 character"; }

		

		if(!valid::hasValue($last_name)) { $is_error = true; $error['last_name'] = "Last Name can not be empty"; }

		else if(valid::isTooLong($last_name, 30)) { $is_error = true; $error['last_name'] = "First Name can not more than 30 character"; }

		

		if(!valid::isEmail($email)) { $is_error = true; $error['email'] = "Invalid Email id"; }	

		else {
			//check through database if already exist
			if ($is_edit) {
				$stmt = $database->db->prepare("SELECT user_id FROM users WHERE email = ? AND user_id != ?");
				$stmt->bind_param('ss', $email, $user_id);
			} else {
				$stmt = $database->db->prepare("SELECT user_id FROM users WHERE email = ?");
				$stmt->bind_param('s', $email);
			}
			$stmt->execute();
			$result = $stmt->get_result();
			if ($result->num_rows > 0) {
				$is_error = true; $error['email'] = "User with this mail id exist";
			}
			$stmt->close();
		}

		/* 

		if(!valid::hasValue($password)) {

			$is_error = true; $error['password'] = "Please enter your password";

		} else if(!valid::isTooShort($password, 6)) { $is_error = true; $error['password'] = "Password length must be equal or more than 6 characters"; }

		

		if($password != $password2) { $is_error = true; $error['password2'] = "Password does not match";}

		*/

		if(!valid::hasValue($user_type)) {

			$is_error = true; $error['user_type'] = "Please select user type";

		}
		//$access_control = "";
		if(isset($_POST['access']))
		{
			$access_controls = $_POST['access'];
			/*echo "<pre>";
			print_r($access_controls);
			echo "</pre>";*/
			//exit();
		$access_controls_keys = array('testimonials','categories','seasons','behind_the_scenes','web_users', 'enrollments', 'winners', 'sorting', 'panelists', 'news', 'core_team', 'admin');
			$x = 0;
			foreach($access_controls as $key=>$value)
			{
				$access_controls_keys_post[$x] = $key;
				$x++;
			}
			
			if(count($access_controls_keys_post) < count($access_controls_keys))
			{
				for($i=0;$i<count($access_controls_keys);$i++)
				{
					if(in_array($access_controls_keys[$i],$access_controls_keys_post))
					{
						//echo $access_controls_keys[$i];
					}
					else
					{
						for($y=0;$y<3;$y++)
						{
							$key_name = $access_controls_keys[$i];
							$access_controls[$key_name][$y] = 0;
						}
					}
				}
				
			}
			//exit();
			/*echo "<pre>";
			print_r($access_controls);
			echo "</pre>";
			exit();*/
			foreach($access_controls as $key=>$value)
			{
				$a[$key]=$access_controls[$key];
				//print_r($a[$key]);
				for($i=0;$i<3;$i++)
				{
					//echo $a[$key][$i];
					if(isset($a[$key][$i]))
					{
								
					}
					else
					{
						$a[$key][$i] = 0;	
					}
				}
				//$b[$key] = asort($a[$key]);
				$b = $a[$key];
				ksort($b);	
				$c[$key] = $b;
			}
			$z=0;
			foreach($c as $key=>$value)
			{
				$d[$key] = implode(",",$c[$key]);
				$access_keys[$z] = $key;
				$z++;
			}
			$access_control = implode(",",$d);
			$access_controls_keys = implode(",",$access_keys);
			/*echo "<pre>";
			print_r($access_control);
			echo "</pre>";
			echo "<pre>";
			print_r($access_controls_keys);
			echo "</pre>";
			exit();*/
		}
		else
		{
			$access_control = "";
			$access_controls_keys="";
		}
				/*echo "<pre>";
				print_r($e);
				echo "<pre>";
				exit();*/
				/*foreach($a as $key=>$value)
				{
					$b = $a[$key];
					ksort($b);	
					$c[$key] = $b;
				}*/
				//exit();
				//$b = sort($a);
				/*echo "<pre>";
				print_r($c);
				echo "</pre>";
				exit();*/

			



		if(!$is_error) {

			//$user_id = $user->generateUniqueID();

			if($is_edit) {

				$query = "UPDATE users SET status=?, first_name=?, last_name=?, email=?, user_type=?, last_update=?, designation=?, access_control=?, access_control_keys=? WHERE user_id=?";
				$stmt = $database->db->prepare($query);
				$stmt->bind_param('ssssssssss', $status, $first_name, $last_name, $email, $user_type, $date, $designation, $access_control, $access_controls_keys, $user_id);
				
				$result = $stmt->execute();

				if(!$result) die("Unable to update data: " . $stmt->error);

				else { 				

					$_SESSION['msg_type'] = 1;

					$_SESSION['msg'] = 'User Details were updated.';

						

					header("location: users.php"); 

					exit();

				 }			

			} 

			else {

				
				//generate a new password and salt
				$newUser = new visitor();
				$password = $newUser->rand_string(8);	
				$salt = $newUser->generateSalt();
				$hash = $newUser->generateHash($password, $salt);
				
				$query = "INSERT INTO users(create_date, salt, status, first_name, last_name, email, password, user_type, designation, access_control, access_control_keys) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"; 
				$stmt = $database->db->prepare($query);
				$stmt->bind_param('sssssssssss', $date, $salt, $status, $first_name, $last_name, $email, $hash, $user_type, $designation, $access_control, $access_controls_keys);

				//echo $query;
				//exit();
				$result = $stmt->execute();

				if(!$result) die("Unable to insert data: " . $stmt->error);

				else { 

					//echo $auto_inform; 

					if($auto_inform) {

						$subject = "Login Information for: $sitename";

						$headers = "From: info@satyamraj.com\r\n";

						$headers .= "Reply-To: info@satyamraj.com\r\n";

						$headers .= "MIME-Version: 1.0\r\n";

						$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

						$message = "Your Login information as below:<br>

									<b>Login Link:</b> <a href='".$path.$admin_location."'>".$path.$admin_location."</a><br>

									<b>Login ID:</b>".$email."<br>

									<b>Password:</b>".$password."<br>";

			

						//echo $message;			

						mail($email,$subject,$message,$headers);			

					}

					

					$_SESSION['msg_type'] = 1;

					$_SESSION['msg'] = 'User '.$email.'Added<br />Password: '.$password;

						

					header("location: users.php"); 

					exit();

				 }	

				

			}

			

			

		}	



	}

	

	if(isset($_GET['id'])) {	

		$user_id = $_GET['id'];

		$query2 = "SELECT * FROM users WHERE user_id = ?";
		$stmt = $database->db->prepare($query2);
		$stmt->bind_param("s", $user_id);
		$stmt->execute();
		$result2 = $stmt->get_result();

		

		if($result2->num_rows > 0) {

			$is_edit = true;

			$data = $result2->fetch_assoc();

			

			$status = $data['status'];

			$first_name = $data['first_name'];

			$last_name = $data['last_name'];

			$email = $data['email'];

			$user_type = $data['user_type'];

			$designation = $data['designation'];

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

					<!-- BEGIN SAMPLE FORM PORTLET-->

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
							<form class="form-horizontal" role="form" action="" method="post" enctype="multipart/form-data" id="user_search_form" name="user_search_form">
								<div class="form-body">
									<div class="row">
										<div class="col-lg-12">
											<div class="form-group">
												<label class="col-lg-2 control-label">Search by Name or Email</label>
												<div class="col-lg-6">
													<input type="text" id="search_query" name="search_query" class="form-control" />
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
										if(isset($_POST['search_user'])) {
											$search_query = $_POST['search_query'];
											$query = "SELECT * FROM users WHERE first_name LIKE ? OR last_name LIKE ? OR email LIKE ?";
											$stmt = $database->db->prepare($query);
											$search_param = "%".$search_query."%";
											$stmt->bind_param('sss', $search_param, $search_param, $search_param);
										} else {
											$query = "SELECT * FROM users";
											$stmt = $database->db->prepare($query);
										}
										$stmt->execute();
										$result = $stmt->get_result();
										if($result->num_rows > 0) {
											while($row = $result->fetch_assoc()) {
												echo '<tr>';
												echo '<td>'.$row['first_name'].' '.$row['last_name'].'</td>';
												echo '<td>'.$row['email'].'</td>';
												echo '<td>'.$row['user_type'].'</td>';
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

					<?php 

					if(isset($_POST['submit_access'])) {
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
						$stmt->bind_param('sss', $access_control_str, $access_keys_str, $user_id_for_access);
						if($stmt->execute()){
							$_SESSION['msg_type'] = 1;
							$_SESSION['msg'] = 'Access controls updated successfully.';
							header("Location: users.php?id=".$user_id_for_access);
							exit();
						} else {
							$is_error = true;
							$error['access'] = 'Failed to update access controls.';
						}
					}

					if(isset($_GET['id'])) { 
						$user_id_to_manage = $_GET['id'];
						$user_to_manage_q = $database->db->prepare("SELECT * FROM users WHERE user_id = ?");
						$user_to_manage_q->bind_param('s', $user_id_to_manage);
						$user_to_manage_q->execute();
						$user_to_manage_r = $user_to_manage_q->get_result();
						if($user_to_manage_r->num_rows > 0) {
							$user_to_manage = $user_to_manage_r->fetch_assoc();
							
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
					<div class="portlet box blue">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-key"></i> Manage Access for <?php echo htmlspecialchars($user_to_manage['first_name'].' '.$user_to_manage['last_name']); ?>
							</div>
						</div>
						<div class="portlet-body form">
							<?php if(isset($_SESSION['msg'])) echo '<div class="alert alert-'.($_SESSION['msg_type'] == 1 ? 'success' : 'danger').'"><strong>'.($_SESSION['msg_type'] == 1 ? 'Success!' : 'Error!').'</strong> '.$_SESSION['msg'].'</div>'; ?>
							<?php if($is_error && isset($error['access'])) echo '<div class="alert alert-danger"><strong>Error!</strong> '.$error['access'].'</div>'; ?>
							<form class="form-horizontal" role="form" action="users.php?id=<?php echo $user_id_to_manage; ?>" method="post">
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
											<button type="submit" name="submit_access" class="btn btn-primary">Save Changes</button>
										</div>
									</div>
								</div>
							</form>
						</div>
					</div>
					<?php } } ?>
					<!-- END SAMPLE FORM PORTLET-->
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
<!-- END PAGE LEVEL SCRIPTS -->
<script>
jQuery(document).ready(function() {    
   // initiate layout and plugins
   Metronic.init(); // init metronic core components
Layout.init(); // init current layout
QuickSidebar.init(); // init quick sidebar
Demo.init(); // init demo features
   FormSamples.init();
});
</script>

<script src="assets/admin/layout/scripts/jquery.validationEngine-en.js" type="text/javascript" charset="utf-8"></script>
<script src="assets/admin/layout/scripts/jquery.validationEngine.js" type="text/javascript" charset="utf-8"></script>
<script>
	jQuery(document).ready(function(){
		// binds form submission and fields to the validation engine
		jQuery("#user_form").validationEngine();
	});
</script>

 <!-- For Zebra Dialog Box -->
<script type="text/javascript" src="assets/admin/layout/scripts/zebra_dialog.js"></script>
<script type="text/javascript">
	$(document).ready(function() {
		 $(".del").click(function() {
			 var id = $(this).attr('id');
			 $.Zebra_Dialog('Are you sure you want to delete this?', {
						'type':     'question',
						'title':    'Delete User',
						'buttons':  ['Yes', 'No'],
						'onClose':  function(caption) {
							if(caption == 'Yes') {
								location.href = 'users.php?del_id='+id;
							}
						}
					});
		 });
	});
</script>
</body>
<!-- END BODY -->
</html>

                                            <option value="user" <?php if($user_record['user_type'] == 'user') echo  'selected="selected"'; ?>>User</option>

                                            <option value="admin" <?php if($user_record['user_type'] == 'admin') echo  'selected="selected"'; ?>>Admin</option>

                                          </select>	 

                                          <?php if(($is_error) && $error['user_type'] != '') echo '<span class="help-block">'.$error['user_type'].'</span>' ?>

                                      </div>

                                  </div>

                                  	<!--<div class="form-group <?php if(($is_error) && $error['password'] != '') echo 'has-error' ?>">

                                          <label  class="col-lg-4 control-label">Password</label>

                                          <div class="col-lg-6">

                                          	  <input type="password" id="password" name="password" class="validate[required, minSize[6]] text-input form-control" /> 	 

                                              <?php if(($is_error) && $error['password'] != '') echo '<span class="help-block">'.$error['password'].'</span>' ?>

                                          </div>

                                      </div>

                                  	<div class="form-group <?php if(($is_error) && $error['password2'] != '') echo 'has-error' ?>">

                                          <label  class="col-lg-4 control-label">Re-type Password</label>

                                          <div class="col-lg-6">

                                          	  <input type="password" id="password2" name="password2" class="validate[required, minSize[6], equals[password]] text-input form-control" />

                                              <?php if(($is_error) && $error['password2'] != '') echo '<span class="help-block">'.$error['password2'].'</span>' ?>

                                          </div>

                                      </div> -->

                                  	

                                  	<div class="form-group">

                                      <label class="col-sm-4 control-label col-lg-4" for="">Status</label>

                                      <div class="col-lg-6">

                                          <select class="form-control m-bot15" name="status" id="status">

                                          	  <option value="1" <?php if($user_record['status'] == '1') echo  'selected="selected"'; ?>>Active</option>

                    <option value="0" <?php if($user_record['status'] == '0' || $user_record['status'] == NULL) echo  'selected="selected"'; ?>>Inactive</option>

                                          </select>



                                      </div>

                                  </div>
									<div class="form-group">
                                    
									<?php 
									  if(isset($user_record['access_control'])) 
									  {
										  $access = $user_record['access_control'];
										 // echo $access;
										 // echo "<br/>";
										  $testimonial = substr($access, 0, 5);
										  $testimonial1 = explode(",",$testimonial);
										  //print_r($testimonial1);
										 // echo "testimonial=".$testimonial;
										  $categories = substr($access, 6, 5);
										  $categories1 = explode(",",$categories);
										  //echo "categories=".$categories;
										  $seasons = substr($access, 12, 5);
										  $seasons1 = explode(",",$seasons);
										  //print_r($seasons1);
										  //echo "seasons=".$seasons;
										  $behind_the_scenes = substr($access, 18, 5);
										  $behind_the_scenes1 = explode(",",$behind_the_scenes);
										  // print_r($behind_the_scenes1);
										  $web_users = substr($access, 24, 5);
										  $web_users1 = explode(",",$web_users);
										 // print_r($web_users1);
										  $access_keys = $user_record['access_control_keys'];
										  $access_keys_array = explode(",",$access_keys);
										 // print_r($access_keys_array);
										  $new[0] = $testimonial1;
										  $new[1] = $categories1;
										  $new[2] = $seasons1;
										  $new[3] = $behind_the_scenes1;
										  $new[4] = $web_users1;
										  /*echo "<pre>";
										  print_r($new);
										  echo "</pre>";*/
										  foreach($access_keys_array as $key=>$value)
										  {
											  $final_access_array[$value] = $new[$key];
										  }
										  foreach($final_access_array as $key=>$value)
										  {
											  if($key == 'testimonials')
											  {
												  $testimonial2 = $final_access_array[$key];
											  }
											  elseif($key == 'categories')
											  {
												  $categories2 = $final_access_array[$key];
											  }
											  elseif($key == 'seasons')
											  {
												  $seasons2 = $final_access_array[$key];
											  }
											  elseif($key == 'behind_the_scenes')
											  {
												  $behind_the_scenes2 = $final_access_array[$key];
											  }
											  elseif($key == 'web_users')
											  {
												  $web_users2 = $final_access_array[$key];
											  }
										  }
										  /*echo "<pre>";
										  print_r($testimonials2);
										  echo "</pre>";
										  echo "<pre>";
										  print_r($categories2);
										  echo "</pre>";
										  echo "<pre>";
										  print_r($seasons2);
										  echo "</pre>";
										  echo "<pre>";
										  print_r($behind_the_scenes2);
										  echo "</pre>";
										  echo "<pre>";
										  print_r($web_users2);
										  echo "</pre>";*/
									  } 
									?>
                                      <label  class="col-lg-4 control-label">Access Control</label>

                                      <div class="col-lg-6">
                                      	<table border="1" style="width:100%">
											<tr>
                                        		<td>Category Name</td>
                                                <td>View</td>
                                                <td>Edit</td>
                                                <td>Delete</td>
                                             </tr>
                                             <tr>
                                        		<td>Testimonials</td>
                                                <?php if(isset($testimonial2)) { 
												$i=0;
												for($i=0;$i<count($testimonial2);$i++) { //echo $i; ?>
													<td><input type='checkbox' class='form-control' id='' name='access[testimonials][<?php echo $i; ?>]' value='1' <?php if($testimonial2[$i] == 1) { ?> checked=checked <?php } ?>>
                                                    </td> 
												<?php } } else { ?>
                                                <td><input type='checkbox' class='form-control' id='' name='access[testimonials][0]' value='1'>
                                                 </td>
                                                 <td><input type='checkbox' class='form-control' id='' name='access[testimonials][1]' value='1'>
                                                 </td>
                                                 <td><input type='checkbox' class='form-control' id='' name='access[testimonials][2]' value='1'>
                                                 </td>
                                                 <?php } ?>
                                             </tr>
                                             <tr>
                                        		<td>Categories</td>
                                                <?php if(isset($categories2)) { 
												$i=0;
												for($i=0;$i<count($categories2);$i++) { //echo $i; ?>
													<td><input type='checkbox' class='form-control' id='' name='access[categories][<?php echo $i; ?>]' value='1' <?php if($categories2[$i] == 1) { ?> checked=checked <?php } ?>>
                                                    </td> 
												<?php } } else { ?>
                                                <td><input type="checkbox" class="form-control" id="" name="access[categories][0]" value="1" ></td>
                                                <td><input type="checkbox" class="form-control" id="" name="access[categories][1]" value="1" ></td>
                                                <td><input type="checkbox" class="form-control" id="" name="access[categories][2]" value="1" ></td>
                                                <?php } ?>
                                             </tr>
                                             <tr>
                                        		<td>Seasons</td>
                                                <?php if(isset($seasons2)) { 
												$i=0;
												for($i=0;$i<count($seasons2);$i++) { //echo $i; ?>
													<td><input type='checkbox' class='form-control' id='' name='access[seasons][<?php echo $i; ?>]' value='1' <?php if($seasons2[$i] == 1) { ?> checked=checked <?php } ?>>
                                                    </td> 
												<?php } } else { ?>
                                                <td><input type="checkbox" class="form-control" id="" name="access[seasons][0]" value="1" ></td>
                                                <td><input type="checkbox" class="form-control" id="" name="access[seasons][1]" value="1" ></td>
                                                <td><input type="checkbox" class="form-control" id="" name="access[seasons][2]" value="1" ></td>
                                                <?php } ?>
                                             </tr>
                                             <tr>
                                        		<td>Behind the scenes</td>
                                                 <?php if(isset($behind_the_scenes2)) { 
												$i=0;
												for($i=0;$i<count($behind_the_scenes2);$i++) { //echo $i; ?>
													<td><input type='checkbox' class='form-control' id='' name='access[behind_the_scenes][<?php echo $i; ?>]' value='1' <?php if($behind_the_scenes2[$i] == 1) { ?> checked=checked <?php } ?>>
                                                    </td> 
												<?php } } else { ?>
                                                <td><input type="checkbox" class="form-control" id="" name="access[behind_the_scenes][0]" value="1" ></td>
                                                <td><input type="checkbox" class="form-control" id="" name="access[behind_the_scenes][1]" value="1" ></td>
                                                <td><input type="checkbox" class="form-control" id="" name="access[behind_the_scenes][2]" value="1" ></td>
                                                <?php } ?>
                                             </tr>
                                             <tr>
                                        		<td>Web users</td>
                                                 <?php if(isset($web_users2)) { 
												$i=0;
												for($i=0;$i<count($web_users2);$i++) { //echo $i; ?>
													<td><input type='checkbox' class='form-control' id='' name='access[web_users][<?php echo $i; ?>]' value='1' <?php if($web_users2[$i] == 1) { ?> checked=checked <?php } ?>>
                                                    </td> 
												<?php } } else { ?>
                                                <td><input type="checkbox" class="form-control" id="" name="access[web_users][0]" value="1" ></td>
                                                <td><input type="checkbox" class="form-control" id="" name="access[web_users][1]" value="1" ></td>
                                                <td><input type="checkbox" class="form-control" id="" name="access[web_users][2]" value="1" ></td>
                                                <?php } ?>
                                             </tr>
                                        </table>
                                          

                                      </div>

                                  </div>

                                  </div>

                                  </div>

							

                            		<div class="form-body">

                                	<h3 class="form-section">User Details</h3>

                                	<div class="row">

                			     <div class="col-lg-6">

                                  	<div class="form-group <?php if(($is_error) && $error['first_name'] != '') echo 'has-error' ?>">

                                      <label  class="col-lg-4 control-label">First Name</label>

                                      <div class="col-lg-6">

                                      	  <input type="text" id="firstname" name="firstname" value="" class="validate[required, maxSize[30]] text-input form-control" />

                                          <?php if(($is_error) && $error['first_name'] != '') echo '<span class="help-block">'.$error['first_name'].'</span>' ?>

                                         

                                      </div>

                                  </div>

                                  	<div class="form-group <?php if(($is_error) && $error['last_name'] != '') echo 'has-error' ?>">

                                      <label  class="col-lg-4 control-label">Last Name</label>

                                      <div class="col-lg-6">

                                      	  <input type="text" id="lastname" name="lastname" value="" class="validate[required, maxSize[30]] text-input form-control" />	

                                          <?php if(($is_error) && $error['last_name'] != '') echo '<span class="help-block">'.$error['last_name'].'</span>' ?>

                                      </div>

                                  </div>

                                  	<div class="form-group <?php if(($is_error) && $error['email'] != '') echo 'has-error' ?>">

                                      <label  class="col-lg-4 control-label">Email</label>

                                      <div class="col-lg-6">

                                      	  <input type="text" id="email" name="email" class="validate[required,custom[email]] form-control" value="" />

                                          <?php if(($is_error) && $error['email'] != '') echo '<span class="help-block">'.$error['email'].'</span>' ?>

                                      </div>

                                  </div>

                                  

                                  	<div class="form-group">

                                      <label  class="col-lg-4 control-label">Designation</label>

                                      <div class="col-lg-6">

                                          <input type="text" class="form-control" id="designation" name="designation" value="" >

                                      </div>

                                  </div>

                                  

                                  	

                                      

                                  </div>

                                  <div class="col-lg-6">

                                  	<div class="form-group <?php if(($is_error) && $error['user_type'] != '') echo 'has-error' ?>">

                                      <label class="col-sm-4 control-label col-lg-4" for="inputSuccess">User Type</label>

                                      <div class="col-lg-6">

                                      	  <select name="user_type" id="user_type" class="validate[required] form-control m-bot15" style="padding:5px;">

                                            <option value="">Select User Type</option>

                                            <option value="user">User</option>

                                            <option value="admin">Admin</option>

                                          </select>	 

                                          <?php if(($is_error) && $error['user_type'] != '') echo '<span class="help-block">'.$error['user_type'].'</span>' ?>

                                      </div>

                                  </div>

                                  	<!--<div class="form-group <?php if(($is_error) && $error['password'] != '') echo 'has-error' ?>">

                                          <label  class="col-lg-4 control-label">Password</label>

                                          <div class="col-lg-6">

                                          	  <input type="password" id="password" name="password" class="validate[required, minSize[6]] text-input form-control" /> 	 

                                              <?php if(($is_error) && $error['password'] != '') echo '<span class="help-block">'.$error['password'].'</span>' ?>

                                          </div>

                                      </div>

                                  	<div class="form-group <?php if(($is_error) && $error['password2'] != '') echo 'has-error' ?>">

                                          <label  class="col-lg-4 control-label">Re-type Password</label>

                                          <div class="col-lg-6">

                                          	  <input type="password" id="password2" name="password2" class="validate[required, minSize[6], equals[password]] text-input form-control" />

                                              <?php if(($is_error) && $error['password2'] != '') echo '<span class="help-block">'.$error['password2'].'</span>' ?>

                                          </div>

                                      </div> -->

                                  	

                                  	<div class="form-group">

                                      <label class="col-sm-4 control-label col-lg-4" for="">Status</label>

                                      <div class="col-lg-6">

                                          <select class="form-control m-bot15" name="status" id="status">

                                          	  <option value="1">Active</option>

                    <option value="0">Inactive</option>

                                          </select>



                                      </div>

                                  </div>

                                   <div class="form-group">

                                      <label class="col-sm-4 control-label col-lg-4" for="">Auto Inform</label>

                                      <div class="col-lg-6">

                                         <div class="checkbox">

                                              <label>

                                                  <input type="checkbox" value="" name="auto_inform">

                                                  Inform user with ID & Password

                                              </label>

                                          </div>

                                      </div>

                                  </div>
                                  
                                   <div class="form-group">

                                      <label  class="col-lg-4 control-label">Access Control</label>

                                      <div class="col-lg-6">
                                      	<table border="1" style="width:100%">
											<tr>
                                        		<td>Category Name</td>
                                                <td>View</td>
                                                <td>Edit</td>
                                                <td>Delete</td>
                                             </tr>
                                             <tr>
                                        		<td>Testimonials</td>
                                                <td><input type="checkbox" class="form-control" id="" name="access[testimonials][0]" value="1" ></td>
                                                <td><input type="checkbox" class="form-control" id="" name="access[testimonials][1]" value="1" ></td>
                                                <td><input type="checkbox" class="form-control" id="" name="access[testimonials][2]" value="1" ></td>
                                             </tr>
                                             <tr>
                                        		<td>Categories</td>
                                                <td><input type="checkbox" class="form-control" id="" name="access[categories][0]" value="1" ></td>
                                                <td><input type="checkbox" class="form-control" id="" name="access[categories][1]" value="1" ></td>
                                                <td><input type="checkbox" class="form-control" id="" name="access[categories][2]" value="1" ></td>
                                             </tr>
                                             <tr>
                                        		<td>Seasons</td>
                                                <td><input type="checkbox" class="form-control" id="" name="access[seasons][0]" value="1" ></td>
                                                <td><input type="checkbox" class="form-control" id="" name="access[seasons][1]" value="1" ></td>
                                                <td><input type="checkbox" class="form-control" id="" name="access[seasons][2]" value="1" ></td>
                                             </tr>
                                             <tr>
                                        		<td>Behind the scenes</td>
                                                <td><input type="checkbox" class="form-control" id="" name="access[behind_the_scenes][0]" value="1" ></td>
                                                <td><input type="checkbox" class="form-control" id="" name="access[behind_the_scenes][1]" value="1" ></td>
                                                <td><input type="checkbox" class="form-control" id="" name="access[behind_the_scenes][2]" value="1" ></td>
                                             </tr>
                                             <tr>
                                        		<td>Web users</td>
                                                <td><input type="checkbox" class="form-control" id="" name="access[web_users][0]" value="1" ></td>
                                                <td><input type="checkbox" class="form-control" id="" name="access[web_users][1]" value="1" ></td>
                                                <td><input type="checkbox" class="form-control" id="" name="access[web_users][2]" value="1" ></td>
                                             </tr>
                                        </table>
                                          

                                      </div>

                                  </div>
									 </div>

                                  </div>

								
                                    
                                   <div class="form-actions">
                                   	<input name="submit_user" type="submit" value="Save" class="btn blue" >
			   					</div>

                                  </div>
                              </form>
							
						</div>

                        

					</div>

					<!-- END SAMPLE FORM PORTLET-->

					<!-- BEGIN SAMPLE FORM PORTLET-->

					

					<!-- END SAMPLE FORM PORTLET-->

					<!-- BEGIN SAMPLE FORM PORTLET-->

					

					<!-- END SAMPLE FORM PORTLET-->

				</div>

				

			</div>

			<div class="row">

                  <div class="col-lg-12">

                      <section class="panel">

                          <header class="panel-heading">

                              All Existing Users

                          </header>

                          <div class="panel-body">

                              <section id="unseen">

                              	

                                <table class="table table-bordered table-striped table-condensed">

                                  <thead>

                                  <tr>

                                      <th>Sr. #</th>

                                      <th>First Name</th>

                                      <th>Last Name</th>

                                      <th>Email</th>

                                      <th>User Type</th>

                                      <th>Last Login</th>

                                      <th>Login IP</th>

                                      <th>Status</th>

                                      <th>Action</th>

                                  </tr>

                                  </thead>

                                  <tbody>
                                  
                                      <?php 
                                        $index = 1;
                                        try {
                                            // Use the correct PDO object from our database class and prepare the statement
                                            $query1 = "SELECT *, DATE_FORMAT(last_login, '%d %b %Y %h:%i %p') AS last_login_formatted FROM users";
                                            $stmt = $database->db->query($query1);

                                            // Fetch all results at once into an array. This is more efficient than fetching row by row.
                                            $profiles = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                            // A foreach loop is a clean way to iterate over the results.
                                            // If there are no results, the loop will simply not run.
                                            foreach ($profiles as $profile) {
                                                // Sanitize all output with htmlspecialchars to prevent XSS attacks.
                                                $status = (int)$profile['status'] === 1 ? 'Active' : 'Inactive';
                                                $id = htmlspecialchars((string)$profile['user_id'], ENT_QUOTES, 'UTF-8');
                                                $firstName = htmlspecialchars($profile['first_name'], ENT_QUOTES, 'UTF-8');
                                                $lastName = htmlspecialchars($profile['last_name'], ENT_QUOTES, 'UTF-8');
                                                $email = htmlspecialchars($profile['email'], ENT_QUOTES, 'UTF-8');
                                                $userType = htmlspecialchars($profile['user_type'], ENT_QUOTES, 'UTF-8');
                                                $lastLogin = htmlspecialchars($profile['last_login_formatted'] ?? 'Never', ENT_QUOTES, 'UTF-8');
                                                $ipAddress = htmlspecialchars($profile['ip_address'] ?? 'N/A', ENT_QUOTES, 'UTF-8');

                                                echo '<tr>
                                                    <td>' . $index++ . '</td>
                                                    <td>' . $firstName . '</td>
                                                    <td>' . $lastName . '</td>
                                                    <td>' . $email . '</td>
                                                    <td>' . $userType . '</td>
                                                    <td>' . $lastLogin . '</td>
                                                    <td>' . $ipAddress . '</td>
                                                    <td>' . $status . '</td>
                                                    <td>
                                                        <div class="btn-group" style="margin-bottom:0px !important;">
                                                            <a class="btn red" href="#" data-toggle="dropdown" style="padding:3px 7px !important;">
                                                                <i class="icon-user"></i> Options <i class="icon-angle-down"></i>
                                                            </a>
                                                            <ul class="dropdown-menu">
                                                                <li><a href="users.php?id=' . $id . '"><i class="icon-pencil"></i>Edit</a></li>
                                                                <li><a href="javascript:void(0)" class="example36" title="' . $firstName . ' | ' . $id . '"><i class="icon-trash"></i> Delete</a></li>
                                                                <li><a href="javascript:void(0)" class="example37" title="' . $firstName . ' | ' . $id . '"><i class="icon-pencil"></i> Reset Password</a></li>
                                                            </ul>
                                                        </div>
                                                    </td>
                                                </tr>';
                                            }
                                        } catch (PDOException $e) {
                                            // It's good practice to handle potential database errors.
                                            // In a real application, you would log this error.
                                            echo '<tr><td colspan="9">Error fetching users: ' . htmlspecialchars($e->getMessage()) . '</td></tr>';
                                        }
                                      ?>
                                  
                                  </tbody>
                              </table>
                              </section>

                          </div>

                      </section>

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

<script type="text/javascript" src="assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>

<script type="text/javascript" src="assets/global/plugins/bootstrap-select/bootstrap-select.min.js"></script>

<script type="text/javascript" src="assets/global/plugins/select2/select2.min.js"></script>

<script type="text/javascript" src="assets/global/plugins/jquery-multi-select/js/jquery.multi-select.js"></script>

<!-- END PAGE LEVEL PLUGINS -->



<!-- BEGIN PAGE LEVEL SCRIPTS -->

<script src="assets/global/scripts/metronic.js" type="text/javascript"></script>

<script src="assets/admin/layout/scripts/layout.js" type="text/javascript"></script>

<script src="assets/admin/layout/scripts/demo.js" type="text/javascript"></script>

<script src="assets/admin/pages/scripts/components-pickers.js"></script>

<script src="assets/admin/pages/scripts/components-dropdowns.js"></script>

<script src="assets/admin/pages/scripts/form-samples.js"></script>

<!-- END PAGE LEVEL SCRIPTS -->

    

<script>

jQuery(document).ready(function() {   

    // initiate layout and plugins

    Metronic.init(); // init metronic core components

	Layout.init(); // init current layout

	Demo.init(); // init demo features

	ComponentsPickers.init();

	FormSamples.init();

	ComponentsDropdowns.init();

});

</script>

<!-- END JAVASCRIPTS -->

<!-- validation --> 



<script src="assets/admin/layout/scripts/jquery.validationEngine-en.js" type="text/javascript" charset="utf-8"></script> 

<script src="assets/admin/layout/scripts/jquery.validationEngine.js" type="text/javascript" charset="utf-8"></script> 

<script>

        jQuery(document).ready(function(){

			// binds form submission and fields to the validation engine

            jQuery("#user_form").validationEngine();

        });

    </script> 



<script type="text/javascript" src="assets/admin/layout/scripts/highlight.js"></script>

<script type="text/javascript" src="assets/admin/layout/scripts/zebra_dialog.js"></script>

<script type="text/javascript">

    hljs.initHighlightingOnLoad();

</script>

<script type="text/javascript">

    $(document).ready(function() {

        $('.example36').bind('click', function(e) {

            var temp = this.title;

            var arr = temp.split('|');

            //var index = temp.indexOf("|");

            //var name = temp.substring(0, index);

            //var user_id = temp.substring(index+2);

			var name = arr[0];

            var user_id = arr[1];

			//alert(user_id);



            e.preventDefault();

            $.Zebra_Dialog('<strong>Are you sure</strong>, you want to delete ' + name, {

                'type':     'question',

                'title':    'Confirmation',

                'buttons':  ['Yes', 'No'],

                'onClose':  function(caption) {

                    if(caption == 'Yes'){

                        $.ajax({ url: 'delete_user.php',

                        data: {id: user_id},

                        type: 'post',

                        success: function(output) {

                            //alert(client_name + ' Deleted Successfully');

                            $.Zebra_Dialog(name + ' Deleted Successfully', {

                            'type':     'confirmation',

                            'title':    'Confirmation',

                             'onClose':  function() {

                               location.reload();

                            }

                        });

                            

                        }

                    });



                    }

                }

            });

        });

		$('.example37').bind('click', function(e) {

            var temp = this.title;

            var arr = temp.split('|');

            //var index = temp.indexOf("|");

            //var name = temp.substring(0, index);

            //var user_id = temp.substring(index+2);

			var name = arr[0];

            var user_id = arr[1];

			//alert(user_id);



            e.preventDefault();

            $.Zebra_Dialog('<strong>Are you sure</strong>, you want to reset password for ' + name, {

                'type':     'question',

                'title':    'Confirmation',

                'buttons':  ['Yes', 'No'],

                'onClose':  function(caption) {

                    if(caption == 'Yes'){

                        $.ajax({ url: 'reset_user_pass.php',

                        data: {id: user_id},

                        type: 'post',

                        success: function(output) {

                            //alert(client_name + ' Deleted Successfully');

                            $.Zebra_Dialog('Password of' + name + ' Reset Successfully. New Password is ' + output, {

                            'type':     'confirmation',

                            'title':    'Confirmation',

                             'onClose':  function() {

                               //location.reload();

                            }

                        });

                            

                        }

                    });



                    }

                }

            });

        });

    });

</script> 



<?php 

unset($_SESSION['msg_type']);

unset($_SESSION['msg']);

?>

</body>

<!-- END BODY -->

</html>