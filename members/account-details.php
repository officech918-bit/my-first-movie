<?php declare(strict_types=1);

/**
 * Account Details Page.
 *
 * @author   closemarketing
 * @license  https://www.closemarketing.com/
 * @version  1.0.0
 * @since    1.0.0
 * @package  MFM
 * @subpackage Members
 */

require_once __DIR__ . '/inc/requires.php';

// Load composer autoloader first (before anything else)
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

// Load environment variables from .env file
if (class_exists('Dotenv\Dotenv')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
}

// Load S3Uploader for environment detection
if (file_exists(__DIR__ . '/../classes/S3Uploader.php')) {
    require_once __DIR__ . '/../classes/S3Uploader.php';
    $s3Uploader = new S3Uploader();
}

if (!$user->check_session() || !$user->isActive()) {
    header('Location: index.php');
    exit();
}

$is_error = false;
$is_image = false;
$error = [];
$echo_message = "";

$first_name = $user->get('first_name');
$last_name = $user->get('last_name');
$email = $user->get('email');
$contact = $user->get('contact');
$company = $user->get('company');
$address = $user->get('address');
$city = $user->get('city');
$state = $user->get('state');
$zip = $user->get('zip');
$country = $user->get('country');
$newsletter = $user->get('newsletter');
$avatar = $user->get('avatar');
$about_me = $user->get('about_me');
$avatar_path = $user->get('avatar_path');

$company_name = $user->get_company_name();

