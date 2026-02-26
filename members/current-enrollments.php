<?php
/**
 * Member Current Enrollments Page.
 *
 * @package MFM
 * @subpackage Members
 */
declare(strict_types=1);

require_once 'inc/requires.php';

// Load environment variables from .env file
if (class_exists('Dotenv\Dotenv')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
}

if (!$user->check_session()) {
    header('Location: index.php');
    exit();
}

if (!$user->isActive()) {
    header('Location: activate.php');
    exit();
}

$company_name = $user->get_company_name();
$uid = (int)$_SESSION['uid'];

// Determine if we are in edit mode
$edit_mode = false;
$enrollment_data = null;
$enrollment_id = 0;
$initial_fee = 0.0;

if (filter_has_var(INPUT_GET, 'id')) {
    $enrollment_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    
    if ($enrollment_id) {
        $edit_mode = true;
        $stmt = $database->db->prepare("SELECT * FROM enrollments WHERE id = ? AND uid = ?");
        $stmt->execute([$enrollment_id, $uid]);
        $enrollment_data = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        if (!$enrollment_data) {
            header('Location: dashboard.php?error=notfound');
            exit();
        }
        $initial_fee = (float)$enrollment_data['fee'];
    }
}

if (isset($_POST['enrol_submit'])) {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die('A security error occurred. Please try again.');
    }

    $category = $_POST['category'] ?? '';
    $explanation = $_POST['editor2'] ?? '';
    $fee = $_POST['hfee'] ?? 0.0;

    // --- File Deletion ---
    if ($edit_mode && !empty($_POST['files_to_delete'])) {
        $base_dir = realpath(__DIR__ . "/../uploads/" . $uid . "/" . $enrollment_data['title']);
        if ($base_dir) {
            foreach ($_POST['files_to_delete'] as $file_to_delete) {
                // Sanitize to prevent path traversal
                $safe_path = str_replace('..', '', $file_to_delete);
                $full_path = $base_dir . DIRECTORY_SEPARATOR . $safe_path;
                if (file_exists($full_path) && strpos(realpath($full_path), $base_dir) === 0) {
                    unlink($full_path);
                }
            }
        }
    }

    // --- File Upload ---
    if (isset($_FILES['docs']['name']) && is_array($_FILES['docs']['name'])) {
        // Create unique directory name with enrollment ID (for new enrollments) or existing directory (for edits)
        $dir_name = $edit_mode ? $enrollment_data['title'] : ($enrollment_id ? $enrollment_id . '_' . $category : $category);
        $upload_dir = __DIR__ . "/../uploads/" . $uid . "/" . $dir_name;
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // Check if S3 is enabled via environment variable
        $useS3 = !empty($_ENV['S3_BASE_URL']);
        $s3BaseUrl = $useS3 ? rtrim($_ENV['S3_BASE_URL'], '/') : null;

        foreach ($_FILES['docs']['name'] as $i => $name) {
            if (!empty($name) && $_FILES['docs']['error'][$i] === UPLOAD_ERR_OK) {
                $file_name = basename($name);
                $tmp_name = $_FILES['docs']['tmp_name'][$i];
                $ext = strtoupper(pathinfo($file_name, PATHINFO_EXTENSION) ?: 'MISC');
                $ext_dir = $upload_dir . "/" . $ext;
                if (!is_dir($ext_dir)) {
                    mkdir($ext_dir, 0777, true);
                }
                
                // Always upload locally as backup
                move_uploaded_file($tmp_name, $ext_dir . "/" . $file_name);
                
                // If S3 is enabled, we could implement S3 upload here in the future
                // For now, files are stored locally and S3 URLs are not used for enrollments
                // (enrollment files are typically private and don't need public S3 URLs)
            }
        }
    }

    // --- Recalculate File Count ---
    $final_file_count = 0;
    $scan_dir = __DIR__ . "/../uploads/" . $uid . "/" . $dir_name . "/";
    if (is_dir($scan_dir)) {
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($scan_dir, RecursiveDirectoryIterator::SKIP_DOTS));
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $final_file_count++;
            }
        }
    }

    // --- Database Operation ---
    // Get category_id from the category title
    $stmt_cat = $database->db->prepare("SELECT id FROM categories WHERE title = ? AND status = '1'");
    $stmt_cat->execute([$category]);
    $category_data = $stmt_cat->fetch(PDO::FETCH_ASSOC);
    $category_id = $category_data ? $category_data['id'] : null;
    $stmt_cat->closeCursor();
    
    // Get current active season_id
    $stmt_season = $database->db->prepare("SELECT id FROM seasons WHERE status = 'ACTIVE' OR status = 1 ORDER BY id DESC LIMIT 1");
    $stmt_season->execute();
    $season_data = $stmt_season->fetch(PDO::FETCH_ASSOC);
    $season_id = $season_data ? $season_data['id'] : null;
    $stmt_season->closeCursor();
    
    if ($edit_mode) {
        $stmt = $database->db->prepare("UPDATE enrollments SET title = ?, explanation = ?, no_of_files = ?, fee = ?, category_id = ?, season_id = ? WHERE id = ? AND uid = ?");
        $stmt->execute([$category, $explanation, $final_file_count, $fee, $category_id, $season_id, $enrollment_id, $uid]);
    } else {
        $time = date("Y-m-d H:i:s");
        $status = 'pending';
        $stmt = $database->db->prepare("INSERT INTO enrollments (uid, title, explanation, no_of_files, fee, dt, status, category_id, season_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$uid, $category, $explanation, $final_file_count, $fee, $time, $status, $category_id, $season_id]);
        $enrollment_id = $database->db->lastInsertId();
        
        // For new enrollments, move files from temp directory to final directory with enrollment ID
        if ($enrollment_id && isset($upload_dir)) {
            $final_dir_name = $enrollment_id . '_' . $category;
            $final_upload_dir = __DIR__ . "/../uploads/" . $uid . "/" . $final_dir_name;
            
            if ($upload_dir !== $final_upload_dir && is_dir($upload_dir)) {
                rename($upload_dir, $final_upload_dir);
            }
        }
    }
    $stmt->closeCursor();

    if ($edit_mode && $enrollment_data['status'] === 'completed') {
        // Redirect to enrollment-step2.php to allow address updates for completed enrollments
        header("Location: enrollment-step2.php?id=" . $enrollment_id . "&success=updated");
    } else {
        header("Location: enrollment-step2.php?id=" . $enrollment_id);
    }
    exit();
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
<title>Current Enrollments | <?= e($company_name) ?></title>
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
<!-- Page level plugin styles END -->

<!-- Theme styles START -->
<link href="assets/global/css/components.css" rel="stylesheet">
<link href="assets/frontend/layout/css/style.css" rel="stylesheet">
<link href="assets/frontend/pages/css/style-shop.css" rel="stylesheet" type="text/css">
<link href="assets/frontend/layout/css/style-responsive.css" rel="stylesheet">
<link href="assets/frontend/layout/css/themes/red.css" rel="stylesheet" id="style-color">
<link href="assets/frontend/layout/css/custom.css" rel="stylesheet">
<!-- Theme styles END -->

<!-- BEGIN PAGE LEVEL STYLES -->
<link href="assets/global/plugins/jquery-file-upload/blueimp-gallery/blueimp-gallery.min.css" rel="stylesheet"/>
<link rel="stylesheet" type="text/css" href="assets/global/plugins/bootstrap-fileinput/bootstrap-fileinput.css"/>
<!-- END PAGE LEVEL STYLES -->

<!-- validation -->
<link href="assets/frontend/layout/css/validationEngine.jquery.css" rel="stylesheet">
<meta content="width=device-width, initial-scale=1.0" name="viewport">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">


<link rel="stylesheet" href="source/jquery-labelauty.css" type="text/css" media="screen" charset="utf-8" />
<link rel="stylesheet" href="source/lby-main.css" type="text/css" media="screen" charset="utf-8" />
<style>
@-webkit-keyframes myanimation {
 from {
 left: 0%;
}
to {  
	left: 50%;
}
}

.checkout-wrap {
	color: #444;
	font-family: 'PT Sans Caption', sans-serif;
	width: 100%;
	min-height: 50px;
	overflow: hidden;
}
ul.checkout-bar {
	margin: 0 20px;
}
ul.checkout-bar li {
	color: #ccc;
	display: block;
	font-size: 16px;
	font-weight: 600;
	padding: 14px 20px 14px 80px;
	position: relative;
}
ul.checkout-bar li:before {
	-webkit-box-shadow: inset 2px 2px 2px 0px rgba(0, 0, 0, 0.2);
	box-shadow: inset 2px 2px 2px 0px rgba(0, 0, 0, 0.2);
	background: #ddd;
	border: 2px solid #FFF;
	border-radius: 50%;
	color: #fff;
	font-size: 16px;
	font-weight: 700;
	left: 20px;
	line-height: 37px;
	height: 35px;
	position: absolute;
	text-align: center;
	text-shadow: 1px 1px rgba(0, 0, 0, 0.2);
	top: 4px;
	width: 35px;
	z-index: 999;
}
ul.checkout-bar li.active {
	color: #8bc53f;
	font-weight: bold;
}
ul.checkout-bar li.active:before {
	background: #8bc53f;
	z-index: 99999;
}
ul.checkout-bar li.visited {
	background: #ECECEC;
	color: #57aed1;
	z-index: 99999;
}
ul.checkout-bar li.visited:before {
	background: #57aed1;
	z-index: 99999;
}
ul.checkout-bar li:nth-child(1):before {
	content: "1";
}
ul.checkout-bar li:nth-child(2):before {
	content: "2";
}
ul.checkout-bar li:nth-child(3):before {
	content: "3";
}
ul.checkout-bar li:nth-child(4):before {
	content: "4";
}
ul.checkout-bar li:nth-child(5):before {
	content: "5";
}
ul.checkout-bar li:nth-child(6):before {
	content: "6";
}
ul.checkout-bar a {
	color: #57aed1;
	font-size: 16px;
	font-weight: 600;
	text-decoration: none;
}
 @media all and (min-width: 800px) {
.checkout-bar li.active:after {
	-webkit-animation: myanimation 3s 0;
	background-size: 35px 35px;
	background-color: #8bc53f;
	background-image: -webkit-linear-gradient(-45deg, rgba(255, 255, 255, 0.2) 25%, transparent 25%, transparent 50%, rgba(255, 255, 255, 0.2) 50%, rgba(255, 255, 255, 0.2) 75%, transparent 75%, transparent);
	background-image: -moz-linear-gradient(-45deg, rgba(255, 255, 255, 0.2) 25%, transparent 25%, transparent 50%, rgba(255, 255, 255, 0.2) 50%, rgba(255, 255, 255, 0.2) 75%, transparent 75%, transparent);
	-webkit-box-shadow: inset 2px 2px 2px 0px rgba(0, 0, 0, 0.2);
	box-shadow: inset 2px 2px 2px 0px rgba(0, 0, 0, 0.2);
	content: "";
	height: 15px;
	width: 100%;
	left: 50%;
	position: absolute;
	top: -50px;
	z-index: 0;
}
.checkout-wrap {
	margin: 20px auto 40px auto;
}
ul.checkout-bar {
	-webkit-box-shadow: inset 2px 2px 2px 0px rgba(0, 0, 0, 0.2);
	box-shadow: inset 2px 2px 2px 0px rgba(0, 0, 0, 0.2);
	background-size: 35px 35px;
	background-color: #EcEcEc;
	background-image: -webkit-linear-gradient(-45deg, rgba(255, 255, 255, 0.4) 25%, transparent 25%, transparent 50%, rgba(255, 255, 255, 0.4) 50%, rgba(255, 255, 255, 0.4) 75%, transparent 75%, transparent);
	background-image: -moz-linear-gradient(-45deg, rgba(255, 255, 255, 0.4) 25%, transparent 25%, transparent 50%, rgba(255, 255, 255, 0.4) 50%, rgba(255, 255, 255, 0.4) 75%, transparent 75%, transparent);
	border-radius: 15px;
	height: 15px;
	margin: 0 auto;
	padding: 0;
	position: absolute;
	width: 94%;
}
ul.checkout-bar:before {
	background-size: 35px 35px;
	background-color: #57aed1;
	background-image: -webkit-linear-gradient(-45deg, rgba(255, 255, 255, 0.2) 25%, transparent 25%, transparent 50%, rgba(255, 255, 255, 0.2) 50%, rgba(255, 255, 255, 0.2) 75%, transparent 75%, transparent);
	background-image: -moz-linear-gradient(-45deg, rgba(255, 255, 255, 0.2) 25%, transparent 25%, transparent 50%, rgba(255, 255, 255, 0.2) 50%, rgba(255, 255, 255, 0.2) 75%, transparent 75%, transparent);
	-webkit-box-shadow: inset 2px 2px 2px 0px rgba(0, 0, 0, 0.2);
	box-shadow: inset 2px 2px 2px 0px rgba(0, 0, 0, 0.2);
	border-radius: 15px;
	content: " ";
	height: 15px;
	left: 0;
	position: absolute;
	width: 50%;
}
ul.checkout-bar li {
	display: inline-block;
	margin: 50px 0 0;
	padding: 0;
	text-align: center;
	width: 40%;
}
ul.checkout-bar li:before {
	height: 45px;
	left: 50%;
	line-height: 45px;
	position: absolute;
	top: -65px;
	width: 45px;
	z-index: 99;
}
ul.checkout-bar li.visited {
	background: none;
}
ul.checkout-bar li.visited:after {
	background-size: 35px 35px;
	background-color: #57aed1;
	background-image: -webkit-linear-gradient(-45deg, rgba(255, 255, 255, 0.2) 25%, transparent 25%, transparent 50%, rgba(255, 255, 255, 0.2) 50%, rgba(255, 255, 255, 0.2) 75%, transparent 75%, transparent);
	background-image: -moz-linear-gradient(-45deg, rgba(255, 255, 255, 0.2) 25%, transparent 25%, transparent 50%, rgba(255, 255, 255, 0.2) 50%, rgba(255, 255, 255, 0.2) 75%, transparent 75%, transparent);
	-webkit-box-shadow: inset 2px 2px 2px 0px rgba(0, 0, 0, 0.2);
	box-shadow: inset 2px 2px 2px 0px rgba(0, 0, 0, 0.2);
	content: "";
	height: 15px;
	left: 50%;
	position: absolute;
	top: -50px;
	width: 100%;
	z-index: 99;
}
}

.fee-display {
  background-color: teal;
    padding: 10px;
    padding-left: 18px;
    color: white;
    font-weight: bold;
}
</style>
</head>
<!-- Head END -->

<body class="ecommerce">
<!-- BEGIN HEADER -->
<?php include 'inc/header.php'; ?>
<!-- Header END -->

<div class="main">
  <div class="page-head">
    <div class="container"> 
      <!-- BEGIN PAGE TITLE -->
      <div class="page-title">
        <h1>Current Enrollments</h1>
      </div>
      <ul class="page-breadcrumb breadcrumb pull-right">
        <li><a href="dashboard.php"><?= e($company_name) ?></a></li>
        <li class="active">Current Enrollments</li>
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
      <div class="col-md-9 col-sm-9 user_right_area">
        <div class="portlet light">
          <div class="portlet-title tabbable-line">
            <div class="caption font-green-sharp"> <i class="icon-speech font-green-sharp"></i> <span class="caption-subject bold uppercase"> Current Enrollment</span> <span class="caption-helper">apply now...</span> </div>
            <?php include 'inc/top-menu.php'; ?>
          </div>
          <div class="portlet-body form">
            <?php
            $stmt_check = $database->db->prepare("SELECT COUNT(*) as num FROM categories WHERE status='1' AND title NOT IN (SELECT title FROM enrollments WHERE uid=?)");
            $stmt_check->execute([$uid]);
            $result_check = $stmt_check->fetch(PDO::FETCH_ASSOC);
            $stmt_check->closeCursor();
            if ($result_check['num'] > 0) :
            ?>
            <div class="checkout-wrap">
              <ul class="checkout-bar">
                <li class="active">Enrollment</li>
                <li>Payment &amp; Complete</li>
              </ul>
            </div>
            <?php endif; ?>
            
            <!-- BEGIN FORM-->
            <form action="" id="enrollment" name="enrollment" class="form-horizontal" enctype="multipart/form-data" method="post">
    <input type="hidden" name="csrf_token" value="<?php echo e($_SESSION['csrf_token']); ?>">
              <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
              <div class="row" style="margin:10px 0;">
                <?php
                if ($edit_mode) {
                    $stmt = $database->db->prepare("SELECT * FROM categories WHERE status='1'");
                    $stmt->execute();
                } else {
                    $stmt = $database->db->prepare("SELECT * FROM categories WHERE status='1' AND title NOT IN (SELECT title FROM enrollments WHERE uid=?)");
                    $stmt->execute([$uid]);
                }
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $stmt->closeCursor();

                if (!$edit_mode && count($result) === 0) : ?>
                <div class="col-md-12">
                    <h3>You have enrolled for every available category.</h3>
                    <p>Please check the payment status of your submitted applications and take any necessary action.</p>
                </div>
                <?php else :
                    foreach ($result as $category) :
                        $checked = '';
                        if ($edit_mode) {
                            if ($enrollment_data['title'] === $category['title']) {
                                $checked = 'checked';
                            }
                        } else {
                            // In new mode, check the first available category by default
                            if (!isset($is_first)) {
                                $checked = 'checked';
                                $is_first = true;
                            }
                        }
                ?>
                <div class="col-md-3">
                  <input class="to-labelauty synch-icon validate[required] radio ecategory" type="radio" name="category" data-labelauty="<?= e($category['title']) ?>" value="<?= e($category['title']) ?>" <?= $checked ?> />
                </div>
                <?php endforeach; endif; ?>
              </div>
              <h4>Explain Your Enrollment <span class="required">*</span></h4>
              
              <div class="form-group last" style="margin-left:0; margin-right:0px;">
                <div class="col-md-12">
                  <textarea class="ckeditor form-control validate[required]" name="editor2" rows="6" data-error-container="#editor2_error"><?= $edit_mode ? e($enrollment_data['explanation']) : '' ?></textarea>
                  <div id="editor2_error"> </div>
                </div>
              </div>
              
              <div class="form-group fileupload-buttonbar" style="margin-left:0; margin-right:0px;">
                <div class="col-lg-12 margin-bottom-15">
                  <label class="control-label">Manage Files <span class="required">*</span> [upload your photos, videos, music etc.]</label>
                </div>
                <div class="col-lg-12">
                  <?php
                  if ($edit_mode && $enrollment_data['no_of_files'] > 0) :
                      echo '<div class="form-group" style="margin-left:0; margin-right:0px;"><div class="col-lg-12"><label class="control-label" style="font-weight: bold;">Existing Files (select to delete)</label></div>';
                      
                      $upload_path = __DIR__ . "/../uploads/" . $uid . "/" . $enrollment_data['title'] . "/";
                      if (is_dir($upload_path)) {
                          $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($upload_path, RecursiveDirectoryIterator::SKIP_DOTS));
                          foreach ($iterator as $file) {
                              if ($file->isFile()) {
                                  $relative_path = str_replace('\\', '/', str_replace(realpath($upload_path), '', realpath($file->getPathname())));
                                  $relative_path = ltrim($relative_path, '/');
                                  $file_url = str_replace('../', '', $upload_path) . $relative_path;
                                  echo '<div class="col-lg-12" style="margin-bottom: 5px;">';
                                  echo '<input type="checkbox" name="files_to_delete[]" value="' . e($relative_path) . '"> ';
                                  echo '<a href="' . e($file_url) . '" target="_blank">' . e($file->getFilename()) . '</a>';
                                  echo '</div>';
                              }
                          }
                      }
                      echo '</div>';
                  endif;
                  ?>

                  <!-- sheepIt Form for adding new files -->
                  <div id="sheepItForm">
                    <div id="sheepItForm_template" style="margin-bottom:15px;">
                      <label for="sheepItForm_#index#_file">New File <span id="sheepItForm_label"></span></label>
                      <a class="sheepit-remove-btn"> <img class="delete" src="images/cross.png" width="16" height="16" border="0"> </a>
                      <input id="sheepItForm_#index#_file" name="docs[]" type="file" >
                    </div>
                    <div id="sheepItForm_noforms_template"></div>
                    <div id="sheepItForm_controls">
                      <button type="button" id="add_new_file_btn" class="btn blue-hoki"><span>Add New File</span></button>
                    </div>
                  </div>
                  <!-- /sheepIt Form -->
                 </div>
              </div>
              
               <div class="fee-display">
                    ALLOTTED FEE : Rs. <span id="damount"><?= number_format($initial_fee, 2) ?></span> /-
                    <input type="hidden" id="hfee" name="hfee" value="<?= $initial_fee ?>">
               </div>
                    
              <div class="form-actions">
                <div class="row no-margin">
                  <div class="ol-md-9">
                    <div class="pull-left">
                      <button type="submit" name="enrol_submit" class="btn green"><?php echo $edit_mode ? 'Update' : 'Submit'; ?></button>
                      <button type="button" id="btnc" class="btn default">Cancel</button>
                    </div>
                  </div>
                </div>
                
              </div>
            </form>
            <!-- END FORM--> 
            
          </div>
        </div>
      </div>
      <!-- END CONTENT --> 
    </div>
    <!-- END SIDEBAR & CONTENT --> 
  </div>
