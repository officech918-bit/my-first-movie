<?php
require_once('inc/requires.php');
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];
	
	//create objects
	$database = new MySQLDB();
	$user = new visitor();
	$is_error = false;
	$error = array();
	$is_edit = false;
	$menu = 'inc/left-menu-user.php';

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    $csrf_token = $_SESSION['csrf_token'];
	
	//check if the user is not logged in
	if(!$user->check_session())
	{	
		header("location: index.php"); 
		exit();
	} else if ($_SESSION['user_type'] == '3'){
		$user = new webmaster();
		
		$menu = 'inc/left-menu-webmaster.php';	
		$wm_first_name = $user->get_wm_first_name();
		$wm_last_name = $user->get_wm_last_name();
		
	} else if ($_SESSION['user_type'] == '2'){
		$menu = 'inc/left-menu-admin.php';
		$user = new admin();
	} else {
		$user = new user();
	}
	
	//set default data
	$first_name = $user->get('first_name');
	$last_name = $user->get('first_name');
	$email = $user->get('email');
	$designation = $user->get('designation');
	$contact = $user->get('contact');
		
	
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
		
	
	/* $direct_path = "";
	if($user->admin_location != "") $direct_path = $_SERVER['DOCUMENT_ROOT'].'/'.$user->admin_location.'/';		
	else $direct_path = $_SERVER['DOCUMENT_ROOT'].'/'; */
	
	if(isset($_POST['update_user_data'])) {
        if (!isset($_POST['csrf_token']) || !hash_equals($csrf_token, $_POST['csrf_token'])) {
            die('Invalid CSRF token');
        }
		if(isset($_GET['id'])) {$is_edit = true;}
		
		$first_name = $_POST['firstname'];
		$last_name = $_POST['lastname'];
		$email = $_POST['email'];
		$designation = $_POST['designation'];
		$contact = $_POST['contact'];
		
		//validate
		if(!valid::hasValue($first_name)) { $is_error = true; $error['first_name'] = "First Name can not be empty"; }
		else if(valid::isTooLong($first_name, 30)) { $is_error = true; $error['first_name'] = "First Name can not more than 30 character"; }
		
		if(!valid::hasValue($last_name)) { $is_error = true; $error['last_name'] = "Last Name can not be empty"; }
		else if(valid::isTooLong($last_name, 30)) { $is_error = true; $error['last_name'] = "First Name can not more than 30 character"; }
		
		if(!valid::isEmail($email)) {$is_error = true; $error['email'] = "Invalid Email id"; }	

		if(!$is_error) {
			$user_id = $_SESSION['uid'];
            $stmt = $database->db->prepare("UPDATE users SET first_name=?, last_name=?, email=?, designation=?, contact=?, last_updated=? WHERE user_id=?");
            $stmt->bind_param("ssssssi", $first_name, $last_name, $email, $designation, $contact, $date, $user_id);
					
			//echo $query;
			$stmt->execute();
			if($stmt->error) die("Unable to insert car data: ".$stmt->error);
			else { $message = "Car details were successfully updated...";
				header("location: profile-edit.php"); 
				exit();
			 }
		}	

	}
	
	if(isset($_POST['update_user_pass'])) {
        if (!isset($_POST['csrf_token']) || !hash_equals($csrf_token, $_POST['csrf_token'])) {
            die('Invalid CSRF token');
        }
		if(isset($_GET['id'])) {$is_edit = true;}
		$password = $_POST['password'];
		$password_new1 = $_POST['password_new1'];
		$password_new2 = $_POST['password_new2'];
		
		//validate
		if(valid::hasValue($password)) {
			$salt = $user->get_salt();
			$db_hash = $user->get_password();
			if (!password_verify($password, $db_hash)) {$is_error = true; $error['password'] = "Password does not match with database";}
		} else { $is_error = true; $error['password'] = "Please type your current password"; }
		
		if(!valid::hasValue($password_new1)) { $is_error = true; $error['password_new1'] = "Please type the new password"; }
		else if(!valid::isTooShort($password_new1, 6)) { $is_error = true; $error['password_new1'] = "Password length must be equal or more than 6 characters"; }
		
		if($password_new1 != $password_new2){ $is_error = true; $error['password_new2'] = "Re-type password does not matches with the new password"; }
		else if(!valid::hasValue($password_new2)) { $is_error = true; $error['password_new2'] = "Please type the Re-type password"; }
		
		

		if(!$is_error) {
			$user_id = $_SESSION['uid'];
			$new_hash = password_hash($password_new1, PASSWORD_DEFAULT);
            $stmt = $database->db->prepare("UPDATE users SET password=?, last_updated=? WHERE user_id=?");
            $stmt->bind_param("ssi", $new_hash, $date, $user_id);
					
			//echo $query;
			$stmt->execute();
			if($stmt->error) die("Database Error: ".$stmt->error);
			else { 
				header("location: profile-edit.php"); 
				exit();
			 }
		}	

	}
	
	if(isset($_POST['update_user_avatar'])) {
        if (!isset($_POST['csrf_token']) || !hash_equals($csrf_token, $_POST['csrf_token'])) {
            die('Invalid CSRF token');
        }
		$is_pic = false;
		if((strlen($_FILES['avatar']['name']) > 0)) {
				$is_pic = true; 
				//the image -> variables
	    		$file_type = $_FILES['avatar']['type'];
        		$file_name = stripslashes($_FILES['avatar']['name']);
        		$file_size = $_FILES['avatar']['size'];
        		$file_tmp = $_FILES['avatar']['tmp_name'];
				
				//get extention
				$extension = strtolower($user->getExtension($file_name));
				//echo $extension;
				
				//validate image credentials
				$maxSize = ((1024*1024)*10);
				if (($extension != 'jpeg') && ($extension != 'jpg') && ($extension != 'gif') && ($extension != 'png')) {
					$is_error = true;
					$error_image = "Invalid File Format: JPG/JPEG/GIF/PNG file format allowed only";
				} elseif($file_size > $maxSize) {
					$is_error = true;
					$error_image = "Maximum image size allowed is 10 MB";
				}
				
				if(!$is_error) {
					//create folder for user ads
					$pic_id = $user->generateUniqueID();
					$ImageName = $pic_id.'.'.$extension;
					$thumbImageName = $pic_id.'_thumb.'.$extension;
					$uploadPath = $direct_path."images/users/";
					$linkPath = 'images/users/';
					
					$avatar = $linkPath.$ImageName;
						
					if(!file_exists($uploadPath)) {mkdir($uploadPath, 0755); chmod($uploadPath, 0755);}
					
					//delete the image if exist
					if(file_exists($uploadPath.$ImageName)) { 
						unlink($uploadPath.$ImageName);
					}
					
					//upload the image
					$copied = move_uploaded_file($_FILES['avatar']['tmp_name'], $uploadPath.$ImageName);
					if($copied) {
						//create thumb picture
						$thumb_width = 200;
						$isThumCreated = create_thumbnail($uploadPath.$ImageName, $uploadPath.$thumbImageName, $thumb_width);
						if(!$isThumCreated){$is_error = true;$error_image = "Error Occored while creating thumb image for you, try again later...";}
						
						
					} else {$is_error = true;
						$error_image = "Error occured while uploading file!!!";} 
					
				}
				
			}
		else {
			$error_image = "User Profile Image is required.";
			$is_error = true;
		}
		
		if(!$is_error) {
			$user_id = $_SESSION['uid'];
			$avatar_thumb = $linkPath.$thumbImageName;
            $stmt = $database->db->prepare("UPDATE users SET avatar=?, avatar_original=?, last_updated=? WHERE user_id=?");
            $stmt->bind_param("sssi", $avatar_thumb, $avatar, $date, $user_id);
					
			//echo $query;
			$stmt->execute();
			if($stmt->error) die("Database Error: ".$stmt->error);
			else { 
				header("location: profile-edit.php"); 
				exit();
			 }
			
		}
		
	}