if (isset($_POST['submit_user_data'])) {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $is_error = true;
        $echo_message = "CSRF token validation failed. Please try again.";
    } else {
        $user_id = (int)$_SESSION['uid'];
        // Get the data
        $first_name = filter_input(INPUT_POST, 'first_name', FILTER_SANITIZE_SPECIAL_CHARS);
        $last_name = filter_input(INPUT_POST, 'last_name', FILTER_SANITIZE_SPECIAL_CHARS);
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $contact = filter_input(INPUT_POST, 'contact', FILTER_SANITIZE_SPECIAL_CHARS);
        $company = filter_input(INPUT_POST, 'company', FILTER_SANITIZE_SPECIAL_CHARS);
        $address = filter_input(INPUT_POST, 'address', FILTER_SANITIZE_SPECIAL_CHARS);
        $city = filter_input(INPUT_POST, 'city', FILTER_SANITIZE_SPECIAL_CHARS);
        $state = filter_input(INPUT_POST, 'state', FILTER_SANITIZE_SPECIAL_CHARS);
        $zip = filter_input(INPUT_POST, 'zip', FILTER_SANITIZE_SPECIAL_CHARS);
        $country = filter_input(INPUT_POST, 'country', FILTER_SANITIZE_SPECIAL_CHARS);
        $newsletter = isset($_POST['newsletter']) ? 1 : 0;
        $about_me = filter_input(INPUT_POST, 'about_me', FILTER_SANITIZE_SPECIAL_CHARS);
        $date = date("Y-m-d H:i:s");

        // Validate the data
        if (empty($first_name)) {
            $is_error = true;
            $error['first_name'] = "First Name can not be empty";
        } elseif (mb_strlen($first_name) > 50) {
            $is_error = true;
            $error['first_name'] = "First Name can not more than 50 character";
        }

        // Validate email id
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $is_error = true;
            $error['email'] = "Invalid Email id entered";
        } else {
            // Validate if email id already exist
            $stmt = $database->db->prepare("SELECT email FROM web_users WHERE email = ? AND uid != ?");
            $stmt->execute([$email, $user_id]);
            if ($stmt->rowCount() > 0) {
                $is_error = true;
                $error['email'] = "User already exist with this email id!";
            }
        }
        if (empty($contact)) {
            $is_error = true;
            $error['contact'] = "Please Enter Your Contact Number";
        }
        if (empty($address)) {
            $is_error = true;
            $error['address'] = "Please Enter Your Billing Address";
        }
        if (empty($city)) {
            $is_error = true;
            $error['city'] = "Please Enter Your City";
        }
        if (empty($state)) {
            $is_error = true;
            $error['state'] = "Please Enter Your State";
        }
        if (empty($zip)) {
            $is_error = true;
            $error['zip'] = "Please Enter Your Pin Code";
        }
        if (empty($country)) {
            $is_error = true;
            $error['country'] = "Please Select Your Country";
        }

	
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $is_image = true;
            $file_tmp = $_FILES['avatar']['tmp_name'];
            $file_name = basename((string) $_FILES['avatar']['name']);
            $file_size = $_FILES['avatar']['size'];

            // Validate file extension
            $extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
            if (!in_array($extension, $allowed_extensions, true)) {
                $is_error = true;
                $error['avatar'] = "Invalid Image Format: formats required are jpeg, jpg, png, and gif.";
            }

            // Validate file size
            $maxSize = 5 * 1024 * 1024; // 5MB
            if ($file_size > $maxSize) {
                $is_error = true;
                $error['avatar'] = "Maximum image size allowed is 5MB.";
            }

            // Validate MIME type
            if (!$is_error) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime_type = finfo_file($finfo, $file_tmp);
                finfo_close($finfo);
                $allowed_mime_types = ['image/jpeg', 'image/png', 'image/gif'];
                if (!in_array($mime_type, $allowed_mime_types, true)) {
                    $is_error = true;
                    $error['avatar'] = "Invalid file type. Only JPEG, PNG, and GIF are allowed.";
                }
            }

            if (!$is_error) {
                $pic_id = bin2hex(random_bytes(8));
                $avatar = $pic_id . '_raw.' . $extension;
                $avatar_thumb = $pic_id . '_100.' . $extension;

                // Set upload paths based on environment
                if ($s3Uploader && $s3Uploader->isS3Enabled()) {
                    // S3 upload
                    $avatar_path = 'members/images/profile/';
                    $uploadPath = null; // Not used for S3
                } else {
                    // Local upload
                    $uploadPath = __DIR__ . '/images/profile/';
                    $avatar_path = 'members/images/profile/';

                    if (!file_exists($uploadPath)) {
                        mkdir($uploadPath, 0777, true);
                    }
                }

                // Delete old images if they exist
                $old_avatar = $user->get('avatar');
                if (!empty($old_avatar)) {
                    if ($s3Uploader && $s3Uploader->isS3Enabled()) {
                        // Delete from S3
                        $s3Uploader->deleteFile($avatar_path . $old_avatar);
                        $s3Uploader->deleteFile($avatar_path . str_replace('_raw.', '_100.', (string)$old_avatar));
                    } else {
                        // Delete from local storage
                        $old_avatar_raw_path = $uploadPath . $old_avatar;
                        $old_avatar_thumb_path = $uploadPath . str_replace('_raw.', '_100.', (string)$old_avatar);
                        if (file_exists($old_avatar_raw_path)) {
                            unlink($old_avatar_raw_path);
                        }
                        if (file_exists($old_avatar_thumb_path)) {
                            unlink($old_avatar_thumb_path);
                        }
                    }
                }

                // Upload the file
                if ($s3Uploader && $s3Uploader->isS3Enabled()) {
                    // Upload to S3
                    $s3Key = $avatar_path . $avatar;
                    $uploadResult = $s3Uploader->uploadFile($file_tmp, $s3Key);
                    
                    if ($uploadResult) {
                        // Create thumbnail and upload to S3
                        $tempThumbPath = sys_get_temp_dir() . '/' . $avatar_thumb;
                        
                        // Create thumbnail locally first
                        list($width) = getimagesize($file_tmp);
                        if ($width > 100) {
                            $thumb_width = 100;
                            if (function_exists('create_thumbnail')) {
                                $isThumCreated = create_thumbnail($file_tmp, $tempThumbPath, $thumb_width);
                                if ($isThumCreated) {
                                    // Upload thumbnail to S3
                                    $thumbS3Key = $avatar_path . $avatar_thumb;
                                    $s3Uploader->uploadFile($tempThumbPath, $thumbS3Key);
                                    unlink($tempThumbPath); // Clean up temp file
                                } else {
                                    $is_error = true;
                                    $error['avatar'] = "Error occurred while creating thumb image, please try again later.";
                                    // Delete the uploaded main image
                                    $s3Uploader->deleteFile($s3Key);
                                }
                            } else {
                                $is_error = true;
                                $error['avatar'] = "Thumbnail creation function not available.";
                                $s3Uploader->deleteFile($s3Key);
                            }
                        } else {
                            // If the image is smaller than the thumb width, just upload it as thumbnail
                            $thumbS3Key = $avatar_path . $avatar_thumb;
                            $s3Uploader->uploadFile($file_tmp, $thumbS3Key);
                        }
                    } else {
                        $is_error = true;
                        $error['avatar'] = "Error occurred while uploading image to cloud storage!";
                    }
                } else {
                    // Local upload
                    $destination = $uploadPath . $avatar;
                    if (move_uploaded_file($file_tmp, $destination)) {
                        list($width) = getimagesize($destination);

                        if ($width > 100) {
                            $thumb_width = 100;
                            // Assuming create_thumbnail is defined in requires.php or another included file
                            if (function_exists('create_thumbnail')) {
                                $isThumCreated = create_thumbnail($destination, $uploadPath . $avatar_thumb, $thumb_width);
                                if (!$isThumCreated) {
                                    $is_error = true;
                                    $error['avatar'] = "Error occurred while creating thumb image, please try again later.";
                                    unlink($destination); // Clean up uploaded file
                                }
                            } else {
                                $is_error = true;
                                $error['avatar'] = "Thumbnail creation function not available.";
                                unlink($destination);
                            }
                        } else {
                            // If the image is smaller than the thumb width, just copy it
                            copy($destination, $uploadPath . $avatar_thumb);
                        }
                    } else {
                        $is_error = true;
                        $error['avatar'] = "Error occurred while uploading image!";
                    }
                }
            }
        }

			
        // Process the data to the database
        if (!$is_error) {
            if ($is_image) {
                $stmt = $database->db->prepare("UPDATE web_users SET first_name=?, last_name=?, email=?, contact=?, last_update_on=?, company=?, address=?, city=?, state=?, zip=?, country=?, newsletter=?, avatar=?, avatar_thumb=?, avatar_path=?, about_me=? WHERE uid=?");
                $stmt->execute([$first_name, $last_name, $email, $contact, $date, $company, $address, $city, $state, $zip, $country, $newsletter, $avatar, $avatar_thumb, $avatar_path, $about_me, $user_id]);
            } else {
                $stmt = $database->db->prepare("UPDATE web_users SET first_name=?, last_name=?, email=?, contact=?, last_update_on=?, company=?, address=?, city=?, state=?, zip=?, country=?, newsletter=?, about_me=? WHERE uid=?");
                $stmt->execute([$first_name, $last_name, $email, $contact, $date, $company, $address, $city, $state, $zip, $country, $newsletter, $about_me, $user_id]);
            }

            if ($stmt->execute()) {
                $echo_message = "Account Details were successfully updated.";
                
                // Refresh user session data to reflect the updated avatar
                if ($is_image) {
                    // Update session with new avatar information
                    $_SESSION['avatar'] = $avatar;
                    $_SESSION['avatar_path'] = $avatar_path;
                }
            } else {
                $is_error = true;
                $echo_message = "Error while updating Database: " . $stmt->errorInfo()[2];
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

<!-- Head BEGIN -->
<head>
<meta charset="utf-8">
<title>Account Details | <?php echo e($company_name); ?></title>
<link rel="shortcut icon" href="favicon.ico">

<!-- Fonts START -->
<link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700|PT+Sans+Narrow|Source+Sans+Pro:200,300,400,600,700,900&amp;subset=all" rel="stylesheet" type="text/css">
<!-- Fonts END -->

<!-- Global styles START -->
<link href="assets/global/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet">
<link href="assets/global/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet">
<!-- Global styles END -->

<!-- Page level plugin styles START -->
<link href="assets/global/plugins/fancybox/source/jquery.fancybox.css" rel="stylesheet">
<link href="assets/global/plugins/uniform/css/uniform.default.css" rel="stylesheet" type="text/css">
<link href="assets/global/plugins/carousel-owl-carousel/owl-carousel/owl.carousel.css" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="assets/global/plugins/bootstrap-fileinput/bootstrap-fileinput.css"/>
<!-- Page level plugin styles END -->

<!-- Theme styles START -->
<link href="assets/global/css/components.css" rel="stylesheet">
<link href="assets/frontend/layout/css/style.css" rel="stylesheet">
<link href="assets/frontend/pages/css/style-shop.css" rel="stylesheet" type="text/css">
<link href="assets/frontend/layout/css/style-responsive.css" rel="stylesheet">
<link href="assets/frontend/layout/css/themes/red.css" rel="stylesheet" id="style-color">
<link href="assets/frontend/layout/css/custom.css" rel="stylesheet">
<!-- Theme styles END -->

<!-- validation -->
<link href="assets/frontend/layout/css/validationEngine.jquery.css" rel="stylesheet">
<?php include 'inc/pre-body.php'; ?>
</head>
<!-- Head END -->

<!-- Body BEGIN -->
<body class="ecommerce">
<!-- BEGIN TOP BAR -->
<?php include 'inc/header.php'; ?>
<!-- Header END -->

<div class="main">
  <div class="page-head">
    <div class="container"> 
      <!-- BEGIN PAGE TITLE -->
      <div class="page-title">
        <h1>ACCOUNT DETAILS</h1>
      </div>
      <ul class="page-breadcrumb breadcrumb pull-right">
        <li><a href="dashboard.php"><?php echo e($company_name); ?></a></li>
        <li class="active">My Account Details</li>
      </ul>
      <!-- END PAGE TITLE --> 
    </div>
  </div>
  <div class="container"> 
    
    <!-- BEGIN SIDEBAR & CONTENT -->
    <div class="row margin-bottom-40"> 
      <!-- BEGIN SIDEBAR -->
      <div class="sidebar col-md-3 col-sm-3">
        <?php include 'inc/left-menu.php'; ?>
      </div>
      <!-- END SIDEBAR --> 
      
      <!-- BEGIN CONTENT -->
      <div class="col-md-9 col-sm-7 user_right_area">
        <div class="portlet light bordered">
          <div class="portlet-title tabbable-line">
            <div class="caption font-green-sharp"> <i class="icon-speech font-green-sharp"></i> <span class="caption-subject bold uppercase"> Account Details</span> <span class="caption-helper">make it personalized...</span> </div>
            <?php include 'inc/top-menu.php'; ?>
          </div>
          <div class="portlet-body" style=" overflow:hidden; padding-bottom:20px;">
            <?php if (!empty($echo_message)) {
                echo '<div class="alert alert-success"><strong>Success!</strong> ' . e($echo_message) . '</div>';
            } ?>

            <form class="horizontal-form" name="user_data" id="user_data" action="" method="post" enctype="multipart/form-data">
              <input type="hidden" name="csrf_token" value="<?php echo e((string)$_SESSION['csrf_token']); ?>">
              <div class="form-body">
              <div class="col-md-6 col-sm-6">

                <div class="form-group <?php if ($is_error && !empty($error['first_name'])) { echo 'has-error'; } ?>">
                  <label>First Name <span class="require">*</span></label>
                  <input type="text" name="first_name" id="first_name" class="form-control validate[required]" value="<?php echo e($first_name); ?>" />
                  <span class="help-block">
                  <?php if ($is_error && !empty($error['first_name'])) { echo e($error['first_name']); } ?>
                  </span> </div>
                <div class="form-group">
                  <label for="lastname">Last Name</label>
                  <input type="text" id="last_name" name="last_name" value="<?php echo e($last_name); ?>" class="form-control">
                </div>
                <div class="form-group <?php if ($is_error && !empty($error['email'])) { echo 'has-error'; } ?>">
                  <label for="email">E-Mail <span class="require">*</span></label>
                  <input type="text" name="email" id="email" class="form-control validate[required,custom[email]]" value="<?php echo e($email); ?>" />
                  <span class="help-block">
                  <?php if ($is_error && !empty($error['email'])) { echo e($error['email']); } ?>
                  </span> </div>
                <div class="form-group <?php if ($is_error && !empty($error['contact'])) { echo 'has-error'; } ?>">
                  <label for="telephone">Contact <span class="require">*</span></label>
                  <input type="text" name="contact" id="contact" class="form-control validate[required]"  value="<?php echo e($contact); ?>" />
                  <span class="help-block">
                  <?php if ($is_error && !empty($error['contact'])) { echo e($error['contact']); } ?>
                  </span> </div>
                <div class="form-group <?php if ($is_error && !empty($error['avatar'])) { echo 'has-error'; } ?>">
                  <label class="control-label">Profile Picture</label>
                  <input type="file" name="avatar" id="avatar" />
                  <?php if ($is_error && !empty($error['avatar'])) { echo '<span class="help-block">' . e($error['avatar']) . '</span>'; } ?>
                  <div class="clearfix margin-top-10"> <span class="label label-danger"> NOTE! </span> JPEG, JPG, PNG and GIF Allowd (Max Size 5MB) </div>
                </div>
                <div class="form-group" style="padding-left:30px;">
                  <div class="checkbox">
                    <label>
                      <input name="newsletter" id="newsletter" value="1" type="checkbox" <?php if ($newsletter == 1) { echo 'checked'; } ?>>
                      I wish to subscribe to the <?php echo e($company_name); ?> Updates/Newsletters. </label>
                  </div>
                </div>
              </div>
              <div class="col-md-6 col-sm-6">

                <div class="form-group">
                  <label for="company">Company Name (Optional)</label>
                  <input type="text" id="company" name="company" class="form-control" value="<?php echo e($company); ?>">
                </div>
                <div class="form-group <?php if ($is_error && !empty($error['address'])) { echo 'has-error'; } ?>">
                  <label >Address <span class="require">*</span></label>
                  <input type="text" name="address" id="address" class="form-control validate[required]"  value="<?php echo e($address); ?>" />
                  <span class="help-block">
                  <?php if ($is_error && !empty($error['address'])) { echo e($error['address']); } ?>
                  </span> </div>
                <div class="form-group <?php if ($is_error && !empty($error['country'])) { echo 'has-error'; } ?>">
                  <label for="country">Country <span class="require">*</span></label>
                  <select class="form-control validate[required]" name="country" id="country" style="color:#333;">
                    <option value="">Select a Country</option>
                    <?php
                    $stmt = $database->db->prepare("SELECT CountryName, CountryID FROM countries ORDER BY CountryName ASC");
                    $stmt->execute();
                    while ($countries_db = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $selected = ($country === $countries_db['CountryName']) ? 'selected' : '';
                        echo '<option value="' . e($countries_db['CountryName']) . '" data-id="' . e((string)$countries_db['CountryID']) . '" ' . $selected . '>' . e($countries_db['CountryName']) . '</option>';
                    }
                    ?>
                  </select>
                  <span  class="help-block">
                  <?php if ($is_error && !empty($error['country'])) { echo e($error['country']); } ?>
                  </span> </div>
                <div  class="form-group <?php if ($is_error && !empty($error['state'])) { echo 'has-error'; } ?>">
                  <label for="states">Region/State <span class="require">*</span></label>
                  <?php if ($country == 'India') { ?>
                  <select class="form-control validate[required]" name="state" id="state" style="color:#333;" >
                    <option value="">Select State</option>
                    <?php
                    if (!empty($country)) {
                        $stmt = $database->db->prepare("SELECT CountryID FROM countries WHERE CountryName = ?");
                        $stmt->execute([$country]);
                        $arr = $stmt->fetch(PDO::FETCH_ASSOC);
                        if ($arr) {
                            $country_id = $arr['CountryID'];

                            $stmt = $database->db->prepare("SELECT StateName, StateID FROM states WHERE CountryID = ? ORDER BY StateName ASC");
                            $stmt->execute([$country_id]);
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                $selected = ($state === $row['StateName']) ? 'selected' : '';
                                echo '<option value="' . e($row['StateName']) . '" data-id="' . e((string)$row['StateID']) . '" ' . $selected . '>' . e($row['StateName']) . '</option>';
                            }
                        }
                    }
                    ?>
                  </select>
                  <?php } else { ?>
                  <input type="text" name="state" id="state2" class="form-control validate[required]"  value="<?php echo e($state); ?>" />
                  <?php } ?>
                  <span  class="help-block">
                  <?php if ($is_error && !empty($error['state'])) { echo e($error['state']); } ?>
                  </span> </div>
                <div class="form-group <?php if ($is_error && !empty($error['city'])) { echo 'has-error'; } ?>">
                  <label for="city">City <span class="require">*</span></label>
                  <?php if ($country == 'India') { ?>
                  <select class="form-control validate[required]" name="city" id="city" style="color:#333;" >
                    <option value="">Select City</option>
                    <?php
                    if (!empty($state)) {
                        $stmt = $database->db->prepare("SELECT StateID FROM states WHERE StateName = ?");
                        $stmt->execute([$state]);
                        $arr = $stmt->fetch(PDO::FETCH_ASSOC);
                        if ($arr) {
                            $state_id = $arr['StateID'];

                            $stmt = $database->db->prepare("SELECT CityName, CityID FROM cities WHERE StateID = ? ORDER BY CityName ASC");
                            $stmt->execute([$state_id]);
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                $selected = ($city === $row['CityName']) ? 'selected' : '';
                                echo '<option value="' . e($row['CityName']) . '" data-id="' . e((string)$row['CityID']) . '" ' . $selected . '>' . e($row['CityName']) . '</option>';
                            }
                        }
                        }
                    ?>
                  </select>
                  <?php } else { ?>
                  <input type="text" name="city" id="city2" class="form-control validate[required]"  value="<?php echo e($city); ?>" />
                  <?php } ?>
                  <span class="help-block">
                  <?php if ($is_error && !empty($error['city'])) { echo e($error['city']); } ?>
                  </span> </div>
                <div class="form-group <?php if ($is_error && !empty($error['zip'])) { echo 'has-error'; } ?>">
                  <label for="post-code">Zip Code/Postal Code <span class="require">*</span></label>
                  <input type="text" id="zip" name="zip" class="form-control validate[required]"  value="<?php echo e($zip); ?>" />
                  <span class="help-block">
                  <?php if ($is_error && !empty($error['zip'])) { echo e($error['zip']); } ?>
                  </span> </div>
              </div>
              <div class="form-group">
                    <label class="control-label col-md-12">About me</label>
                    <textarea class="form-control" name="about_me" id="about_me" rows="6"><?php echo e($about_me); ?></textarea>
                </div>

              <div class="form-actions">
                <button class="btn btn-primary " name="submit_user_data" type="submit"  > Save My Details </button>
              </div>
              </div>
            </form>
          </div>
        </div>
      </div>
      <!-- END CONTENT -->
    </div>
    <!-- END SIDEBAR & CONTENT -->
  </div>