</div>

<!-- BEGIN FOOTER --> 
<!-- BEGIN FOOTER -->
<?php include('inc/footer.php'); ?>
<!-- END FOOTER --> <!-- END FOOTER --> 

<!-- Load javascripts at bottom, this will reduce page load time --> 
<!-- BEGIN CORE PLUGINS(REQUIRED FOR ALL PAGES) --> 
<!--[if lt IE 9]>
    <script src="assets/global/plugins/respond.min.js" nonce="<?= $nonce ?>"></script>  
    <![endif]--> 
<script src="assets/global/plugins/jquery.min.js" type="text/javascript" nonce="<?= $nonce ?>"></script> 
<script src="assets/global/plugins/jquery-migrate.min.js" type="text/javascript" nonce="<?= $nonce ?>"></script> 
<!-- IMPORTANT! Load jquery-ui-1.10.3.custom.min.js before bootstrap.min.js to fix bootstrap tooltip conflict with jquery ui tooltip --> 
<script src="assets/global/plugins/jquery-ui/jquery-ui-1.10.3.custom.min.js" type="text/javascript" nonce="<?= $nonce ?>"></script> 
<script src="assets/global/plugins/bootstrap/js/bootstrap.min.js" type="text/javascript" nonce="<?= $nonce ?>"></script> 
<script src="assets/global/plugins/bootstrap-hover-dropdown/bootstrap-hover-dropdown.min.js" type="text/javascript" nonce="<?= $nonce ?>"></script> 
<script src="assets/global/plugins/jquery-slimscroll/jquery.slimscroll.min.js" type="text/javascript" nonce="<?= $nonce ?>"></script> 
<script src="assets/global/plugins/jquery.blockui.min.js" type="text/javascript" nonce="<?= $nonce ?>"></script> 
<script src="assets/global/plugins/jquery.cokie.min.js" type="text/javascript" nonce="<?= $nonce ?>"></script> 
<script src="assets/global/plugins/uniform/jquery.uniform.min.js" type="text/javascript" nonce="<?= $nonce ?>"></script> 
<!-- END CORE PLUGINS --> 

