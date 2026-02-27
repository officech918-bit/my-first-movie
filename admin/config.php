<?php
	date_default_timezone_set('Asia/Calcutta');
	$date = date('d-m-Y');
	$time = date("d-m-Y H:i", time());

	//get class files
	include('inc/requires.php');
	
	//create objects
	$database = new MySQLDB();
	$user = new visitor();
	$menu = 'inc/left-menu-user.php';
	
	//check if the user is not logged in
	if(!$user->check_session())
	//if($user->check_session())
	{	
		header("location: index.php"); 
		exit();
	} else if ($_SESSION['user_type'] == 'webmaster'){
		$user = new webmaster();
		$menu = 'inc/left-menu-webmaster.php';	
	} else if ($_SESSION['user_type'] == 'admin'){
		$user = new admin();
		$menu = 'inc/left-menu-admin.php';
		header("location: dashboard.php"); 
		exit();
	} else {
		$user = new user();
		header("location: dashboard.php"); 
		exit();
	}
	
	if (empty($_SESSION['csrf_token'])) {
		$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
	}
	$csrf_token = $_SESSION['csrf_token'];

	$first_name = $user->get_wm_first_name();
	$last_name = $user->get_wm_last_name();
	$email = $user->get_wm_email();
	$sitename = $user->get_wm_sitename();
	$sub_location = $user->get_wm_sub_location();
	$admin_location = $user->get_wm_admin_location();
	

	$path = "";
	$direct_path = "";
	$admin_path = "";
	if($sub_location != ""){
		$path = $sitename.'/'.$sub_location.'/';
		$direct_path = $_SERVER['DOCUMENT_ROOT'].'/'.$sub_location.'/';
		$admin_path = $sitename.'/'.$sub_location.'/'.$admin_location.'/';
	}
	else {
		$path = $sitename.'/';
		$direct_path = $_SERVER['DOCUMENT_ROOT'].'/';
		$admin_path = $sitename.'/'.$admin_location.'/';
	}

	if(isset($_POST['submit_config'])) {
		if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
			die('CSRF token validation failed.');
		}
		$first_name = $_POST['firstname'];
		$last_name = $_POST['lastname'];
		$email = $_POST['email'];
		$password = $_POST['password'];
		$password2 = $_POST['password2'];
		$sitename = $_POST['sitename'];
		$sub_location = $_POST['sub_location'];
		$admin_location = $_POST['admin_location'];
			
		$salt = $user->generateSalt();
		$hash = $user->generateHash($password, $salt);
		
		//create data dimentional array
		$data = array (
			   array("salt",$salt),
			   array("first_name",$first_name),
			   array("last_name",$last_name), 
			   array("email",$email),
			   array("password",$hash),
			   array("sitename",$sitename),
		   	   array("sub_location",$sub_location),
		   	   array("admin_location",$admin_location)
		    );

		
		$col = 0;
		$count = count($data);
		for ($row = 0; $row < $count; $row++)
		{
			$variable = $data[$row][$col];
			$value = $data[$row][$col+1];
			
			//check if variable is already exist
			// Use prepared statements for security
			$result = $database->get_record_by_ID('configs', 'variable', $variable);
			
			if($result) {
				// Update existing record using prepared statement
				$update_data = array('value' => $value);
				$database->update_array('configs', 'variable', $variable, $update_data);
			} else {
				// Insert new record using prepared statement
				$insert_data = array(
					'variable' => $variable,
					'value' => $value
				);
				$database->insert_array('configs', $insert_data);
			}

		}
		header("location: config.php"); 
		exit();
	}
	
	if(isset($_POST['update_subscription_files'])) {
		$subscription_path = $direct_path.'esubscriptions/';

		//get the list of category slugs and copy to those folders
		$result = $database->query("SELECT * FROM subscriptions");
		if($result) {
			while($res_data = $result->fetch_assoc()) {
				$slug = $res_data['slug'];
				
				//check if slug exists or not, if not create
				if(!file_exists($subscription_path.$slug)) {
					mkdir($subscription_path.$slug, 0777, true);
				}
				$copy_from = $direct_path.'required-issue-detail-page/index.php';
				if(file_exists($copy_from)) {
					copy($copy_from, $subscription_path.$slug."/index.php");
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
<!-- BEGIN HEAD -->
<head>
<meta charset="utf-8"/>
<title>Configs</title>
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
			Site Configs 
			</h3>
			<div class="page-bar">
				<ul class="page-breadcrumb">
					<li>
						<i class="fa fa-home"></i>
						<a href="dashboard.php">dashboard</a>
						<i class="fa fa-angle-right"></i>
					</li>
					<li>
						<a href="#">Config Settings</a>
						<i class="fa fa-angle-right"></i>
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
								<i class="fa fa-gift"></i> Add/Edit Site Configs
							</div>
						</div>
                        
						<div class="portlet-body form">
                        	<h3> Site Settings</h3>
                              <form class="form-horizontal" role="form" action="" method="post" enctype="multipart/form-data" id="site_config" name="site_config">
                              	<div class="form-body">
                                  <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>" />
                                  <div class="form-group">
                                      <label  class="col-lg-2 control-label">First Name</label>
                                      <div class="col-lg-6">
                                          <input type="text" class="validate[required, maxSize[30]] text-input form-control" id="firstname" name="firstname" value="<?php echo $first_name ?>" />
                                      </div>
                                  </div>
                                  <div class="form-group">
                                      <label  class="col-lg-2 control-label">Last Name</label>
                                      <div class="col-lg-6">
                                          <input type="text" class="validate[required, maxSize[30]] text-input form-control" id="lastname" name="lastname" value="<?php echo $last_name ?>">
                                      </div>
                                  </div>
                                  <div class="form-group">
                                      <label  class="col-lg-2 control-label">Email</label>
                                      <div class="col-lg-6">
                                          <input type="text" class="validate[required,custom[email]] text-input form-control" id="email" name="email" value="<?php echo $email ?>">
                                      </div>
                                  </div>
                                  <div class="form-group">
                                         <label  class="col-lg-2 control-label">Password</label>
                                         <div class="col-lg-6">
                                              <input type="password" class="validate[required, minSize[6]] text-input form-control" id="password" name="password" />
                                        </div>
                                  </div>
                                  <div class="form-group">
                                         <label  class="col-lg-2 control-label">Retype Password</label>
                                         <div class="col-lg-6">
                                              <input type="password" class="validate[required, minSize[6], equals[password]] text-input form-control" id="password2" name="password2" />
                                        </div>
                                  </div>
                                  <div class="form-group">
                                      <label  class="col-lg-2 control-label">Site Name</label>
                                      <div class="col-lg-6">
                                          <input type="text" class="form-control" id="sitename" name="sitename" value="<?php echo $sitename ?>" />
                                      </div>
                                  </div>
                                  <div class="form-group">
                                      <label  class="col-lg-2 control-label">Sub-folder</label>
                                      <div class="col-lg-6">
                                          <input type="text" class="form-control" id="sub_location" name="sub_location" value="<?php echo $sub_location ?>" />
                                      </div>
                                  </div>
                                  <div class="form-group">
                                      <label  class="col-lg-2 control-label">Admin Location</label>
                                      <div class="col-lg-6">
                                          <input type="text" class="form-control" id="admin_location" name="admin_location" value="<?php echo $admin_location ?>" />
                                      </div>
                                  </div>
                                  <div class="form-actions">
                                   	<input name="submit_config" type="submit" value=" Save " class="btn blue" >
								</div>
                                  </div>
                                  
                              </form>
                              
                              
            <form action="" method="post" class="form-horizontal" enctype="multipart/form-data" name="update_catagory_equipment" style="display:none;">
            				<div class="form-body" style="display:none;">
                            	<h3>For Developer only</h3>
                            		<div class="form-actions">
                                   	<input name="update_subscription_files" type="submit" value=" Update Subscribe Files " class="btn blue" >
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
</body>
<!-- END BODY -->
</html>