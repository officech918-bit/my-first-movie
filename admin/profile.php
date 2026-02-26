<?php
use App\Models\WebUser;
use App\Services\Validator;

// 1. Bootstrap the application
// Note: The original file included 'inc/requires.php'. We assume 'vendor/autoload.php' and 'config/database.php' replace this.
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/database.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Authentication and Authorization
if (!isset($_SESSION['uid'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['uid'];
$current_user = WebUser::find($user_id);

if (!$current_user) {
    session_destroy();
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['format']) && $_GET['format'] === 'json') {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'uid' => (int)$current_user->uid,
            'first_name' => (string)$current_user->first_name,
            'last_name' => (string)$current_user->last_name,
            'email' => (string)$current_user->email,
            'contact' => (string)$current_user->contact,
            'avatar' => (string)$current_user->avatar,
            'avatar_thumb' => (string)$current_user->avatar_thumb,
            'avatar_path' => (string)$current_user->avatar_path,
            'updated_at' => (string)$current_user->last_update_on
        ]);
        exit;
    } else {
        header('Location: profile-modern.php');
        exit;
    }
}

// Handle Change Password Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user_pass'])) {
    // 1. CSRF Validation
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $_SESSION['errors'] = ['csrf' => ['CSRF token mismatch.']];
        $_SESSION['active_tab'] = 3;
        header('Location: profile-modern.php');
        exit;
    }

    // 2. Validation
    $validator = new Validator();
    $validator->make($_POST, [
        'password' => 'required',
        'password_new1' => 'required|min:6',
        'password_new2' => 'required|same:password_new1',
    ]);

    if ($validator->fails()) {
        $_SESSION['errors'] = $validator->getErrors();
        $_SESSION['active_tab'] = 3;
        header('Location: profile-modern.php');
        exit;
    }

    // 3. Verify old password and update
    if (password_verify($_POST['password'], $current_user->password)) {
        $current_user->password = password_hash($_POST['password_new1'], PASSWORD_DEFAULT);
        if ($current_user->save()) {
            $_SESSION['success'] = 'Password updated successfully!';
        } else {
            $_SESSION['errors'] = ['database' => ['Failed to update password.']];
        }
    } else {
        $_SESSION['errors'] = ['password' => ['Your current password does not match.']];
    }

    $_SESSION['active_tab'] = 3;
    header('Location: profile-modern.php');
    exit;
}

// Handle Avatar Upload Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user_avatar'])) {
    // 1. CSRF Validation
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $_SESSION['errors'] = ['csrf' => ['CSRF token mismatch.']];
        $_SESSION['active_tab'] = 2;
        header('Location: profile-modern.php');
        exit;
    }

    // 2. File Upload Handling
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['avatar'];
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (in_array($file['type'], $allowed_types) && $file['size'] <= 2 * 1024 * 1024) {
            $upload_dir = __DIR__ . '/assets/images/users/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $file_name = bin2hex(random_bytes(16)) . '.' . $extension;
            $thumb_name = bin2hex(random_bytes(16)) . '_thumb.' . $extension;
            $destination = $upload_dir . $file_name;

            if (move_uploaded_file($file['tmp_name'], $destination)) {
                // Optionally, create a thumbnail
                // create_thumbnail($destination, $upload_dir . $thumb_name, 200);

                // Delete old avatar if it exists
                if ($current_user->avatar && file_exists($upload_dir . $current_user->avatar)) {
                    unlink($upload_dir . $current_user->avatar);
                }
                if ($current_user->avatar_thumb && file_exists($upload_dir . $current_user->avatar_thumb)) {
                    unlink($upload_dir . $current_user->avatar_thumb);
                }

                $current_user->avatar = $file_name;
                $current_user->avatar_thumb = $thumb_name; // Or just the file_name if no thumb
                $current_user->avatar_path = 'admin/assets/images/users/';
                if ($current_user->save()) {
                    $_SESSION['success'] = 'Avatar updated successfully!';
                } else {
                    $_SESSION['errors'] = ['database' => ['Failed to update avatar.']];
                }
            } else {
                $_SESSION['errors'] = ['avatar' => ['Failed to move uploaded file.']];
            }
        } else {
            $_SESSION['errors'] = ['avatar' => ['Invalid file type or size (max 2MB).']];
        }
    } else {
        $_SESSION['errors'] = ['avatar' => ['File upload error.']];
    }

    $_SESSION['active_tab'] = 2;
    header('Location: profile-modern.php');
    exit;
}

// Determine menu based on user type
$menu = 'inc/left-menu-user.php';
if ($_SESSION['user_type'] === 'webmaster') {
    $menu = 'inc/left-menu-webmaster.php';
} elseif ($_SESSION['user_type'] === 'admin') {
    $menu = 'inc/left-menu-admin.php';
}

