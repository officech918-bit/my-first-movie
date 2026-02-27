<?php
	require_once('inc/requires.php');
	date_default_timezone_set('Asia/Kolkata');
	$date = date("Y-m-d H:i:s", time());
	
	//create objects
	$database = new MySQLDB();
	$user = new visitor();
	$is_edit = false;
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
	
	
	if(isset($_POST['submit_region'])) {
		if(isset($_GET['id'])) {$is_edit = true; $id = $_GET['id'];}
		
		$region_name = $_POST['region_name'];
		$status = $_POST['status'];
		
		//validate
		if(!valid::hasValue($region_name)) { $is_error = true; $error['region_name'] = "Region Name can not be empty"; }
		else if(valid::isTooLong($first_name, 100)) { $is_error = true; $error['region_name'] = "Region Name can not more than 100 character"; }

			

		if(!$is_error) {
			$created_by = $_SESSION['user_id'];
			$modified_by = $_SESSION['user_id'];
			
			if($is_edit) {
				$query = "UPDATE regions SET status='$status', region_name='$region_name', modified_by='$modified_by' WHERE id='$id'";
				//echo $query;
				$result = mysql_query($query);
				if(!$result) die("Unable to insert data: ".mysql_error());
				else { 				
					$_SESSION['msg_type'] = 1;
					$_SESSION['msg'] = 'Details were updated.';
						
					header("location: regions.php?id=".$id); 
					exit();
				 }			
			} 
			else {
	
				$query = "INSERT INTO regions(region_name, status, created, created_by) VALUES ('$region_name', '$status', '$date', '$created_by')"; 
				
				//echo $query;
				$result = mysql_query($query);
				if(!$result) die("Unable to insert data: ".mysql_error());
				else { 
					
					$_SESSION['msg_type'] = 1;
					$_SESSION['msg'] = 'Region '.$region_name.'Added Successfully';
						
					header("location: regions.php"); 
					exit();
				 }	
				
			}
			
			
		}	

	}
	
	if(isset($_GET['id'])) {	
		$id = $_GET['id'];
		$query2 = "SELECT * FROM regions WHERE id = '$id'";
		$result2 = mysql_query($query2);
		$rows = mysql_num_rows($result2);
		
		if($rows > 0) {
			$is_edit = true;
			$data = mysql_fetch_assoc($result2);
			
			$status = $data['status'];
			$region_name = $data['region_name'];
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
<title>Regions</title>
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
			Manage Regions <a id="sample_editable_1_new" href="regions.php" class="btn green"> Add New <i class="fa fa-plus"></i></a> 
			</h3>
			<div class="page-bar">
				<ul class="page-breadcrumb">
					<li>
						<i class="fa fa-home"></i>
						<a href="dashboard.php">Dashboard</a>
						<i class="fa fa-angle-right"></i>
					</li>
					<li>
						<a href="#">Manage Regions</a>
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
								<i class="fa fa-gift"></i> Add/Edit Regions
							</div>
							<div class="tools">
								<a href="" class="collapse">
								</a>
							</div>
						</div>
                        
						<div class="portlet-body form">
                        	<?php if(isset($_SESSION['msg'])) echo '<div class="alert alert-success"><strong>Success!</strong> '.$_SESSION['msg'].'</div>'; ?>
                            <form class="form-horizontal" role="form" action="" method="post" enctype="multipart/form-data" id="user_form" name="user_form">
                            	<div class="form-body">
                                	<h3 class="form-section">Regions Details</h3>
                                	<div class="row">
                			     <div class="col-lg-6">
                                  	<div class="form-group <?php if(($is_error) && $error['region_name'] != '') echo 'has-error' ?>">
                                      <label  class="col-lg-3 control-label col-lg-3">Region Name</label>
                                      <div class="col-lg-9">
                                      	  <input type="text" id="region_name" name="region_name" value="<?php echo $region_name ?>" class="validate[required, maxSize[100]] text-input form-control" />
                                          <?php if(($is_error) && $error['region_name'] != '') echo '<span class="help-block">'.$error['region_name'].'</span>' ?>
                                         
                                      </div>
                                  </div>
                                  </div>
                                  <div class="col-lg-6">                                	
                                  	<div class="form-group">
                                      <label class="col-sm-3 control-label col-lg-3" for="">Status</label>
                                      <div class="col-lg-9">
                                          <select class="form-control m-bot15" name="status" id="status">
                                          	  <option value="1" <?php if($status == '1') echo  'selected="selected"'; ?>>Active</option>
                    <option value="0" <?php if($status == '0' || $status == NULL) echo  'selected="selected"'; ?>>Inactive</option>
                                          </select>

                                      </div>
                                  </div>
                                  </div>
                                  </div>
                                   <div class="form-actions">
                                   	<input name="submit_region" type="submit" value="Save" class="btn blue" >
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
                  
                  <div class="portlet box red">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-cogs"></i> All Existing Regions
							</div>
							<div class="tools">
								<a href="javascript:;" class="collapse">
								</a>
							</div>
						</div>
						<div class="portlet-body">
                        	<table class="table table-bordered table-striped table-condensed">
                                  <thead>
                                  <tr>
                                      <th>Sr. #</th>
                                      <th>Region Name</th>
                                      <th>Created On</th>
                                      <th>Status</th>
                                      <th>Action</th>
                                  </tr>
                                  </thead>
                                  <tbody>
                                  
                                      <?php 
					$index = 1;
					$query1 = "SELECT *, DATE_FORMAT(created,'%d %b %Y %h:%i %p') AS created FROM regions";
					$result1 = mysql_query($query1);
					$num_rows = mysql_num_rows($result1);
					if($num_rows > 0) {
						while($profiles = mysql_fetch_assoc($result1)) {
							$status = "";
							$user_type2show = "";
							$temp = $profiles['status'];
							if($temp == "1") $status = "Active"; else $status = "Inactive";
							
							echo '<tr>
										<td>'.$index.'</td>
                                      <td>'.$profiles['region_name'].'</td>
                                      <td>'.$profiles['created'].'</td>
                                      <td>'.$status.'</td>
                                      <td>
									  <div class="btn-group" style="margin-bottom:0px !important;"> <a class="btn red" href="#" data-toggle="dropdown" style="padding:3px 7px !important;"> <i class="icon-user"></i> Options <i class="icon-angle-down"></i> </a>
					<ul class="dropdown-menu">
					  <li><a href="regions.php?id='.$profiles['id'].'"><i class="icon-pencil"></i>Edit</a></li>
					  <li><a href="javascript:void(0)" class="example36" title="'.$profiles['region_name'].' | '.$profiles['id'].'"><i class="icon-trash"></i> Delete</a></li>
					</ul>
				  </div>
                                      </td>
							
                                    </tr>
                                ';
							$index++;
						} 
					}
					echo '</tbody></table>';
				
				?>
                                  
                                  </tbody>
                              </table>
                        </div>
                        </div>
                        
                      
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
                        $.ajax({ url: 'delete_region.php',
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