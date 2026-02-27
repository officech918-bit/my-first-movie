<?php
	require_once('inc/requires.php');
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];
	date_default_timezone_set('Asia/Kolkata');
	$date = date("Y-m-d H:i:s", time());
	
	//create objects
	$database = new MySQLDB();
	$user = new visitor();
	$is_edit = false;
	$is_edit_price = false;
	if(isset($_GET['price_id'])) {$is_edit_price = true;}
	$is_image = false;
	$is_pdf_zip = false;
	$is_new_slug = false;
	$active = 'subscription';
	if(isset($_GET['price_active'])) $active = 'pricing';
	$error = array();
	$msg = '';
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
	

	if(isset($_POST['submit_file'])) {
		if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
			die('Invalid CSRF token');
		}
		
		//get the id from url if edit mode
		if(isset($_GET['id'])) {$is_edit = true; $id=$_GET['id'];}
		
		$title = $_POST['title'];
		$pdf = $_POST['pdf'];
		$status = $_POST['status'];		
		$regions = $_POST['regions'];

		
		//validate
		if(!valid::hasValue($title)) { $is_error = true; $error['title'] = "Title can not be empty"; }
		else if(valid::isTooLong($title, 100)) { $is_error = true; $error['title'] = "Title can not more than 100 character"; }
		
		if(count($regions) == 0) { $is_error = true; $error['regions'] = "Region can not be empty"; }
		
			
		if((strlen($_FILES['pdf']['name']) > 0)) {
			$is_pdf_zip = true; 
				//the image -> variables
	    		$file_type = $_FILES['pdf']['type'];
        		$file_name = stripslashes($_FILES['pdf']['name']);
        		$file_size = $_FILES['pdf']['size'];
        		$file_tmp = $_FILES['pdf']['tmp_name'];
				
				//get extention
				$extension = strtolower($user->getExtension($file_name));
				//echo $extension;
				
				//validate image credentials PDF, Doc, Docx, XLS, XLSX, PPTX, PPT, Zip
				$maxSize = 20000*1024;
				if (($extension != 'pdf') && ($extension != 'doc') && ($extension != 'docx') && ($extension != 'xls') && ($extension != 'xlsx') && ($extension != 'ppt') && ($extension != 'pptx') && ($extension != 'zip')) {
					$is_error = true;
					$error['pdf'] = "Invalid File Format: only PDF, Doc, Docx, XLS, XLSX, PPTX, PPT, Zip formats is allowed";
				} elseif($file_size > $maxSize) {
					$is_error = true;
					$error['pdf'] = "Maximum file size allowed is 20MB";
				}
				
				if(!$is_error) {
					//create folder for user ads
					$zip_name = $file_name;
					
					$uploadPath = "";
					if($sub_location != "") { $uploadPath = $_SERVER['DOCUMENT_ROOT'].'/'.$sub_location.'/members/docs/';}
					else { $uploadPath = $_SERVER['DOCUMENT_ROOT'].'/members/docs/'; }
					$linkPath = 'members/docs/';
					$pdf_link = $linkPath.$zip_name;

				
					if(!file_exists($uploadPath)) {mkdir($uploadPath, 0777);}
					
					//delete the file if exist
					if(file_exists($uploadPath.$zip_name)) { 
						unlink($uploadPath.$zip_name);
					}

					
					//upload the image
					$copied = move_uploaded_file($_FILES['pdf']['tmp_name'], $uploadPath.$zip_name);
					if(!$copied) {
						$is_error = true;
						$error['pdf'] = "Error occured while uploading pdf file!!!";} 
					
					}
				
			}
			
		if(!$is_error) {
			//$id = $user->generateUniqueID();
			$created_by = $_SESSION['user_id'];
			$last_updated_by = $_SESSION['user_id'];
			
			if($is_edit) {
					$id = $_GET['id'];
					$ip = $user->getRealIPAddr();
					//update common data
					if($is_pdf_zip) { 
						$stmt = $database->db->prepare("UPDATE download_files SET title=?, status=?, last_update=?, last_update_ip=?, pdf=? WHERE id=?");
						$stmt->bind_param("sssssi", $title, $status, $date, $ip, $pdf_link, $id);
					} else {
						$stmt = $database->db->prepare("UPDATE download_files SET title=?, status=?, last_update=?, last_update_ip=? WHERE id=?");
						$stmt->bind_param("ssssi", $title, $status, $date, $ip, $id);
					}
					
					if(!$stmt->execute()) die("Unable to update data: ".$stmt->error);
					else {
						$stmt->close();
						//delete regions & update
						$stmt = $database->db->prepare("DELETE FROM download_files_regions WHERE pdf_id=?");
						$stmt->bind_param("i", $id);
						$stmt->execute();
						$stmt->close();
						
						if(is_array($regions)) {
							$uid = $_SESSION['user_id'];
							$stmt = $database->db->prepare("INSERT INTO download_files_regions(region_id, pdf_id, create_date, created_by) VALUES (?, ?, ?, ?)");
							foreach( $regions as $key => $value ) {
								if($value!='') {
									$stmt->bind_param("iisi", $value, $id, $date, $created_by);
									$stmt->execute();
								}
							}
							$stmt->close();
						}
					
					
						$_SESSION['msg_type'] = 1;
						$_SESSION['msg'] = 'Successfully the record has been updated.';
					
						header("location: download-files.php?id=".$id); 
						exit();
					}
			} 
			else {
				$ip = $user->getRealIPAddr();	
				$stmt = $database->db->prepare("INSERT INTO download_files(title, pdf, status, create_date, created_by, last_updated_by, last_update_ip) VALUES (?, ?, ?, ?, ?, ?, ?)");
				$stmt->bind_param("ssssiis", $title, $pdf_link, $status, $date, $created_by, $created_by, $ip);
				
				if(!$stmt->execute()) die("Unable to insert data: ".$stmt->error);
				else { 
					$id = $database->db->insert_id;
					$stmt->close();
					
					if(is_array($regions)) {
						$uid = $_SESSION['user_id'];
						$stmt = $database->db->prepare("INSERT INTO download_files_regions(region_id, pdf_id, create_date, created_by) VALUES (?, ?, ?, ?)");
						foreach( $regions as $key => $value ) {
							if($value!='') {
								$stmt->bind_param("iisi", $value, $id, $date, $created_by);
								$stmt->execute();
							}
						}
						$stmt->close();
					}
							
					$_SESSION['msg_type'] = 1;
					$_SESSION['msg'] = 'Successfully A new Record has been added.';
					
					header("location: download-files.php"); 
					exit();
						
				 }
			}
		}	

	}
	
	if(isset($_GET['id'])) {	
		$id = $_GET['id'];
		$result = mysql_query("SELECT * FROM download_files WHERE id='$id'");
		$rows = mysql_num_rows($result);
		
		if($rows > 0) {
			$is_edit = true;
			$data = mysql_fetch_assoc($result);
			
			$title = $data['title'];
			$pdf = $data['pdf'];
			$status = $data['status'];
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
<title>Download Files</title>
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
<link rel="stylesheet" type="text/css" href="assets/global/plugins/bootstrap-fileinput/bootstrap-fileinput.css"/>
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
      <h3 class="page-title"> Add/Edit Download Files <a href="all-download-files.php" class="btn green"> Back to Manage Download Files </a> </h3>
      <div class="page-bar">
        <ul class="page-breadcrumb">
          <li> <i class="fa fa-home"></i> <a href="dashboard.php">Dashboard</a> <i class="fa fa-angle-right"></i> </li>
          <li> <a href="#">Download Filess</a> </li>
        </ul>
        <div class="page-toolbar">
          <div class="btn-group pull-right"> </div>
        </div>
      </div>
      <!-- END PAGE HEADER--> 
      <!-- BEGIN PAGE CONTENT-->
      
      <div class="row">
        <div class="col-md-12">
          <div class="portlet box blue">
            <div class="portlet-title">
              <div class="caption"> <i class="fa fa-gift"></i>Add/Edit Download Files </div>
            </div>
            <div class="portlet-body form">
              <?php 
										if(isset($_SESSION['msg'])) echo '<div class="alert alert-success"><strong>Success!</strong> '.$_SESSION['msg'].'</div>';
									?>
              <form class="form-horizontal" name="subscription" id="subscription" action="" method="post" enctype="multipart/form-data">
				<input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>" />
                <div class="form-body"> 
                  
                  <!--<h3 class="form-section">Download Files Details <a id="sample_editable_1_new" href="download-files.php" class="btn green">
                                                Add Download Files <i class="fa fa-plus"></i>
                                                </a></h3> -->
                  <div class="row">
                    <div class="col-md-6">
                      <div class="form-group <?php if(($is_error) && $error['title'] != '') echo 'has-error' ?>">
                        <label class="control-label col-md-3">Title</label>
                        <div class="col-md-9">
                          <input type="text" class="form-control validate[required, maxSize[100]] " name="title" id="title" placeholder="" value="<?php echo $title ?>">
                          <?php if(($is_error) && $error['title'] != '') echo '<span class="help-block">'.$error['title'].'</span>' ?>
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="control-label col-md-3">Select Regions</label>
                        <div class="col-md-9">
                          <select name="regions[]" id="select2_sample2" class="form-control select2" data-placeholder="Select Regions" multiple>
                            <?php 
									if($is_edit) {
										$result = mysql_query("SELECT * FROM download_files_regions WHERE pdf_id='$id'");
										$region_array = array();
										while($row = mysql_fetch_assoc($result)) {
											array_push($region_array, $row['region_id']);
										}
									}
 
								
									$result = mysql_query("SELECT * FROM regions WHERE status='1' ORDER BY region_name ASC");
									$num_rows = mysql_num_rows($result);
									if($num_rows > 0) {
										while($regions = mysql_fetch_assoc($result)) {
											$selected = '';
											if($is_edit && in_array($regions['id'],$region_array)) $selected = 'selected';	
											echo '<option value="'.$regions['id'].'" '.$selected.'>'.$regions['region_name'].'</option>';
										}
										
									}
								?>
                          </select>
                        </div>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group <?php if(($is_error) && $error['pdf'] != '') echo 'has-error' ?>">
                        <label class="control-label col-md-3">File Upload</label>
                        <div class="col-md-9">
                          <div class="fileinput fileinput-new" data-provides="fileinput"> <span class="btn default btn-file"> <span class="fileinput-new"> Select file </span> <span class="fileinput-exists"> Change </span>
                            <input type="file" class="<?php if(!$is_edit) echo 'validate[required]'; ?>" name="pdf" id="pdf" />
                            </span> <span class="fileinput-filename"> </span> &nbsp; <a href="#" class="close fileinput-exists" data-dismiss="fileinput"> </a> </div>
                          <?php if(($is_error) && $error['pdf'] != '') echo '<span class="help-block">'.$error['pdf'].'</span>' ?>
                          <div class="clearfix margin-top-10"> <span class="label label-danger"> NOTE! </span> PDF, Doc, Docx, XLS, XLSX, PPTX, PPT, Zip Allowed only, Max Size 20MB.
                            <?php if($is_edit) echo '<a href="../'.$pdf.'">Download Zip File</a>'; ?>
                          </div>
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="control-label col-md-3">Status</label>
                        <div class="col-md-9">
                          <select class="form-control" name="status" id="status">
                            <option value="1" <?php if($status == 1) echo 'selected'; ?> >Active</option>
                            <option value="0" <?php if($status == 0) echo 'selected'; ?> >Inactive</option>
                          </select>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="form-actions">
                  <input type="submit" name="submit_file" class="btn blue" value=" Submit " />
                </div>
              </form>
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
<script type="text/javascript" src="assets/global/plugins/bootstrap-fileinput/bootstrap-fileinput.js"></script> 
<script type="text/javascript" src="assets/global/plugins/ckeditor/ckeditor.js"></script> 
<!-- END PAGE LEVEL PLUGINS --> 

<!-- BEGIN PAGE LEVEL SCRIPTS --> 
<script src="assets/global/scripts/metronic.js" type="text/javascript"></script> 
<script src="assets/admin/layout/scripts/layout.js" type="text/javascript"></script> 
<script src="assets/admin/layout/scripts/quick-sidebar.js" type="text/javascript"></script> 
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
	QuickSidebar.init(); // init quick sidebar
	Demo.init(); // init demo features
	ComponentsPickers.init();
	FormSamples.init();
	ComponentsDropdowns.init();
});
</script> 
<!-- END JAVASCRIPTS --> 

<script src="assets/admin/layout/scripts/jquery.validationEngine-en.js" type="text/javascript" charset="utf-8"></script> 
<script src="assets/admin/layout/scripts/jquery.validationEngine.js" type="text/javascript" charset="utf-8"></script> 
<script>
        jQuery(document).ready(function(){
			// binds form submission and fields to the validation engine
            jQuery("#subscription").validationEngine();
			jQuery("#pricing").validationEngine();
        });
    </script> 
<script type="text/javascript" src="assets/admin/layout/scripts/highlight.js"></script> 
<script type="text/javascript" src="assets/admin/layout/scripts/zebra_dialog.js"></script> 
<script type="text/javascript">
    hljs.initHighlightingOnLoad();
</script> 
<script type="text/javascript">
    $(document).ready(function() {
        $('.example36').live('click', function(e) {
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
                        $.ajax({ url: 'delete_download_file.php',
                        data: {id: user_id, csrf_token: '<?php echo $csrf_token; ?>'},
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
<?php 
unset($_SESSION['msg_type2']);
unset($_SESSION['msg2']);
unset($_SESSION['msg_type']);
unset($_SESSION['msg']);
?>
</body>
<!-- END BODY -->
</html>