<!-- validation --> 
<script src="assets/frontend/layout/scripts/jquery.validationEngine-en.js" type="text/javascript" nonce="<?= $nonce ?>"></script> 
<script src="assets/frontend/layout/scripts/jquery.validationEngine.js" type="text/javascript" nonce="<?= $nonce ?>"></script>
<script nonce="<?= $nonce ?>">
        jQuery(document).ready(function() {
            // binds form submission and fields to the validation engine
            jQuery("#enrollment").validationEngine();

            <?php
            // PHP code to fetch fees
            $stmt_fees = $database->db->prepare("SELECT title, fee FROM categories WHERE status='1'");
            $stmt_fees->execute();
            $fees = $stmt_fees->fetchAll(PDO::FETCH_KEY_PAIR);
            $stmt_fees->closeCursor();
            ?>
            // JavaScript declaration using the PHP variable
            const categoryFees = <?php echo json_encode($fees); ?>;

            $('input[name="category"]').change(function() {
                const selectedCategory = $(this).val();
                const newFee = categoryFees[selectedCategory] || 0;
                $('#damount').text(parseFloat(newFee).toFixed(2));
                $('#hfee').val(newFee);
            });
        });
    </script> 

<!--<script type="text/javascript" src="jquery-1.4.min.js"></script> --> 
<script type="text/javascript" src="jquery.sheepItPlugin.js" nonce="<?= $nonce ?>"></script> 
<script type="text/javascript" nonce="<?= $nonce ?>">
	$(document).ready(function() {
     
    var sheepItForm = $('#sheepItForm').sheepIt({
        separator: '',
        allowRemoveLast: true,
        allowRemoveCurrent: true,
        allowRemoveAll: true,
        allowAdd: true,
        allowAddN: true,
        maxFormsCount: 10,
        minFormsCount: 0,
        iniFormsCount: 2
    });

    // Delegated event handler for sheepIt remove buttons
    $('#sheepItForm').on('click', '.sheepit-remove-btn', function(e) {
        e.preventDefault(); // Prevent default link behavior
        // Trigger the sheepIt remove functionality
        // This assumes sheepIt has a method to remove a form by its element or index
        // You might need to inspect sheepItPlugin.js to find the correct way to trigger removal
        // For now, we'll simulate a click on the original remove button if it existed, or find a way to call sheepItForm.removeForm()
        var $this = $(this);
        var formIndex = $this.closest('.sheepItForm_item').index(); // Assuming each form item has a class 'sheepItForm_item'
        if (formIndex !== -1) {
            sheepItForm.removeForm(formIndex);
        }
    });
 
});
			