?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <meta name="keyword" content="">
    <link rel="shortcut icon" href="img/favicon.png">

    <title>Profile Edit</title>

    <!-- Bootstrap core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/bootstrap-reset.css" rel="stylesheet">
    <!--external css-->
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
    <!-- Custom styles for this template -->
    <link href="css/style.css" rel="stylesheet">
    <link href="css/style-responsive.css" rel="stylesheet" />

	<link rel="stylesheet" type="text/css" href="assets/bootstrap-fileupload/bootstrap-fileupload.css" />
    
    
    <!-- HTML5 shim and Respond.js IE8 support of HTML5 tooltipss and media queries -->
    <!--[if lt IE 9]>
      <script src="js/html5shiv.js"></script>
      <script src="js/respond.min.js"></script>
    <![endif]-->
    
        <!-- js placed at the end of the document so the pages load faster -->
    <script src="js/jquery.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script class="include" type="text/javascript" src="js/jquery.dcjqaccordion.2.7.js"></script>
    <script src="js/jquery.scrollTo.min.js"></script>
    <script src="js/jquery.nicescroll.js" type="text/javascript"></script>
    <script src="assets/jquery-knob/js/jquery.knob.js"></script>
    <script src="js/respond.min.js" ></script>
    
	<!-- validation -->
    <link rel="stylesheet" href="css/validationEngine.jquery.css" type="text/css"/>
    <script src="js/jquery.validationEngine-en.js" type="text/javascript" charset="utf-8"></script>
    <script src="js/jquery.validationEngine.js" type="text/javascript" charset="utf-8"></script>
    <script>
        jQuery(document).ready(function(){
			
            // binds form submission and fields to the validation engine
            jQuery("#user_data").validationEngine();
			jQuery("#user_pass").validationEngine();
			jQuery("#user_avatar").validationEngine();
        });
    </script>
    
    <script type="text/javascript" src="assets/bootstrap-fileupload/bootstrap-fileupload.js"></script>
    
    <!--common script for all pages-->
    <script src="js/common-scripts.js"></script>
	<!--this page  script only-->
    <script src="js/advanced-form-components.js"></script>
    
    
  </head>

  <body>

  <section id="container" class="">
      <!--header start-->
      <?php include('inc/header.php'); ?>
      <!--header end-->
      <!--sidebar start-->
      <aside>
          <?php include($menu); ?>
      </aside>
      <!--sidebar end-->
      <!--main content start-->
      <section id="main-content">
          <section class="wrapper">
              <!-- page start-->
              <div class="row">
                  <aside class="profile-nav col-lg-3">
                      <section class="panel">
                          <div class="user-heading round">
                              <a href="#">
                                  <img src="../<?php echo $user->get_avatar(); ?>" alt="" width="140" height="140">
                              </a>
                              <h1><?php echo $user->get_first_name(); echo " ".$user->get_last_name(); ?></h1>
                              <p><?php echo $user->get_email(); ?></p>
                          </div>

                          <ul class="nav nav-pills nav-stacked">
                              <li><a href="profile-edit.php"> <i class="icon-user"></i> Profile</a></li>
                              <li><a href="dashboard.php"> <i class="icon-calendar"></i> Back to Dashboard</a></li>
                          </ul>

                      </section>
                  </aside>
                  <aside class="profile-info col-lg-9">
                      <section class="panel">
                          <div class="panel-body bio-graph-info">
                              <h1> Profile Info</h1>
                              <form class="form-horizontal" role="form" action="" method="post" enctype="multipart/form-data" id="user_data" name="user_data">
                                  <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                  <div class="form-group <?php if(isset($error['first_name'])) echo 'has-error'; ?>">
                                      <label  class="col-lg-2 control-label">First Name</label>
                                      <div class="col-lg-6">
                                          <input type="text" id="firstname" name="firstname" value="<?php echo $first_name ?>" class="validate[required, maxSize[30]] text-input form-control" />
                                          <?php if(isset($error['first_name'])) echo '<p class="help-block">'.$error['first_name'].'</p>'; ?>
                                      </div>
                                  </div>
                                  <div class="form-group <?php if(isset($error['last_name'])) echo 'has-error'; ?>">
                                      <label  class="col-lg-2 control-label ">Last Name</label>
                                      <div class="col-lg-6">
                                          <input type="text" id="lastname" name="lastname" value="<?php echo $last_name ?>" class="validate[required, maxSize[30]] text-input form-control" />
                                          <?php if(isset($error['last_name'])) echo '<p class="help-block">'.$error['last_name'].'</p>'; ?>
                                      </div>
                                  </div>
                                  <div class="form-group">
                                      <label  class="col-lg-2 control-label">Designation</label>
                                      <div class="col-lg-6">
                                          <input type="text" class="form-control" id="designation" name="designation" value="<?php echo $designation ?>" >
                                      </div>
                                  </div>
                                  <div class="form-group <?php if(isset($error['email'])) echo 'has-error'; ?>">
                                      <label  class="col-lg-2 control-label">Email</label>
                                      <div class="col-lg-6">
                                          <input type="text" id="email" name="email" class="validate[required,custom[email]] form-control" value="<?php echo $email ?>" />
                                          <?php if(isset($error['email'])) echo '<p class="help-block">'.$error['email'].'</p>'; ?>
                                      </div>
                                  </div>
                                  <div class="form-group">
                                      <label  class="col-lg-2 control-label">Contact</label>
                                      <div class="col-lg-6">
                                          <input type="text" class="form-control" id="contact" name="contact" value="<?php echo $contact ?>" >
                                      </div>
                                  </div>
                                  <div class="form-group">
                                      <div class="col-lg-offset-2 col-lg-10">
                                      	  <input name="update_user_data" type="submit" value="Update" class="btn btn-success" >
                                         
                                      </div>
                                  </div>
                              </form>
                          </div>
                      </section>
                      <section>
                          <div class="panel panel-primary">
                              <div class="panel-heading">Change Password</div>
                              <div class="panel-body">
                                  <form class="form-horizontal" role="form" action="" method="post" enctype="multipart/form-data" id="user_pass" name="user_pass">
                                      <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                      <div class="form-group <?php if(isset($error['password'])) echo 'has-error'; ?>">
                                          <label  class="col-lg-2 control-label">Current Password</label>
                                          <div class="col-lg-6">
                                              <input type="password" id="password" name="password" class="validate[required, minSize[6]] text-input form-control">
                                              <?php if(isset($error['password'])) echo '<p class="help-block">'.$error['password'].'</p>'; ?>
                                          </div>
                                      </div>
                                      <div class="form-group <?php if(isset($error['password_new1'])) echo 'has-error'; ?>">
                                          <label  class="col-lg-2 control-label">New Password</label>
                                          <div class="col-lg-6">
                                              <input type="password" id="password_new1" name="password_new1" class="validate[required, minSize[6]] text-input form-control">
                                              <?php if(isset($error['password_new1'])) echo '<p class="help-block">'.$error['password_new1'].'</p>'; ?>
                                          </div>
                                      </div>
                                      <div class="form-group <?php if(isset($error['password_new2'])) echo 'has-error'; ?>">
                                          <label  class="col-lg-2 control-label">Re-type New Password</label>
                                          <div class="col-lg-6">
                                              <input type="password" id="password_new2" name="password_new2" class="validate[required, minSize[6], equals[password_new1]] text-input form-control">
                                              <?php if(isset($error['password_new2'])) echo '<p class="help-block">'.$error['password_new2'].'</p>'; ?>
                                          </div>
                                      </div>


                                      <div class="form-group">
                                          <div class="col-lg-offset-2 col-lg-10">
                                              <input name="update_user_pass" type="submit" value="Update" class="btn btn-success" >
                                          </div>
                                      </div>
                                  </form>
                              </div>
                          </div>
                      </section>
                      <section>
                          <div class="panel panel-primary">
                              <div class="panel-heading">Change Picture</div>
                              <div class="panel-body">
                              		<form class="form-horizontal" role="form" action="" method="post" enctype="multipart/form-data" id="user_avatar" name="user_avatar">
                                      <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                      	<div class="form-group <?php if($is_error) echo 'has-error'; ?>">
                                          <label class="control-label col-lg-2">Image Upload</label>
                                          <div class="col-lg-10">
                                              <div class="fileupload fileupload-new" data-provides="fileupload">
                                                  <div class="fileupload-new thumbnail" style="width: 200px; height: 150px;">
                                                      <img src="../<?php echo $user->get_avatar(); ?>" alt="" />
                                                  </div>
                                                  <div class="fileupload-preview fileupload-exists thumbnail" style="max-width: 200px; max-height: 150px; line-height: 20px;"></div>
                                                  <div>
                                                   <span class="btn btn-white btn-file">
                                                   <span class="fileupload-new"><i class="icon-paper-clip"></i> Select image</span>
                                                   <span class="fileupload-exists"><i class="icon-undo"></i> Change</span>
                                                   <input type="file" name="avatar" id="avatar" class="validate[required] default" />
                                                   </span>
                                                      <a href="#" class="btn btn-danger fileupload-exists" data-dismiss="fileupload"><i class="icon-trash"></i> Remove</a>
                                                  </div>
                                              </div>
                                              <span class="label label-danger">NOTE!</span>
                                             <span>
                                             Attached image thumbnail is
                                             supported in Latest Firefox, Chrome, Opera,
                                             Safari and Internet Explorer 10 only
                                             </span>
                                              <?php if($is_error) echo '<p class="help-block">'.$error_image.'</p>'; ?>
                                          </div>
                                      </div>
                                      <div class="form-group">
                                          <div class="col-lg-offset-2 col-lg-10">
                                              <input name="update_user_avatar" type="submit" value="Update" class="btn btn-success" >
                                              
                                              
                                          </div>
                                      </div>
                                  </form>
                              </div>
                          </div>
                      </section>
                  </aside>
              </div>

              <!-- page end-->
          </section>
      </section>
      <!--main content end-->
      <!--footer start-->
      <?php include('inc/footer.php'); ?>
      <!--footer end-->
  </section>
  </body>
</html>