</div>

<!-- BEGIN FOOTER -->
<?php include 'inc/footer.php'; ?>
<!-- END FOOTER -->

<!-- Load javascripts at bottom, this will reduce page load time -->
<!-- BEGIN CORE PLUGINS(REQUIRED FOR ALL PAGES) -->
<!--[if lt IE 9]>
    <script src="assets/global/plugins/respond.min.js"></script>  
    <![endif]--> 
<script src="assets/global/plugins/jquery.min.js" type="text/javascript"></script> 
<script src="assets/global/plugins/jquery-migrate.min.js" type="text/javascript"></script> 
<script src="assets/global/plugins/bootstrap/js/bootstrap.min.js" type="text/javascript"></script> 
<script src="assets/frontend/layout/scripts/back-to-top.js" type="text/javascript"></script> 
<script src="assets/global/plugins/jquery-slimscroll/jquery.slimscroll.min.js" type="text/javascript"></script> 
<!-- END CORE PLUGINS --> 

<!-- BEGIN PAGE LEVEL JAVASCRIPTS (REQUIRED ONLY FOR CURRENT PAGE) --> 
<!--<script src="assets/global/plugins/fancybox/source/jquery.fancybox.pack.js" type="text/javascript"></script> 
<script src="assets/global/plugins/carousel-owl-carousel/owl-carousel/owl.carousel.min.js" type="text/javascript"></script>  --> 

<script src="assets/global/plugins/uniform/jquery.uniform.min.js" type="text/javascript"></script> 

<!--<script src="assets/frontend/layout/scripts/layout.js" type="text/javascript"></script> --> 
<script type="text/javascript">
        jQuery(document).ready(function() {  
            //Layout.init();    
            //Layout.initOWL();
			//Layout.initUniform();
            //Layout.initFixHeaderWithPreHeader(); /* Switch On Header Fixing (only if you have pre-header) */
        });
    </script> 
<!-- END PAGE LEVEL JAVASCRIPTS --> 

<script src="assets/frontend/layout/scripts/jquery.validationEngine-en.js" type="text/javascript"></script> 
<script src="assets/frontend/layout/scripts/jquery.validationEngine.js" type="text/javascript"></script> 
<script>
            jQuery(document).ready(function(){
                // binds form submission and fields to the validation engine
                jQuery("#user_data").validationEngine();
				
				
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
			'url' : 'update_states.php',
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
			'url' : 'update_cities.php',
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