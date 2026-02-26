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
	$is_edit = false;
	$menu = 'inc/left-menu-user.php';
	$is_error = false;
	$error = array();
	
	// Initialize form variables to prevent "undefined variable" warnings
	$first_name = '';
	$last_name = '';
	$contact = '';
	$email = '';
	$company = '';
	$address = '';
	$city = '';
	$state = '';
	$zip = '';
	$country = 'India'; // Default country
	$status = '1'; // Default to active
	$email_ealier = '';
	$user_data = []; // To hold user data if editing

	//check if the user is not logged in
	if (empty($_SESSION['csrf_token'])) {
		$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
	}

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
		// 1. CSRF Token Validation
		if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
			// CSRF token is invalid, handle the error appropriately
			$_SESSION["errormsg"] = "CSRF token validation failed. Please try again.";
			header("location: edit-web-users.php" . (isset($_GET['id']) ? "?id=".$_GET['id'] : ""));
			exit();
		}
		// 1. CSRF Token Validation
		if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
			// CSRF token is invalid, handle the error appropriately
			$_SESSION["errormsg"] = "CSRF token validation failed. Please try again.";
			header("location: edit-web-users.php" . (isset($_GET['id']) ? "?id=".$_GET['id'] : ""));
			exit();
		}

		if(isset($_GET['id'])) {$is_edit = true;}

		// Sanitize and retrieve POST data
		$email_ealier = $_POST['email_ealier'];
		$first_name = $_POST['first_name'];
		$last_name = $_POST['last_name'];
		$email = $_POST['email'];
		$contact = $_POST['contact'];
		$company = $_POST['company'];
		$address = $_POST['address'];
		$city = $_POST['city'];
		$state = $_POST['state'];
		$zip = $_POST['zip'];
		$country = $_POST['country'];
		$region = $_POST['region'] ?? ''; // Use null coalescing for optional fields
		$status = $_POST['status'];
		
		// Validation logic (currently commented out) should be re-enabled and checked here
		// if($is_error) { ... }

		if(!$is_error) {
			if($is_edit && isset($_GET['id'])) {
				$user_id = $_GET['id'];

				// Check if new email id exists in the database
				if($email_ealier != $email) {
					$stmt = $database->db->prepare("SELECT uid FROM web_users WHERE email = ?");
					$stmt->bind_param("s", $email);
					$stmt->execute();
					$stmt->store_result();
					if($stmt->num_rows > 0) {
						$is_error = true; 
						$error['email'] = "User already exist with this email id!";
					}
					$stmt->close();
				}
				
				if(!$is_error) {
					// Use prepared statement for UPDATE to prevent SQL injection
					$query = "UPDATE web_users SET first_name=?, last_name=?, contact=?, email=?, company=?, address=?, city=?, state=?, zip=?, country=?, region=?, status=? WHERE uid=?";
					$stmt = $database->db->prepare($query);
					$stmt->bind_param("sssssssssssss", $first_name, $last_name, $contact, $email, $company, $address, $city, $state, $zip, $country, $region, $status, $user_id);
					
					if($stmt->execute()) {
						$_SESSION["errormsg"] = "Successfully Updated";
					} else {
						$_SESSION["errormsg"] = "Failed To Update: " . $stmt->error;
					}
					$stmt->close();
					header("location: edit-web-users.php?id=".$user_id.""); 
					exit();
				}
			}
			// Note: Add logic for creating a NEW user here if this form supports it
		}
	}
	
	// If editing, fetch the user's data
	if(isset($_GET['id'])) { 	
		$is_edit = true;
		$user_id = $_GET['id'];
		$stmt = $database->db->prepare("SELECT * FROM web_users WHERE uid = ?");
		$stmt->bind_param("s", $user_id);
		$stmt->execute();
		$result = $stmt->get_result();
		
		if($result->num_rows > 0) {
			$user_data = $result->fetch_assoc();
			$first_name = $user_data['first_name'];
			$last_name = $user_data['last_name'];
			$contact = $user_data['contact'];
			$company = $user_data['company'];
			$address = $user_data['address'];
			$email = $user_data['email'];
			$email_ealier = $user_data['email']; // Set email_ealier when editing
			$city = $user_data['city'];
			$state = $user_data['state'];
			$zip = $user_data['zip'];
			$country = $user_data['country'];
			$status = $user_data['status'];
		}
		$stmt->close();
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
<title>Edit Users</title>
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
<div class="clearfix"> </div>
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
      <h3 class="page-title"> Manage Web-Users <small></small> </h3>
      <div class="page-bar">
        <ul class="page-breadcrumb">
          <li> <i class="fa fa-home"></i> <a href="dashboard.php">Dashboard</a> <i class="fa fa-angle-right"></i> </li>
          <li> <a href="web-users.php">Web Users</a> </li>
        </ul>
      </div>
      <!-- END PAGE HEADER--> 
      <!-- BEGIN PAGE CONTENT-->
      <div class="row">
        <div class="col-md-12"> 
          <!-- BEGIN SAMPLE FORM PORTLET-->
          <div class="portlet box blue">
            <?php if(isset($_SESSION['errormsg']) && ($_SESSION['errormsg'] != ''))
							  { echo "<div id='alert'><div class=' alert alert-block alert-info fade in center'>".$_SESSION["errormsg"]."</div></div>" ; } ?>
            <?php unset($_SESSION['errormsg']); ?>
            <div class="portlet-title">
              <div class="caption"> <i class="fa fa-gift"></i> Add/Edit Web User </div>
              <div class="tools"> <a href="" class="collapse"> </a> </div>
            </div>
            <div class="portlet-body form">
              <form class="form-horizontal" role="form" action="" method="post" enctype="multipart/form-data" id="user_form" name="user_form">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                <div class="form-body">
                  <h3 class="form-section">User Details</h3>
                  <div class="row">
                    <div class="col-lg-6">
                      <div class="form-group">
                        <label  class="col-lg-4 control-label">First Name</label>
                        <div class="col-lg-6">
                          <input type="text" id="first_name" name="first_name" value="<?php echo $first_name ?>" class="validate[required, maxSize[30]] text-input form-control" />
                        </div>
                      </div>
                      <div class="form-group">
                        <label  class="col-lg-4 control-label">Last Name</label>
                        <div class="col-lg-6">
                          <input type="text" id="last_name" name="last_name" value="<?php echo $last_name ?>" class="validate[required, maxSize[30]] text-input form-control" />
                        </div>
                      </div>
                      <div class="form-group">
                        <label  class="col-lg-4 control-label">Contact Number</label>
                        <div class="col-lg-6">
                          <input type="text" id="contact" name="contact" class="validate[required] form-control" value="<?php echo $contact ?>" />
                        </div>
                      </div>
                      <div class="form-group <?php if($is_error && ($error['email'] != '')) echo 'has-error' ?>">
                        <label  class="col-lg-4 control-label">Email </label>
                        <div class="col-lg-6">
                          <input type="text" id="email" name="email" class="validate[required] form-control" value="<?php echo $email ?>" />
                          <input type="hidden" id="email_ealier" name="email_ealier" value="<?php echo $email ?>" />
                          <span class="help-block">
                          <?php if($is_error && ($error['email'] != ''))echo $error['email']; ?>
                          </span> </div>
                      </div>
                      <div class="form-group">
                        <label class="col-lg-4 control-label">Status <span class="require">*</span></label>
                        <div class="col-lg-6">
                          <select name="status" id="status" class="form-control">
                            <option value="0" <?php if($status == '0') echo 'selected';  ?>>Inactive</option>
                            <option value="1" <?php if($status == '1') echo 'selected';  ?>>Active</option>
                          </select>
                        </div>
                      </div>
                    </div>
                    <div class="col-lg-6">
                      <div class="form-group">
                        <label  class="col-lg-4 control-label">Company Name</label>
                        <div class="col-lg-6">
                          <input type="text" id="company" name="company" class="form-control " value="<?php echo $company ?>">
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="col-lg-4 control-label">Address </label>
                        <div class="col-lg-6">
                          <input type="text" name="address" id="address" class="form-control "  value="<?php echo $address ?>" />
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="col-lg-4 control-label">Country</label>
                        <div class="col-lg-6">
                          <select class="form-control" name="country" id="country" style="color:#333;">
                            <option value="">Select a Country</option>
                            <?php
                                        // The $country variable is set from the user data fetched earlier or defaulted.
                                        $stmt = $database->db->prepare("SELECT CountryID, CountryName FROM countries ORDER BY CountryName");
                                        $stmt->execute();
                                        $result_countries = $stmt->get_result();
                                        while($country_row = $result_countries->fetch_assoc()) {
                                            $selected = ($country == $country_row['CountryName']) ? 'selected' : '';
                                            echo '<option value="'.htmlspecialchars($country_row['CountryName']).'" data-id="'.$country_row['CountryID'].'" '.$selected.'>'.htmlspecialchars($country_row['CountryName']).'</option>';
                                        }
                                        $stmt->close();
                                      ?>
                          </select>
                          <!-- <input type="text" name="country" id="country" class="form-control validate[required]"  value="<?php echo $country ?>" /> --> 
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="col-lg-4 control-label">Region/State</label>
                        <div class="col-lg-6">
                          <select class="form-control" name="state" id="state" style="color:#333;" >
                            <option value=" ">Select State</option>
                            <?php
                                        if($country != '') {
                                            $stmt = $database->db->prepare("SELECT CountryID FROM countries WHERE CountryName = ?");
                                            $stmt->bind_param("s", $country);
                                            $stmt->execute();
                                            $result_country_id = $stmt->get_result();
                                            if($result_country_id->num_rows > 0) {
                                                $country_arr = $result_country_id->fetch_assoc();
                                                $country_id = $country_arr['CountryID'];

                                                $stmt_states = $database->db->prepare("SELECT StateID, StateName FROM states WHERE CountryID = ? ORDER BY StateName");
                                                $stmt_states->bind_param("i", $country_id);
                                                $stmt_states->execute();
                                                $result_states = $stmt_states->get_result();
                                                while($state_row = $result_states->fetch_assoc()) {
                                                    $selected = ($state == $state_row['StateName']) ? 'selected' : '';
                                                    echo '<option value="'.htmlspecialchars($state_row['StateName']).'" data-id="'.$state_row['StateID'].'" '.$selected.'>'.htmlspecialchars($state_row['StateName']).'</option>';
                                                }
                                                $stmt_states->close();
                                            }
                                            $stmt->close();
                                        }
                                    ?>
                          </select>
                          <input type="text" name="state" id="state2" class="form-control"  value="<?php  echo $state ?>" <?php if($country != 'India') echo ''; else echo 'disabled';  ?> />
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="col-lg-4 control-label">City</label>
                        <div class="col-lg-6">
                          <select class="form-control" name="city" id="city" style="color:#333;" >
                            <option value="">Select City</option>
                            <?php
                                        if($state != '') {
                                            $stmt = $database->db->prepare("SELECT StateID FROM states WHERE StateName = ?");
                                            $stmt->bind_param("s", $state);
                                            $stmt->execute();
                                            $result_state_id = $stmt->get_result();
                                            if($result_state_id->num_rows > 0) {
                                                $state_arr = $result_state_id->fetch_assoc();
                                                $state_id = $state_arr['StateID'];

                                                $stmt_cities = $database->db->prepare("SELECT CityID, CityName FROM cities WHERE StateID = ? ORDER BY CityName");
                                                $stmt_cities->bind_param("i", $state_id);
                                                $stmt_cities->execute();
                                                $result_cities = $stmt_cities->get_result();
                                                while($city_row = $result_cities->fetch_assoc()) {
                                                    $selected = ($city == $city_row['CityName']) ? 'selected' : '';
                                                    echo '<option value="'.htmlspecialchars($city_row['CityName']).'" data-id="'.$city_row['CityID'].'" '.$selected.'>'.htmlspecialchars($city_row['CityName']).'</option>';
                                                }
                                                $stmt_cities->close();
                                            }
                                            $stmt->close();
                                        }
                                    ?>
                          </select>
                          <input type="text" name="city" id="city2" class="form-control"  value="<?php  echo $city ?>" <?php if($country != 'India') echo ''; else echo 'disabled';  ?> />
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="col-lg-4 control-label">Zip Code/Post Code</label>
                        <div class="col-lg-6">
                          <input type="text" name="zip" id="zip" class="form-control"  value="<?php echo $zip ?>" />
                        </div>
                      </div>
                      
                      <!-- 
                                  	<div class="form-group">
                                          <label  class="col-lg-4 control-label">Password</label>
                                          <div class="col-lg-6">
                                          	  <input type="password" id="password" name="password" class="validate[required, minSize[6]] text-input form-control" /> 	 
                                             
                                          </div>
                                      </div>
                                  	<div class="form-group">
                                          <label  class="col-lg-4 control-label">Re-type Password</label>
                                          <div class="col-lg-6">
                                          	  <input type="password" id="password2" name="password2" class="validate[required, minSize[6], equals[password]] text-input form-control" />
                                              
                                          </div>
                                      </div> --> 
                      
                      <!-- <div class="form-group">
                                      <label class="col-sm-4 control-label col-lg-4" for="">Status</label>
                                      <div class="col-lg-6">
                                          <select class="form-control m-bot15" name="status" id="status">
                                          	  <option value="1" <?php if($status == '1') echo  'selected="selected"'; ?>>Active</option>
                    <option value="2" <?php if($status == '0' || $status == NULL) echo  'selected="selected"'; ?>>Inactive</option>
                                          </select>

                                      </div>
                                  </div> --> 
                      <!-- <div class="form-group">
                                      <label class="col-sm-4 control-label col-lg-4" for="user_right">User Rights</label>
                                      <div class="col-lg-6">
                                          <select class="form-control m-bot15" name="user_right" id="user_right">
                                              <option value="1" <?php if($user_right == '1') echo  'selected="selected"'; ?>>Read Only</option>
                                              <option value="2" <?php if($user_right == '2') echo  'selected="selected"'; ?>>Read / Edit Only</option>
                                              <option value="3" <?php if($user_right == '3') echo  'selected="selected"'; ?>>Read / Edit / Delete</option>
                                          </select>

                                      </div>
                                  </div> --> 
                      <!-- <div class="form-group">
                                      <label class="col-sm-4 control-label col-lg-4" for="">Auto Inform</label>
                                      <div class="col-lg-6">
                                         <div class="checkbox">
                                              <label>
                                                  <input type="checkbox" value="" name="auto_inform">
                                                  Inform user with ID & Password
                                              </label>
                                          </div>
                                      </div>
                                  </div> --> 
                      
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
    });