</script>
<style>

a {
    text-decoration:none;
    color:#00F;
    cursor:pointer;
}

#sheepItForm_controls div, #sheepItForm_controls div input {
    float:left;    
    margin-right: 10px;
}

</style>

<!-- BEGIN PAGE LEVEL PLUGINS --> 
<!--<script type="text/javascript" src="assets/global/plugins/jquery-validation/js/jquery.validate.min.js"></script> 
<script type="text/javascript" src="assets/global/plugins/jquery-validation/js/additional-methods.min.js"></script>  --> 

<script type="text/javascript" src="assets/global/plugins/bootstrap-wysihtml5/wysihtml5-0.3.0.js" nonce="<?= $nonce ?>"></script> 
<script type="text/javascript" src="assets/global/plugins/bootstrap-wysihtml5/bootstrap-wysihtml5.js" nonce="<?= $nonce ?>"></script> 
<script type="text/javascript" src="assets/global/plugins/ckeditor/ckeditor.js" nonce="<?= $nonce ?>"></script> 

<!-- END PAGE LEVEL PLUGINS --> 

<!-- BEGIN PAGE LEVEL PLUGINS --> 

<!-- END PAGE LEVEL PLUGINS--> 

<!-- BEGIN PAGE LEVEL STYLES --> 
<script src="assets/global/scripts/metronic.js" type="text/javascript" nonce="<?= $nonce ?>"></script> 
<script src="assets/admin/layout/scripts/layout.js" type="text/javascript" nonce="<?= $nonce ?>"></script> 
<script src="assets/admin/layout/scripts/quick-sidebar.js" type="text/javascript" nonce="<?= $nonce ?>"></script> 
<script src="assets/admin/layout/scripts/demo.js" type="text/javascript" nonce="<?= $nonce ?>"></script> 
<!--<script src="assets/admin/pages/scripts/form-validation.js"></script>  --> 
<!-- END PAGE LEVEL STYLES --> 