// 3. CSRF Token Management
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// Handle Personal Info Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit']) && $_POST['submit'] === 'personal_info') {
    // 1. CSRF Validation
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        // CSRF token mismatch, handle the error as you see fit
        // For now, we'll just redirect back with an error
        $_SESSION['errors'] = ['csrf' => ['CSRF token mismatch.']];
        $_SESSION['active_tab'] = 1;
        header('Location: profile.php');
        exit;
    }

    // 2. Validation
    $validator = new Validator();
    $validator->make($_POST, [
        'first_name' => 'required',
        'last_name' => 'required',
        'email' => 'required|email',
    ]);

    if ($validator->fails()) {
        $_SESSION['errors'] = $validator->getErrors();
        $_SESSION['old'] = $_POST;
        $_SESSION['active_tab'] = 1;
        header('Location: profile.php');
        exit;
    }

    // 3. Update User
    $current_user->first_name = $_POST['first_name'];
    $current_user->last_name = $_POST['last_name'];
    $current_user->email = $_POST['email'];
    $current_user->contact = $_POST['contact'] ?? $current_user->contact;
    
    if ($current_user->save()) {
        $_SESSION['success'] = 'Profile updated successfully!';
    } else {
        $_SESSION['errors'] = ['database' => ['Failed to update profile.']];
    }

    $_SESSION['active_tab'] = 1;
    header('Location: profile.php');
    exit;
}


// 4. Prepare for view
$errors = $_SESSION['errors'] ?? [];
$old_input = $_SESSION['old'] ?? [];
$success_message = $_SESSION['success'] ?? null;
$tab_id = $_SESSION['active_tab'] ?? $_GET['tab'] ?? 1;

unset($_SESSION['errors'], $_SESSION['old'], $_SESSION['success'], $_SESSION['active_tab']);

// Helper function to retrieve old form data
function old($key, $default = '') {
    global $old_input;
    return $old_input[$key] ?? $default;
}

// Prepare user data for the form, using old input if available
$first_name = old('first_name', $current_user->first_name);
$last_name = old('last_name', $current_user->last_name);
$email = old('email', $current_user->email);
$contact = old('contact', $current_user->contact);
$avatar = !empty($current_user->avatar_thumb) ? '../' . $current_user->avatar_path . $current_user->avatar_thumb : 'assets/admin/layout/img/avatar.png';
$avatar_large = !empty($current_user->avatar) ? '../' . $current_user->avatar_path . $current_user->avatar : 'assets/admin/layout/img/avatar.png';

	
	
	
		
	
	
	
	
	

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
<title>Profile | Project Alert</title>
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
<link href="assets/global/plugins/bootstrap-fileinput/bootstrap-fileinput.css" rel="stylesheet" type="text/css"/>
<link href="assets/admin/pages/css/profile.css" rel="stylesheet" type="text/css"/>
<link href="assets/admin/pages/css/tasks.css" rel="stylesheet" type="text/css"/>
<!-- END PAGE LEVEL STYLES -->
<!-- BEGIN THEME STYLES -->
<link href="assets/global/css/components.css" id="style_components" rel="stylesheet" type="text/css"/>
<link href="assets/global/css/plugins.css" rel="stylesheet" type="text/css"/>
<link href="assets/admin/layout/css/layout.css" rel="stylesheet" type="text/css"/>
<link id="style_color" href="assets/admin/layout/css/themes/darkblue.css" rel="stylesheet" type="text/css"/>
<link href="assets/admin/layout/css/custom.css" rel="stylesheet" type="text/css"/>