</script> 
<script type="text/javascript">
	//When DOM loaded we attach click event to button
	$(document).ready(function() {
		//disable state & city if country is india
		var country_name = $('#country').val();
		if(country_name != 'India') {
			//toggle state field
			$('#state').prop('disabled', 'disabled');
			$('#state').css('display','none');
			
			$('#city').prop('disabled', 'disabled');
			$('#city').css('display','none');
			
			$("#state2").removeAttr("disabled");
			$('#state2').css('display','block');
			
			$("#city2").removeAttr("disabled");
			$('#city2').css('display','block');
		} else {
			$("#state").removeAttr("disabled");
			$('#state').css('display','block');
			
			$("#city").removeAttr("disabled");
			$('#city').css('display','block');
			
			$('#state2').prop('disabled', 'disabled');
			$('#state2').css('display','none');
			
			$('#city2').prop('disabled', 'disabled');
			$('#city2').css('display','none');
			
		}
		
	});
	$( "#country" ).live( "change", function() {
		var country_id = $(this).children('option:selected').data('id');
		var country_name = $(this).val();
		var state_name = '<?php echo $state; ?>';
		if(country_id && (country_name == 'India')) {
			//toggle state field
			$("#state").removeAttr("disabled");
			$('#state').css('display','block');
			
			$('#state2').prop('disabled', 'disabled');
			$('#state2').css('display','none');
			
			$("#city").removeAttr("disabled");
			$('#city').css('display','block');
			
			$('#city2').prop('disabled', 'disabled');
			$('#city2').css('display','none');
			
			$.ajax({
			'url' : '../members/update_states.php',
			'type' : 'POST', //the way you want to send data to your URL
			'data' : {'country_id' : country_id, 'state_name' : state_name},
			'success' : function(data){ 
				if(data){
					$('#state').html(data);
					}
				}
			});
			//$('#state').trigger('change');
		}
		else {
			$('#state').html('<option value="State">Select State</option>');
			$('#state').prop('disabled', 'disabled');
			$('#state').css('display','none');
			
			$("#state2").removeAttr("disabled");
			$('#state2').css('display','block');
			$('#state').trigger('change');
		}
		

	});
	
	$( "#state" ).live( "change", function() {
		var state_id = $(this).children('option:selected').data('id');
		var state_name = $(this).val();
		var city_name = '<?php echo $city; ?>';
		//alert(state_id + state_name + 'ok');
		if(state_id && ($('#country').val() == 'India')) {
			//toggle city field
			$("#city").removeAttr("disabled");
			$('#city').css('display','block');
			
			$('#city2').prop('disabled', 'disabled');
			$('#city2').css('display','none');
			
			$.ajax({
			'url' : '../members/update_cities.php',
			'type' : 'POST', //the way you want to send data to your URL
			'data' : {'state_id' : state_id, 'city_name' : city_name},
			'success' : function(data){ 
				if(data){
					$('#city').html(data);
					}
				}
			});
		}
		else {
			$('#city').html('<option value="City">Select City</option>');
			$('#city').prop('disabled', 'disabled');
			$('#city').css('display','none');
			
			$("#city2").removeAttr("disabled");
			$('#city2').css('display','block');
		}
	});
</script>
</body>
<!-- END BODY -->
</html>