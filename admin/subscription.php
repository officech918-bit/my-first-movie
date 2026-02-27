<?php
	date_default_timezone_set('Asia/Kolkata');
	$date = date("Y-m-d H:i:s", time());

	//get class files
	require_once('inc/requires.php');
	require_once('classes/class.admin.php');

	if (empty($_SESSION['csrf_token'])) {
		$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
	}
	$csrf_token = $_SESSION['csrf_token'];
	
	
	
	//create objects
	$database = new MySQLDB();
	
	// Generate CSRF token
	if (empty($_SESSION['csrf_token'])) {
		$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
	}
	$csrf_token = $_SESSION['csrf_token'];

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
	
	$issue_detail_file = $direct_path."required-issue-detail-page/index.php";
	
	if(isset($_POST['submit_subscription'])) {
		
		// CSRF token validation
		if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
			$is_error = true;
			$error['csrf'] = "Invalid CSRF token.";
		} else {
			//get the id from url if edit mode
			if(isset($_GET['id'])) {$is_edit = true; $id=$_GET['id'];}
			
			$title = $_POST['title'];
			$megazine_description = $_POST['megazine_description'];
			$slug = $_POST['slug'];
			$price = $_POST['price'];
			$slug = $user->toSlug($slug);
			$image_raw = $_POST['image_raw'];
			$alt_text = $_POST['alt_text'];
			$description = $_POST['description'];
			$pdf = $_POST['pdf'];
			$status = $_POST['status'];
			$page_title = $_POST['page_title'];
			$page_description = $_POST['page_description'];
			$page_keywords = $_POST['page_keywords'];
			$search_terms = $_POST['search_terms'];
			
			$issue_date = $_POST['issue_date'];
			$issue_date = date("Y-m-d", strtotime($issue_date));
			$ref_issue_title = $_POST['ref_issue_title'];
			$ref_issue_id = $_POST['ref_issue_id'];
		}

		
		//validate
		if(!valid::hasValue($title)) { $is_error = true; $error['title'] = "Title can not be empty"; }
		else if(valid::isTooLong($title, 30)) { $is_error = true; $error['title'] = "Title can not more than 30 character"; }
		
		if(!valid::hasValue($price)) { $is_error = true; $error['price'] = "Price can not be empty"; }
		if(!valid::hasValue($megazine_description)) { $is_error = true; $error['megazine_description'] = "Megazine description can not be empty"; }

		//validate slug, if it is not edit mode it should not be there in database
		if(!$is_edit) {
			$stmt = $database->db->prepare("SELECT * FROM subscriptions WHERE slug=?");
			$stmt->bind_param("s", $slug);
			$stmt->execute();
			$temp_res = $stmt->get_result();
			if($temp_res->num_rows > 0) { $is_error = true; $error['slug'] = "Slug already exist in the database"; }
		} 
	
		//if it is edit mode first check the slug has been changed or not, if changed it should be there in database
		if($is_edit) {
			$db_slug = $_POST['db_slug'];
			$db_slug = trim($db_slug);			
			if($db_slug != $slug) {
				$is_new_slug = true;
				$stmt = $database->db->prepare("SELECT * FROM subscriptions WHERE slug=?");
				$stmt->bind_param("s", $slug);
				$stmt->execute();
				$temp_res = $stmt->get_result();
				if($temp_res->num_rows > 0) { $is_error = true; $error['slug'] = "Slug already exist in the database"; }
			}
		}
		
		if((strlen($_FILES['image_raw']['name']) > 0)) {
				$is_image = true; 
				//the image -> variables
	    		$file_type = $_FILES['image_raw']['type'];
        		$file_name = stripslashes($_FILES['image_raw']['name']);
        		$file_size = $_FILES['image_raw']['size'];
        		$file_tmp = $_FILES['image_raw']['tmp_name'];
				
				//get extention
				$extension = strtolower($user->getExtension($file_name));
				//echo $extension;
				
				//validate image credentials
				$maxSize = 5000*1024;
				if (($extension != 'jpg') && ($extension != 'jpeg') && ($extension != 'png') && ($extension != 'gif')) {
					$is_error = true;
					$error['image_raw'] = "Invalid Image Format: format required are jpeg, jpg, png and gif";
				} elseif($file_size > $maxSize) {
					$is_error = true;
					$error['image_raw'] = "Maximum image size allowed is 5MB";
				}
				
				if(!$is_error) {
					//create folder for user ads
					$pic_id = $user->generateUniqueID();
					$image_raw = $pic_id.'_raw.'.$extension;
					$image_300 = $pic_id.'_300.'.$extension;
					$image_100 = $pic_id.'_100.'.$extension;
					
					$uploadPath = "";
					if($sub_location != "") { $uploadPath = $_SERVER['DOCUMENT_ROOT'].'/'.$sub_location.'/images/subscriptions/';}
					else { $uploadPath = $_SERVER['DOCUMENT_ROOT'].'/images/subscriptions/'; }
					$linkPath = 'images/subscriptions/';
					$image_raw_link = $linkPath.$image_raw;
					$image_300_link = $linkPath.$image_300;
					$image_100_link = $linkPath.$image_100;

				
					if(!file_exists($uploadPath)) {mkdir($uploadPath, 0777);}
					//mkdir($path);
					
					//delete the image if exist
					if(file_exists($uploadPath.$image_raw)) { 
						unlink($uploadPath.$image_raw);
					}
					if(file_exists($uploadPath.$image_300)) {	
						unlink($uploadPath.$image_300);
					}
					if(file_exists($uploadPath.$image_100)) {	
						unlink($uploadPath.$image_100);
					}
					
					//upload the image
					$copied = move_uploaded_file($_FILES['image_raw']['tmp_name'], $uploadPath.$image_raw);
					if($copied) {
						
						list($width, $height, $type, $attr) = getimagesize($uploadPath.$image_raw);
						//create thumb picture
						if($width > 300) {
							$thumb_width = 300;
							$isThumCreated = create_thumbnail($uploadPath.$image_raw, $uploadPath.$image_300, $thumb_width);
							if(!$isThumCreated){
								$is_error = true;
								$error['image_raw'] = "Error Occored while creating thumb image for you, try again later...";
							}
						} else {
							$image_300_link = $image_raw_link;	
						}
						
					
						if($width > 100) {
							$thumb_width = 100;
							$isThumCreated = create_thumbnail($uploadPath.$image_raw, $uploadPath.$image_100, $thumb_width);
							if(!$isThumCreated){
								$is_error = true;
								$error['image_raw'] = "Error Occored while creating thumb image for you, try again later...";
							}
						} else {
							$image_100_link = $image_raw_link;	
						}
						
					} else {
						$is_error = true;
						$error['image_raw'] = "Error occured while uploading image!!!";
					}
					
					
				}
				
			}
	
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
				
				//validate image credentials
				$maxSize = 5000*1024;
				if (($extension != 'zip')) {
					$is_error = true;
					$error['pdf'] = "Invalid File Format: only zip format is allowed";
				} elseif($file_size > $maxSize) {
					$is_error = true;
					$error['pdf'] = "Maximum pdf zip size allowed is 5MB";
				}
				
				if(!$is_error) {
					//create folder for user ads
					$zip_name = $file_name;
					
					$uploadPath = "";
					if($sub_location != "") { $uploadPath = $_SERVER['DOCUMENT_ROOT'].'/'.$sub_location.'/docs/';}
					else { $uploadPath = $_SERVER['DOCUMENT_ROOT'].'/docs/'; }
					$linkPath = 'docs/';
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
						$error['pdf'] = "Error occured while uploading pdf zip file!!!";} 
					
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
					$query = "UPDATE subscriptions SET title=?, price=?, megazine_description=?, alt_text=?, description=?, status=?, page_title=?, page_description=?, page_keywords=?, search_terms=?, issue_date=?, ref_issue_title=?, ref_issue_id=?, last_update=?, last_update_ip=?";
					$params = [$title, $price, $megazine_description, $alt_text, $description, $status, $page_title, $page_description, $page_keywords, $search_terms, $issue_date, $ref_issue_title, $ref_issue_id, $date, $ip];
					$param_types = "sssssssssssssss";
					
					//update slug if changed
					if($is_new_slug) {
						$query .= ", slug=?";
						$params[] = $slug;
						$param_types .= "s";
						
						//rename slug directory
						$old_dir = $direct_path.'esubscriptions/'.$db_slug;
						$new_dir = $direct_path.'esubscriptions/'.$slug;
						rename($old_dir, $new_dir);
					}
					
					//update image files if uploaded
					if($is_image) {
						$query .= ", image_raw=?, image_300=?, image_100=?";
						$params[] = $image_raw_link;
						$params[] = $image_300_link;
						$params[] = $image_100_link;
						$param_types .= "sss";
					}
					
					//update zip file if updated
					if($is_pdf_zip) { 
						$query .= ", pdf=?"; 
						$params[] = $pdf_link;
						$param_types .= "s";
					}
					
					$query .= " WHERE subcription_id=?";
					$params[] = $id;
					$param_types .= "s";

					$stmt = $database->db->prepare($query);
					$stmt->bind_param($param_types, ...$params);
					
					if(!$stmt->execute()) die("Unable to update data: ". $stmt->error);
					else {
					
						/* $query = "UPDATE subscriptions SET title='$title', megazine_description='$megazine_description', slug='$slug', image_raw='$image_raw_link', image_300='$image_300_link', image_100='$image_100_link', alt_text='$alt_text', description='$description', pdf='$pdf_link', status='$status', page_title='$page_title', page_description='$page_description', page_keywords='$page_keywords', search_terms='$search_terms', last_update='$last_update', last_update_time='$last_update_time', sys_update_date='$sys_update_date', sys_update_date_time='$sys_update_date_time', last_updated_by='$last_updated_by' WHERE id='$id'"; */
					
						$_SESSION['msg_type'] = 1;
						$_SESSION['msg'] = 'Successfully the record has been updated.';
					
						header("location: subscription.php?id=".$id); 
						exit();
					}
					
					
			
					
			} 
			else {
				$ip = $user->getRealIPAddr();	
				$query = "INSERT INTO subscriptions(title, megazine_description, slug, price, image_raw, image_300, image_100, alt_text, description, pdf, status, page_title, page_description, page_keywords, search_terms, issue_date, ref_issue_title, ref_issue_id, create_date, created_by, last_updated_by, last_update_ip) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
				$stmt = $database->db->prepare($query);
				$stmt->bind_param("ssssssssssssssssssssss", $title, $megazine_description, $slug, $price, $image_raw_link, $image_300_link, $image_100_link, $alt_text, $description, $pdf_link, $status, $page_title, $page_description, $page_keywords, $search_terms, $issue_date, $ref_issue_title, $ref_issue_id, $date, $created_by, $created_by, $ip);
			}
			
			//echo $query;
			if(!$stmt->execute()) die("Unable to insert data: ". $stmt->error);
			else { 
			
				//create directory
				$page_path = $direct_path.'esubscriptions/'.$slug;
				if(!file_exists($page_path)) {mkdir($page_path, 0777);}
				
				//copy require files
				//system('cp /path/to/file/* /path/to/copy');
				//system("cp $equip_file $cat_path");
				copy($issue_detail_file, $page_path."/index.php");
				
				$_SESSION['msg_type'] = 1;
				$_SESSION['msg'] = 'Successfully A new Record has been added.';
				
				header("location: subscription.php"); 
				exit();
					
			 }
		}	

	}
	
	if(isset($_POST['submit_pricing'])) {
		// CSRF token validation
		if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
			$is_error = true;
			$error['csrf'] = "Invalid CSRF token.";
		} else {
			//CHECK IF IT IS IN EDIT MODE
			if(isset($_GET['id'])) {$is_edit = true; $subscription_id=$_GET['id'];}
			if(isset($_GET['price_id'])) {$is_edit_price = true; $price_id=$_GET['price_id'];}
			$active = 'pricing';
			
			//GET THE DATA & VALIDATE DATA
			$duration = $_POST['duration'];
			$amount = $_POST['amount'];
			$saving = $_POST['saving'];
			$short_order = $_POST['short_order'];
			
			if(!valid::hasValue($subscription_id)) { $is_error = true; $error['duration'] = "Please Edit a Subscription first and then add pricing slab to it."; }
			if(!valid::hasValue($duration)) { $is_error = true; $error['duration'] = "Duration can not be empty"; }
			if(!valid::hasValue($amount)) { $is_error = true; $error['amount'] = "Amount can not be empty"; }
			if(!valid::hasValue($short_order)) { $is_error = true; $error['short_order'] = "Please select shorting order"; }
		}
		
		if(!$is_error) {
			//$price_id = $user->generateUniqueID();
			$created_by = $_SESSION['user_id'];
			$last_updated_by = $_SESSION['user_id'];
			
			if($is_edit_price) {
				$price_id = $_GET['price_id'];
				
				$update_query = "UPDATE pricing SET subscription_id=?, duration=?, amount=?, saving=?, short_order=?, last_update=?, last_updated_by=? WHERE price_id=?";
				$stmt = $database->db->prepare($update_query);
				$stmt->bind_param("ssssissi", $subscription_id, $duration, $amount, $saving, $short_order, $date, $last_updated_by, $price_id);
				
				if(!$stmt->execute()) die("Unable to update data: ". $stmt->error);
				else { 
					$_SESSION['msg_type2'] = 1;
					$_SESSION['msg2'] = 'Successfully the record has been updated.';
						
					header("location: subscription.php?id=".$subscription_id."&price_id=".$price_id."&price_active=1"); 
					exit();
				}
				
			} else {
				$insert_query = "INSERT INTO pricing(subscription_id, duration, amount, saving, short_order, create_date, created_by, last_updated_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
				$stmt = $database->db->prepare($insert_query);
				$stmt->bind_param("ssssisii", $subscription_id, $duration, $amount, $saving, $short_order, $date, $created_by, $created_by);
				
				if(!$stmt->execute()) die("Unable to insert data: " . $stmt->error);
				else {
					
					$_SESSION['msg_type2'] = 1;
					$_SESSION['msg2'] = 'Successfully Pricing Slab has been added.';
					
					header("location: subscription.php?id=".$subscription_id."&price_active=1"); 
					exit();

				 }
				 
			
			}
			
			
		}
	
	}
	
	if(isset($_GET['id'])) {	
		$id = $_GET['id'];
		$stmt = $database->db->prepare("SELECT *, DATE_FORMAT(issue_date,'%d %M %Y') AS issue_date FROM subscriptions WHERE subcription_id=?");
		$stmt->bind_param("s", $id);
		$stmt->execute();
		$result = $stmt->get_result();
		$rows = $result->num_rows;
		
		if($rows > 0) {
			$is_edit = true;
			$data = $result->fetch_assoc();
			
			$title = $data['title'];
			$megazine_description = $data['megazine_description'];
			$slug = $data['slug'];
			$price = $data['price'];
			$image_raw = $data['image_raw'];
			$image_300 = $data['image_300'];
			$alt_text = $data['alt_text'];
			$description = $data['description'];
			$pdf = $data['pdf'];
			$status = $data['status'];
			$page_title = $data['page_title'];
			$page_description = $data['page_description'];
			$page_keywords = $data['page_keywords'];
			$search_terms = $data['search_terms'];
			$pdf = $data['pdf'];
			$issue_date = $data['issue_date'];
			$ref_issue_title = $data['ref_issue_title'];
			$ref_issue_id = $data['ref_issue_id'];
		
		
		}
		
	}
	
	if(isset($_GET['price_id'])) {	
		$price_id = $_GET['price_id'];
		$stmt = $database->db->prepare("SELECT * FROM pricing WHERE price_id=?");
		$stmt->bind_param("s", $price_id);
		$stmt->execute();
		$result = $stmt->get_result();
		$rows = $result->num_rows;
		
		if($rows > 0) {
			$is_edit = true;
			$data = $result->fetch_assoc();
			
			$duration = $data['duration'];
			$amount = $data['amount'];
			$saving = $data['saving'];
			$short_order = $data['short_order'];
			
			$active = 'pricing';
		
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
<title>Subscriptions</title>
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
			Add/Edit Subscription  <a href="all-subscriptions.php" class="btn green"> Back to Manage Subscription </a>
			</h3>
			<div class="page-bar">
				<ul class="page-breadcrumb">
					<li>
						<i class="fa fa-home"></i>
						<a href="dashboard.php">Dashboard</a>
						<i class="fa fa-angle-right"></i>
					</li>
					<li>
						<a href="#">Subscriptions</a>
					</li>
				</ul>
				<div class="page-toolbar">
					<div class="btn-group pull-right">
						
					</div>
				</div>
			</div>
			<!-- END PAGE HEADER-->
			<!-- BEGIN PAGE CONTENT-->
            
			<div class="row">
				<div class="col-md-12">
                	<div class="portlet box blue">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-gift"></i>Add/Edit Subscription  
										
							</div>
                            
							<ul class="nav nav-tabs">
								
								<li <?php if($active == 'subscription') echo 'class="active"'; ?>>
									<a href="#portlet_tab1" data-toggle="tab">
									Subscription Details </a>
								</li>
                                <li <?php if($active == 'pricing' || isset($_GET['price_id'])) echo 'class="active"'; ?>>
									<a href="#portlet_tab2" data-toggle="tab">
									Pricing Slabs </a>
								</li>
							</ul>
						</div>
						<div class="portlet-body form">
							<div class="tab-content">
                            
								<div class="tab-pane <?php if($active == 'subscription') echo 'active'; ?>" id="portlet_tab1">
                                    <?php 
										if(isset($_SESSION['msg'])) echo '<div class="alert alert-success"><strong>Success!</strong> '.$_SESSION['msg'].'</div>';
									?>
  
								<form class="form-horizontal" name="subscription" id="subscription" action="" method="post" enctype="multipart/form-data">
								<input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
								<input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
								<input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
								<div class="form-body">
                                	
                                	<!--<h3 class="form-section">Subscription Details <a id="sample_editable_1_new" href="subscription.php" class="btn green">
                                                Add Subscription <i class="fa fa-plus"></i>
                                                </a></h3> -->
                                	<div class="row">
                                    	<div class="col-md-6">
                                        	<div class="form-group <?php if(($is_error) && $error['title'] != '') echo 'has-error' ?>">
                                                <label class="control-label col-md-3">Title</label>
                                                <div class="col-md-9">
                                                    <input type="text" class="form-control validate[required, maxSize[30]] " name="title" id="title" placeholder="" value="<?php echo $title ?>">
                                                    <?php if(($is_error) && $error['title'] != '') echo '<span class="help-block">'.$error['title'].'</span>' ?>
                                                </div>
                                            </div>
                                        	<div class="form-group <?php if(($is_error) && $error['megazine_description'] != '') echo 'has-error' ?>">
                                                <label class="control-label col-md-3">Short Description</label>
                                                <div class="col-md-9">
                                                <textarea name="megazine_description" id="megazine_description" class="form-control validate[required] " placeholder="Short Description" cols="" rows="7"><?php echo $megazine_description; ?></textarea>
                                                <?php if(($is_error) && $error['megazine_description'] != '') echo '<span class="help-block">'.$error['megazine_description'].'</span>' ?>
                                            </div>
                                            </div>
                                            <div class="form-group <?php if(($is_error) && $error['issue_date'] != '') echo 'has-error' ?>">
                                                <label class="control-label col-md-3">Issue Date</label>
                                                <div class="col-md-9">
                                                    <input type="text" class="form-control date-picker validate[required] " name="issue_date" id="issue_date" placeholder="Issue Date" value="<?php echo $issue_date ?>">
                                                    <?php if(($is_error) && $error['issue_date'] != '') echo '<span class="help-block">'.$error['issue_date'].'</span>' ?>
                                                </div>
                                            </div>
                                            <div class="form-group <?php if(($is_error) && $error['slug'] != '') echo 'has-error' ?>">
                                                <label class="control-label col-md-3">Page Slug (URL)</label>
                                                <div class="col-md-9">
                                                    <input type="text" class="form-control validate[required]" name="slug" id="slug" placeholder="" value="<?php echo $slug ?>">
                                                    <input type="hidden" name="db_slug" value="<?php echo $slug ?>">
                                                    <?php if(($is_error) && $error['slug'] != '') echo '<span class="help-block">'.$error['slug'].'</span>' ?>
                                                    <div class="clearfix margin-top-10">
                                                        <span class="label label-danger">
                                                        NOTE! </span>
                                                         i.e. my-seo-friendly-url
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group <?php if(($is_error) && $error['price'] != '') echo 'has-error' ?>">
                                                <label class="control-label col-md-3">Price (Single)</label>
                                                <div class="col-md-9">
                                                    <input type="text" class="form-control validate[required]" name="price" id="price" placeholder="" value="<?php echo $price ?>">
                                                  
                                                    <?php if(($is_error) && $error['price'] != '') echo '<span class="help-block">'.$error['price'].'</span>' ?>
                                                    <div class="clearfix margin-top-10">
                                                        <span class="label label-danger">
                                                        NOTE! </span>
                                                         i.e. 90 or 80
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="control-label col-md-3">Page Title</label>
                                                <div class="col-md-9">
                                                    <input type="text" class="form-control" name="page_title" id="page_title" placeholder="" value="<?php echo $page_title ?>">
                                                   
                                                    <div class="clearfix margin-top-10">
                                                        <span class="label label-danger">
                                                        NOTE! </span>
                                                         Upto 60-70 Characters
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="control-label col-md-3">Page Keyword</label>
                                                <div class="col-md-9">
                                                    <input type="text" class="form-control" placeholder="" name="page_keywords" id="page_keywords" value="<?php echo $page_keywords; ?>" />
                                                    <div class="clearfix margin-top-10">
                                                        <span class="label label-danger">
                                                        NOTE! </span>
                                                         Coma Separated
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="control-label col-md-3">Page Description</label>
                                                <div class="col-md-9">
                                                    <input type="text" class="form-control" placeholder="" name="page_description" id="page_description" value="<?php echo $page_description ?>" />
                                                    <div class="clearfix margin-top-10">
                                                        <span class="label label-danger">
                                                        NOTE! </span>
                                                         Upto 160 Characters
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            
                                        </div>
                                        <div class="col-md-6">
                                        	<div class="form-group <?php if(($is_error) && $error['image_raw'] != '') echo 'has-error' ?>">
										<label class="control-label col-md-3">Megazine Image</label>
										<div class="col-md-9">
											<div class="fileinput fileinput-new" data-provides="fileinput">
												<div class="fileinput-preview thumbnail" data-trigger="fileinput" style="width: 200px; height: 150px;">
                                                	<?php if($is_edit) echo '<img src="../'.$image_raw.'" />';  ?>
												</div>
												<div>
													<span class="btn default btn-file">
													<span class="fileinput-new">
													Select image </span>
													<span class="fileinput-exists">
													Change </span>
													<input type="file" class="<?php if(!$is_edit) echo 'validate[required]'; ?>" name="image_raw" id="image_raw" />
													</span>
													<a href="#" class="btn red fileinput-exists" data-dismiss="fileinput">
													Remove </a>
												</div>
											</div>
                                            <?php if(($is_error) && $error['image_raw'] != '') echo '<span class="help-block">'.$error['image_raw'].'</span>' ?>
											<div class="clearfix margin-top-10">
												<span class="label label-danger">
												NOTE! </span>
												Width:500px, Height: 690px, Max Size 2MB
											</div>
										</div>
									</div>
                                    		<div class="form-group">
                                                <label class="control-label col-md-3">Image Alt</label>
                                                <div class="col-md-9">
                                                <input type="text" class="form-control" placeholder="" id="alt_text" name="alt_text" value="<?php echo $alt_text ?>" />
                                            </div>
                                            </div>
                                    		<div class="form-group <?php if(($is_error) && $error['pdf'] != '') echo 'has-error' ?>">
										<label class="control-label col-md-3">PDF Zip Upload</label>
										<div class="col-md-9">
											<div class="fileinput fileinput-new" data-provides="fileinput">
												<span class="btn default btn-file">
												<span class="fileinput-new">
												Select file </span>
												<span class="fileinput-exists">
												Change </span>
												<input type="file" class="<?php if(!$is_edit) echo 'validate[required]'; ?>" name="pdf" id="pdf" />
												</span>
												<span class="fileinput-filename">
												</span>
												&nbsp; <a href="#" class="close fileinput-exists" data-dismiss="fileinput">
												</a>
											</div>
                                            <?php if(($is_error) && $error['pdf'] != '') echo '<span class="help-block">'.$error['pdf'].'</span>' ?>
                                            <div class="clearfix margin-top-10">
												<span class="label label-danger">
												NOTE! </span>
												PDF Zip Allowed only, Max Size 5MB. <?php if($is_edit) echo '<a href="../'.$pdf.'">Download Zip File</a>'; ?>
											</div>
										</div>
									</div>	
                                    <div class="form-group">
                                                <label class="control-label col-md-3">Search Keywords/Terms</label>
                                                <div class="col-md-9">
                                                    <input type="text" class="form-control" placeholder="" name="search_terms" id="search_terms" value="<?php echo $search_terms; ?>" />
                                                    <div class="clearfix margin-top-10">
                                                        <span class="label label-danger">
                                                        NOTE! </span>
                                                         Coma Separated
                                                    </div>
                                                </div>
                                            </div>
                                            
                                    <div class="form-group">
                                        <label class="control-label col-md-3">Ref Issue Title</label>
                                            <div class="col-md-9">
                                                <input type="text" class="form-control validate[required]" placeholder="" name="ref_issue_title" id="ref_issue_title" value="<?php echo $ref_issue_title; ?>" />
                                                <div class="clearfix margin-top-10">
                                                    <span class="label label-danger">
                                                    NOTE! </span>
                                                     Previos Issue/Popular Issue etc
                                                </div>
                                            </div>
                                        </div>        
                                    <div class="form-group">
										<label class="control-label col-md-3">Select Ref Issue</label>
                                        <div class="col-md-9">
										<select class="form-control validate[required]" name="ref_issue_id" id="ref_issue_id">
                                        	<?php 
												$result = mysql_query("SELECT * FROM subscriptions WHERE status='1' ORDER BY create_date DESC LIMIT 0, 15");
												$num_rows = mysql_num_rows($result);
												if($num_rows > 0) {
													while($subscriptions = mysql_fetch_assoc($result)) {
														$selected = '';
														if($subscriptions['subcription_id'] == $ref_issue_id) $selected = 'selected';
														echo '<option value="'.$subscriptions['subcription_id'].'" '.$selected.'>'.$subscriptions['title'].'</option>';
													}
													
												}
											?>
                                        </select>
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
                                        <div class="form-group col-md-12">
										<label class="control-label col-md-12" style="text-align:left"><strong>Long Description (Index)</strong></label>
										<div class="col-md-12">
											<textarea class="ckeditor form-control validate[required]" name="description" id="description" rows="6"><?php echo $description; ?></textarea>
										</div>

									</div>
                                        
                                    </div>
                                    

                                    				
								</div>
								<div class="form-actions">
									<input type="submit" name="submit_subscription" class="btn blue" value=" Submit " /> 
								</div>
							</form>
									
									
								</div>
								<div class="tab-pane <?php if($active == 'pricing' || isset($_GET['price_id'])) echo 'active'; ?>" id="portlet_tab2">
								<form class="form-horizontal" name="pricing" id="pricing" method="post" action=""> 
								<input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>"> 
								<input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
								<input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>"> 
								<div class="form-body">
                                	<h3 class="form-section">Pricing Slabs <?php if($is_edit_price) echo '<a id="sample_editable_1_new" href="subscription.php?id='.$id.'&price_active=1" class="btn green">
                                                Add Price Slab <i class="fa fa-plus"></i>
                                                </a>'; ?></h3>
                                	<?php 
										if(isset($_SESSION['msg2'])) echo '<div class="alert alert-success"><strong>Success!</strong> '.$_SESSION['msg2'].'</div>';
									?>
                                    <div class="row">    
                                        
                                        
                                        	
                                        <div class="col-md-3">
                                            <div class="form-group <?php if(($is_error) && $error['duration'] != '') echo 'has-error' ?>">
                                                <label class="control-label col-md-3">Duration</label>
                                                <div class="col-md-9">
                                                <select class="form-control validate[required]" name="duration" id="duration">
                                                    <option value="">Choose an Option</option>
                                                    <option value="Single Issue" <?php if($duration == 'Single Issue') echo 'selected'; ?>>Single Issue</option>
                                                    <option value="3 Months" <?php if($duration == '3 Months') echo 'selected'; ?>>3 Months</option>
                                                    <option value="6 Months" <?php if($duration == '6 Months') echo 'selected'; ?>>6 Months</option>
                                                    <option value="1 Year" <?php if($duration == '1 Year') echo 'selected'; ?>>1 Year</option>
                                                    <option value="2 Year" <?php if($duration == '2 Year') echo 'selected'; ?>>2 Year</option>
                                                </select>
                                                <?php if(($is_error) && $error['duration'] != '') echo '<span class="help-block">'.$error['duration'].'</span>' ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                        <div class="form-group <?php if(($is_error) && $error['amount'] != '') echo 'has-error' ?>">
                                        <label class="control-label col-md-3">Amount</label>
                                        	<div class="col-md-9">
                                        		<input type="text" class="form-control validate[required]" placeholder="" name="amount" id="amount" value="<?php echo $amount ?>">
                                                <?php if(($is_error) && $error['amount'] != '') echo '<span class="help-block">'.$error['amount'].'</span>' ?>
                                        	</div>
                                        </div>
                                        </div>
                                        <div class="col-md-3">
                                        <div class="form-group">
                                        <label class="control-label col-md-3">Saving</label>
                                            <div class="col-md-9">
                                            <input type="text" class="form-control" placeholder="" name="saving" id="saving" value="<?php echo $saving ?>">
                                            </div>
                                        </div>
                                        </div>
                                    	<div class="col-md-3">
                                            <div class="form-group <?php if(($is_error) && $error['short_order'] != '') echo 'has-error' ?>">
                                                <label class="control-label col-md-3">Short Order</label>
                                                <div class="col-md-9">
                                                <select class="form-control validate[required]" name="short_order" id="short_order">
                                                    <option value="">Choose an Option</option>
                                                    <option value="1" <?php if($short_order == 1) echo 'selected'; ?>>1: Single Issue</option>
                                                    <option value="2" <?php if($short_order == 2) echo 'selected'; ?>>2: 3 Months</option>
                                                    <option value="3" <?php if($short_order == 3) echo 'selected'; ?>>3: 6 Months</option>
                                                    <option value="4" <?php if($short_order == 4) echo 'selected'; ?>>4: 1 Year</option>
                                                    <option value="5" <?php if($short_order == 5) echo 'selected'; ?>>5: 2 Year</option>
                                                </select>
                                                 <?php if(($is_error) && $error['short_order'] != '') echo '<span class="help-block">'.$error['short_order'].'</span>' ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
									
                                    				
								</div>
								<div class="form-actions">
                                	<input type="submit" class="btn blue" name="submit_pricing" value=" Submit "/>
								</div>
							</form>
                            <div class="col-md-12"><div class="table-scrollable">
								<table class="table table-striped table-bordered table-advance table-hover">
								<thead>
								<tr>
									<th>
										<strong>Duration</strong>
									</th>
									<th >
										<strong>Amount</strong>
									</th>
									<th>
										<strong>Saving</strong>
									</th>
                                    <th>
										<strong>Order</strong>
									</th>
									<th>
									</th>
								</tr>
								</thead>
								<tbody>
                                <?php 
									$result = mysql_query("SELECT * FROM pricing WHERE subscription_id='$id' ORDER BY short_order ASC");
									
									while($row = mysql_fetch_assoc($result )){
										echo '<tr>
											<td class="highlight">'.$row['duration'].'</td>
											<td>Rs. '.$row['amount'].'</td>
											<td>'.$row['saving'].'</td>
											<td>'.$row['short_order'].'</td>
											<td>
												<a href="subscription.php?id='.$id.'&price_id='.$row['price_id'].'" class="btn default btn-xs purple"><i class="fa fa-edit"></i> Edit </a> 
												<a href="javascript:void(0)" class="btn default btn-xs black example36" title="'.$row['duration'].' | '.$row['price_id'] .'"><i class="fa fa-trash-o"></i> Delete</a>
										
											</td>
										</tr>';
									}
								
								 ?>
								
								
								
								
								</tbody>
								</table>
							</div></div>
                            
									
								</div>
								
							</div>
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
        var csrfToken = '<?php echo $csrf_token; ?>';
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
                        $.ajax({ url: 'delete_subscription_price.php',
                        data: {id: user_id, csrf_token: csrfToken},
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