<link rel="stylesheet" href="assets/admin/layout/css/validationEngine.jquery.css" type="text/css"/>
<!-- END THEME STYLES -->
<link rel="shortcut icon" href="favicon.ico"/>
</head>
<!-- END HEAD -->
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
		<!-- DOC: Set data-auto-scroll="false" to disable the sidebar from auto scrolling/focusing -->
		<!-- DOC: Change data-auto-speed="200" to adjust the sub menu slide up/down speed -->
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
			<div class="page-bar">
				<ul class="page-breadcrumb">
					<li>
						<i class="fa fa-home"></i>
						<a href="dashboard.php">Dashboard</a>
						<i class="fa fa-angle-right"></i>
					</li>
					<li>
						<a href="javascript:;">Profile</a>
					</li>
				</ul>
				
			</div>
			<!-- END PAGE HEADER-->
			<!-- BEGIN PAGE CONTENT-->
			<div class="row margin-top-20">
				<div class="col-md-12">
					<!-- BEGIN PROFILE SIDEBAR -->
					<div class="profile-sidebar">
						<!-- PORTLET MAIN -->
						<div class="portlet light profile-sidebar-portlet pull-right">
							<!-- SIDEBAR USERPIC -->
							<div class="profile-userpic">
                            	<?php if($avatar != '') echo '<img src="../'.$avatar_large.'" class="img-responsive" alt="">'; else echo '<img src="http://www.placehold.it/200x150/EFEFEF/AAAAAA&amp;text=no+image" class="img-responsive" alt="">';?>
								
							</div>
							<!-- END SIDEBAR USERPIC -->
							<!-- SIDEBAR USER TITLE -->
							<div class="profile-usertitle">
								<div class="profile-usertitle-name">
									 <?php echo $first_name . ' ' . $last_name ?>
								</div>
								<div class="profile-usertitle-job">
									 
								</div>
							</div>
							<!-- END SIDEBAR USER TITLE -->
						</div>
						<!-- END PORTLET MAIN -->
					</div>
					<!-- END BEGIN PROFILE SIDEBAR -->
					<!-- BEGIN PROFILE CONTENT -->
					<div class="profile-content">
						<div class="row">
							<div class="col-md-12">
								<div class="portlet light">
									<div class="portlet-title tabbable-line">
										<div class="caption caption-md">
											<i class="icon-globe theme-font hide"></i>
											<span class="caption-subject font-blue-madison bold uppercase">Profile Account</span>
										</div>
										<ul class="nav nav-tabs">
											<li <?php if($tab_id == 1) echo 'class="active"' ?>>
												<a href="#tab_1_1" data-toggle="tab">Personal Info</a>
											</li>
											<li <?php if($tab_id == 2) echo 'class="active"' ?>>
												<a href="#tab_1_2" data-toggle="tab">Change Avatar</a>
											</li>
											<li <?php if($tab_id == 3) echo 'class="active"' ?>>
												<a href="#tab_1_3" data-toggle="tab">Change Password</a>
											</li>
										</ul>
									</div>
									<div class="portlet-body">
										<div class="tab-content">
											<!-- PERSONAL INFO TAB -->
											<div class="tab-pane <?php if($tab_id == 1) echo 'active' ?>" id="tab_1_1">
                                                <?php if ($success_message): ?>
                                                    <div class="alert alert-success"><strong>Success!</strong> <?= htmlspecialchars($success_message) ?></div>
                                                <?php endif; ?>
                                                <?php if (!empty($errors) && (isset($errors['database']) || isset($errors['csrf']))): ?>
                                                    <div class="alert alert-danger">
                                                        <ul>
                                                            <?php foreach (($errors['database'] ?? []) as $error): ?><li><?= htmlspecialchars($error) ?></li><?php endforeach; ?>
                                                            <?php foreach (($errors['csrf'] ?? []) as $error): ?><li><?= htmlspecialchars($error) ?></li><?php endforeach; ?>
                                                        </ul>
                                                    </div>
                                                <?php endif; ?>
												<form class="form-horizontal" role="form" action="profile.php" method="post">
									                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                                                  <div class="form-group <?= isset($errors['first_name']) ? 'has-error' : '' ?>">
                                                      <label  class="col-lg-2 control-label">First Name</label>
                                                      <div class="col-lg-6">
                                                          <input type="text" name="first_name" value="<?= htmlspecialchars($first_name) ?>" class="form-control" />
                                                          <?php if (isset($errors['first_name'])): ?><p class="help-block"><?= htmlspecialchars($errors['first_name'][0]) ?></p><?php endif; ?>
                                                      </div>
                                                  </div>
                                                  <div class="form-group <?= isset($errors['last_name']) ? 'has-error' : '' ?>">
                                                      <label  class="col-lg-2 control-label">Last Name</label>
                                                      <div class="col-lg-6">
                                                          <input type="text" name="last_name" value="<?= htmlspecialchars($last_name) ?>" class="form-control" />
                                                          <?php if (isset($errors['last_name'])): ?><p class="help-block"><?= htmlspecialchars($errors['last_name'][0]) ?></p><?php endif; ?>
                                                      </div>
                                                  </div>
                                                  <div class="form-group <?= isset($errors['email']) ? 'has-error' : '' ?>">
                                                      <label  class="col-lg-2 control-label">Email</label>
                                                      <div class="col-lg-6">
                                                          <input type="text" name="email" class="form-control" value="<?= htmlspecialchars($email) ?>" />
                                                          <?php if (isset($errors['email'])): ?><p class="help-block"><?= htmlspecialchars($errors['email'][0]) ?></p><?php endif; ?>
                                                      </div>
                                                  </div>
                                                  <div class="form-group">
                                                      <label  class="col-lg-2 control-label">Contact</label>
                                                      <div class="col-lg-6">
                                                          <input type="text" class="form-control" name="contact" value="<?= htmlspecialchars($contact) ?>" >
                                                      </div>
                                                  </div>
                                                  <div class="form-group">
                                                      <div class="col-lg-offset-2 col-lg-10">
                                                          <button type="submit" name="submit" value="personal_info" class="btn btn-success">Update</button>
                                                      </div>
                                                  </div>
                                              </form>
											</div>
											<!-- END PERSONAL INFO TAB -->
											<!-- CHANGE AVATAR TAB -->
											<div class="tab-pane <?php if($tab_id == 2) echo 'active' ?>" id="tab_1_2">
                                            	<?php 
													if(isset($_SESSION['msg2'])) echo '<div class="alert alert-success"><strong>Success!</strong> '.$_SESSION['msg2'].'</div>';
												?>
												<form class="form-horizontal" role="form" action="" method="post" enctype="multipart/form-data" id="user_avatar" name="user_avatar">
													<input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
													<div class="form-group <?php if(isset($error['avatar'])) echo 'has-error'; ?>">
														<div class="fileinput fileinput-new" data-provides="fileinput">
															<div class="fileinput-new thumbnail" style="width: 200px; height: 150px;">
                                                            	<?php if($avatar != '') echo '<img src="../'.$avatar.'" alt=""/>'; else echo '<img src="http://www.placehold.it/200x150/EFEFEF/AAAAAA&amp;text=no+image" alt=""/>';?>
																
															</div>
															<div class="fileinput-preview fileinput-exists thumbnail" style="max-width: 200px; max-height: 150px;">
															</div>
															<div>
																<span class="btn default btn-file">
																<span class="fileinput-new">
																Select image </span>
																<span class="fileinput-exists">
																Change </span>
                                                                <input type="file" name="avatar" id="avatar" class="validate[required] default" />
																</span>
																<a href="#" class="btn default fileinput-exists" data-dismiss="fileinput">
																Remove </a>
                                                                <?php if(isset($error['avatar'])) echo '<p class="help-block">'.$error['avatar'].'</p>'; ?>
															</div>
														</div>
														<div class="clearfix margin-top-10">
															<span class="label label-danger">NOTE! </span>
															<span>Max Width:500px, Max Height: 500px, Max Size 2MB</span>
														</div>
													</div>
													<div class="margin-top-10">
														<input name="update_user_avatar" type="submit" value="Update" class="btn btn-success" >
													</div>
												</form>
											</div>
											<!-- END CHANGE AVATAR TAB -->
											<!-- CHANGE PASSWORD TAB -->
											<div class="tab-pane <?php if($tab_id == 3) echo 'active' ?>" id="tab_1_3">
                                            	<?php 
													if(isset($_SESSION['msg3'])) echo '<div class="alert alert-success"><strong>Success!</strong> '.$_SESSION['msg3'].'</div>';
												?>
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
											<!-- END CHANGE PASSWORD TAB -->
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<!-- END PROFILE CONTENT -->
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
<script src="assets/global/plugins/bootstrap-fileinput/bootstrap-fileinput.js" type="text/javascript"></script>
<script src="assets/global/plugins/jquery.sparkline.min.js" type="text/javascript"></script>
<!-- END PAGE LEVEL PLUGINS -->
<!-- BEGIN PAGE LEVEL SCRIPTS -->
<script src="assets/global/scripts/metronic.js" type="text/javascript"></script>
<script src="assets/admin/layout/scripts/layout.js" type="text/javascript"></script>
<script src="assets/admin/layout/scripts/demo.js" type="text/javascript"></script>
<script src="assets/admin/pages/scripts/profile.js" type="text/javascript"></script>
<!-- END PAGE LEVEL SCRIPTS -->
<script>
jQuery(document).ready(function() {       
   // initiate layout and plugins
   Metronic.init(); // init metronic core components
	Layout.init(); // init current layout
	Demo.init(); // init demo features
	Profile.init(); // init page demo
});
</script>

<!-- validation --> 

<script src="assets/admin/layout/scripts/jquery.validationEngine-en.js" type="text/javascript" charset="utf-8"></script> 
<script src="assets/admin/layout/scripts/jquery.validationEngine.js" type="text/javascript" charset="utf-8"></script> 
<script>
        jQuery(document).ready(function(){
            // binds form submission and fields to the validation engine
            jQuery("#user_data").validationEngine();
			jQuery("#user_pass").validationEngine();
			jQuery("#user_avatar").validationEngine();
        });
    </script> 
<!-- END JAVASCRIPTS -->
</body>
<?php 
unset($_SESSION['msg1']);
unset($_SESSION['msg2']);
unset($_SESSION['msg3']);
?>
<!-- END BODY -->
</html>