<!--<script type="text/javascript" src="source/jquery-latest.js"></script>--> 
<script type="text/javascript" src="source/jquery-labelauty.js" nonce="<?= $nonce ?>"></script> 
<script nonce="<?= $nonce ?>">
		$(document).ready(function(){
			$(".to-labelauty").labelauty({ minimum_width: "155px" });
			$(".to-labelauty-icon").labelauty({ label: false });
		});
	</script> 
<script nonce="<?= $nonce ?>">
jQuery(document).ready(function() {   
   // initiate layout and plugins
   //Metronic.init(); // init metronic core components
//Layout.init(); // init current layout
//QuickSidebar.init(); // init quick sidebar
//Demo.init(); // init demo features
   //FormValidation.init(); //
   //FormFileUpload.init();
});
</script>
<script nonce="<?= $nonce ?>">
$(document).ready(function(){
$(".ecategory").click(function(){
var myRadio = $('input[name=category]');
var checkedValue = myRadio.filter(':checked').val();
$.ajax({
           type: "POST",
            url: "getdata.php",
            data: "checkedValue="  + checkedValue,
            success:function(data){
            //return data;
            $("#damount").html(data); 
            $("#hfee").val(data);
            } 
            });
});

$("#btnc").click(function(){
window.location.href = "<?= $path ?>dashboard.php";
});
});<script type="text/javascript" nonce="<?= $nonce ?>">
    CKEDITOR.replace('editor2', {
        toolbar: 'Full',
        height: 300
    });
</script><!-- END BODY -->
</html>