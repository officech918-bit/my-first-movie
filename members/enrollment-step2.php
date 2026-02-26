<?php declare(strict_types=1);

/**
 * @author   closemarketing
 * @license  https://www.closemarketing.com/
 * @version  1.0.0
 * @since    1.0.0
 */

require_once __DIR__ . '/inc/requires.php';

// Get the correct base path from current request
$script = $_SERVER['SCRIPT_NAME'] ?? '';
$basePath = '';
if ($script) {
    $parts = explode('/', trim($script, '/'));
    if (!empty($parts)) {
        $basePath = '/' . $parts[0]; // This will give us /myfirstmovie3
    }
}
$correct_base_path = $basePath;

// Get members path dynamically for URL generation
$scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$requestUri = $_SERVER['REQUEST_URI'];

// Extract the actual path from the current request
$uriParts = explode('/', trim($requestUri, '/'));
$membersIndex = array_search('members', $uriParts);

if ($membersIndex !== false) {
    $members_path = $scheme . '://' . $host . '/' . implode('/', array_slice($uriParts, 0, $membersIndex + 1)) . '/';
} else {
    $members_path = $scheme . '://' . $host . '/members/'; // fallback
}

if (!$user->check_session() || !$user->isActive()) {
    header('Location: index.php');
    exit();
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header('Location: index.php');
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM enrollments where id = :id");
$stmt->execute([':id' => $id]);
$enrollment = $stmt->fetch(PDO::FETCH_ASSOC);
$stmt->closeCursor();

if (!$enrollment) {
    die("Enrollment not found.");
}

if ($enrollment['status'] === 'completed') {
    die("This enrollment is already paid.");
}

// Fetch the last CCAvenue response for this user and enrollment title
$stmt = $pdo->prepare("SELECT * FROM ccav_resp WHERE id = (SELECT MAX(id) FROM ccav_resp WHERE uid = :uid AND title = :title)");
$stmt->execute([':uid' => $_SESSION['uid'], ':title' => $enrollment['title']]);
$last_response = $stmt->fetch(PDO::FETCH_ASSOC);
$stmt->closeCursor();

if ($last_response) {
    $_SESSION['category2'] = $last_response['title'];
}

$billing_address = $last_response['billing_address'] ?? $user->get('address');
$billing_country = $last_response['billing_country'] ?? $user->get('country');
$billing_state = $last_response['billing_state'] ?? '';
$billing_city = $last_response['billing_city'] ?? '';

$countries_stmt = $pdo->query("SELECT * FROM countries");
$countries = $countries_stmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'Account Details | ' . e($user->get_company_name());

$error = [];
$is_error = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die('CSRF token validation failed.');
    }

    $billing_address = filter_input(INPUT_POST, 'address', FILTER_SANITIZE_STRING);
    $billing_country = filter_input(INPUT_POST, 'country', FILTER_SANITIZE_STRING);
    $billing_state = filter_input(INPUT_POST, 'state', FILTER_SANITIZE_STRING);
    $billing_city = filter_input(INPUT_POST, 'city', FILTER_SANITIZE_STRING);
    $billing_zip = filter_input(INPUT_POST, 'zip', FILTER_SANITIZE_STRING);
    $i_agree = filter_input(INPUT_POST, 'i_agree', FILTER_VALIDATE_BOOLEAN);

    if (empty($billing_address)) {
        $is_error = true;
        $error['address'] = 'Address is required.';
    }
    if (empty($billing_country)) {
        $is_error = true;
        $error['country'] = 'Country is required.';
    }
    if (empty($billing_state)) {
        $is_error = true;
        $error['state'] = 'State is required.';
    }
    if (empty($billing_city)) {
        $is_error = true;
        $error['city'] = 'City is required.';
    }
    if (empty($billing_zip)) {
        $is_error = true;
        $error['zip'] = 'ZIP/Postal code is required.';
    }
    if (!$i_agree) {
        $is_error = true;
        $error['i_agree'] = 'You must agree to the terms and conditions.';
    }

    if (!$is_error) {
        // Process payment via CCAvenue
        $_SESSION['billing_details'] = [
            'billing_address' => $billing_address,
            'billing_country' => $billing_country,
            'billing_state' => $billing_state,
            'billing_city' => $billing_city,
            'billing_zip' => $billing_zip,
            'enrollment_id' => $id,
            'enrollment_title' => $enrollment['title'],
            'order_id' => $enrollment['id'], // Use actual enrollment ID
            'amount' => number_format((float)$enrollment['fee'], 2, '.', ''), // Format amount
            'redirect_url' => $members_path . 'ccavenue/ccavResponseHandler.php', // Dynamic URL
            'cancel_url' => $members_path . 'ccavenue/ccavResponseHandler.php' // Dynamic URL
        ];
        error_log("Enrollment fee for ID " . $id . ": " . $enrollment['fee']);
        header('Location: ccavenue/index.php');
        exit();
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
<title>Account Details | <?php echo e($pageTitle); ?></title>
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
</style>

<?php include dirname(__DIR__) . '/inc/before_head_close.php'; ?>
</head>
<!-- Head END -->

<!-- Body BEGIN -->
<body class="ecommerce">
<!-- BEGIN TOP BAR -->
<?php include __DIR__ . '/inc/header.php'; ?>
<!-- Header END -->

<div class="main">
  <div class="page-head">
    <div class="container"> 
      <!-- BEGIN PAGE TITLE -->
      <div class="page-title">
        <h1>ACCOUNT DETAILS</h1>
      </div>
      <ul class="page-breadcrumb breadcrumb pull-right">
        <li><a href="dashboard.php"><?php echo e($user->get_company_name()); ?></a></li>
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
        <?php include __DIR__ . '/inc/left-menu.php'; ?>
      </div>
      <!-- END SIDEBAR --> 
      
      <!-- BEGIN CONTENT -->
      <div class="col-md-9 col-sm-7 user_right_area">
        <div class="portlet light bordered">
          <div class="portlet-title tabbable-line">
            <div class="caption font-green-sharp"> <i class="icon-speech font-green-sharp"></i> <span class="caption-subject bold uppercase"> Account Details</span> <span class="caption-helper">make it personalized...</span> </div>
            <?php // include __DIR__ . '/inc/top-menu.php'; ?>
          </div>
          <div class="portlet-body" style=" overflow:hidden; padding-bottom:20px;">
          <div class="checkout-wrap">
              <ul class="checkout-bar">
                <li class="previous visited">Enrollment</li>
                <li class="active">Payment &amp; Complete</li>
              </ul>
            </div>
            <?php if (isset($echo_message) && $echo_message) : ?>
                <div class="alert alert-success"><strong>Success!</strong> <?php echo e($echo_message); ?></div>
            <?php endif; ?>
            
            <form class="horizontal-form" name="user_data" id="user_data" action="" method="post" enctype="multipart/form-data">
              <input type="hidden" name="csrf_token" value="<?php echo e($_SESSION['csrf_token']); ?>">
              <div class="form-body">
              <div class="col-md-6 col-sm-6">
                
                <div class="form-group <?php if($is_error && isset($error['address']) && ($error['address'] != ''))echo 'has-error' ?>">
                  <label >Address <span class="require">*</span></label>
                  <input type="text" name="address" id="address" class="form-control validate[required]"  placeholder="For Billing Purpose" value="<?php echo e($billing_address); ?>" />
                  <?php if($is_error && isset($error['address']) && ($error['address'] != '')) : ?>
                  <span class="help-block">
                  <?php echo e($error['address']); ?>
                  </span>
                  <?php endif; ?> </div>
                  <div class="form-group <?php if($is_error && isset($error['country']) && ($error['country'] != ''))echo 'has-error' ?>">
                  <label for="country">Country <span class="require">*</span></label>
                  <select class="form-control validate[required]" name="country" id="country" style="color:#333;">
                    <option value="">Select a Country</option>
                    <?php foreach ($countries as $country_item) : ?>
                        <option value="<?php echo e($country_item['CountryName']); ?>" data-id="<?php echo e((string)$country_item['CountryID']); ?>" <?php if ($billing_country === $country_item['CountryName']) echo 'selected'; ?>>
                            <?php echo e($country_item['CountryName']); ?>
                        </option>
                    <?php endforeach; ?>
                  </select>
                  <?php if($is_error && isset($error['country']) && ($error['country'] != '')) : ?>
                  <span  class="help-block">
                  <?php echo e($error['country']); ?>
                  </span>
                  <?php endif; ?> </div>
                  
                
            
                <div class="form-group" style="padding-left:30px;">
                  <div class="checkbox">
                    <label>
                      <input name="i_agree" id="i_agree" value="1" type="checkbox" checked >
                      By Clicking Process I am Agree to the <a href="">Terms & Conditions</a> of <?php echo e($user->get_company_name()); ?>. </label>
                      <?php if ($is_error && isset($error['i_agree'])) : ?>
                          <span class="help-block"><?php echo e($error['i_agree']); ?></span>
                      <?php endif; ?>
                  </div>
                </div>
              </div>
              <div class="col-md-6 col-sm-6">
                 <div  class="form-group <?php if($is_error && isset($error['state']) && ($error['state'] != ''))echo 'has-error' ?>">
                  <label for="states">Region/State <span class="require">*</span></label>
                  <select class="form-control validate[required]" name="state" id="state" style="color:#333;" >
                    <option value="">Select State</option>
                    <?php
                    if (!empty($billing_country)) {
                        $stmt_country_id = $pdo->prepare("SELECT CountryID FROM countries WHERE CountryName = :country_name");
                        $stmt_country_id->execute([':country_name' => $billing_country]);
                        $country_id_row = $stmt_country_id->fetch(PDO::FETCH_ASSOC);
                        $stmt_country_id->closeCursor();

                        if ($country_id_row) {
                            $country_id = $country_id_row['CountryID'];
                            $stmt_states = $pdo->prepare("SELECT * FROM states WHERE CountryID = :country_id");
                            $stmt_states->execute([':country_id' => $country_id]);
                            $states = $stmt_states->fetchAll(PDO::FETCH_ASSOC);
                            $stmt_states->closeCursor();

                            foreach ($states as $state_item) {
                                $selected = ($billing_state === $state_item['StateName']) ? 'selected' : '';
                                echo '<option value="' . e($state_item['StateName']) . '" data-id="' . e((string)$state_item['StateID']) . '" ' . $selected . '>' . e($state_item['StateName']) . '</option>';
                            }
                        }
                    }
                    ?>
                  </select>

                  <?php if($is_error && isset($error['state']) && ($error['state'] != '')) : ?>
                  <span  class="help-block">
                  <?php echo e($error['state']); ?>
                  </span>
                  <?php endif; ?> </div>
                <div class="form-group <?php if($is_error && isset($error['city']) && ($error['city'] != ''))echo 'has-error' ?>">
                  <label for="city">City <span class="require">*</span></label>
                  <select class="form-control validate[required]" name="city" id="city" style="color:#333;" >
                    <option value="">Select City</option>
                    <?php
                    if (!empty($billing_state)) {
                        $stmt_state_id = $pdo->prepare("SELECT StateID FROM states WHERE StateName = :state_name");
                        $stmt_state_id->execute([':state_name' => $billing_state]);
                        $state_id_row = $stmt_state_id->fetch(PDO::FETCH_ASSOC);
                        $stmt_state_id->closeCursor();

                        if ($state_id_row) {
                            $state_id = $state_id_row['StateID'];
                            $stmt_cities = $pdo->prepare("SELECT * FROM cities WHERE StateID = :state_id");
                            $stmt_cities->execute([':state_id' => $state_id]);
                            $cities = $stmt_cities->fetchAll(PDO::FETCH_ASSOC);
                            $stmt_cities->closeCursor();

                            foreach ($cities as $city_item) {
                                $selected = ($billing_city === $city_item['CityName']) ? 'selected' : '';
                                echo '<option value="' . e($city_item['CityName']) . '" data-id="' . e((string)$city_item['CityID']) . '" ' . $selected . '>' . e($city_item['CityName']) . '</option>';
                            }
                        }
                    }
                    ?>
                  </select>

                  <?php if($is_error && isset($error['city']) && ($error['city'] != '')) : ?>
                  <span class="help-block">
                  <?php echo e($error['city']); ?>
                  </span>
                  <?php endif; ?> </div>
                <div class="form-group <?php if($is_error && isset($error['zip']) && ($error['zip'] != ''))echo 'has-error' ?>">
                  <label for="post-code">Zip Code/Postal Code <span class="require">*</span></label>
                  <input type="text" id="zip" name="zip" class="form-control validate[required]"  value="<?php echo e($billing_zip ?? ''); ?>" />
                  <?php if($is_error && isset($error['zip']) && ($error['zip'] != '')) : ?>
                  <span class="help-block">
                  <?php echo e($error['zip']); ?>
                  </span>
                  <?php endif; ?> </div>
              </div>

              <input type="hidden" name="uid" value="<?php echo e((string)intval($_SESSION['uid'])); ?>">
                                   
              <div class="form-actions">
                <button class="btn btn-primary " id="submit_user_data_new" name="submit_user_data_new" type="submit"  > Process to Payment </button>
              </div>
        </div>  
      </div>
      <!-- END CONTENT --> 
    </div>
    <!-- END SIDEBAR & CONTENT --> 
  </div>
</div>

<!-- BEGIN FOOTER -->
<?php include('inc/footer.php'); ?>
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
<script type="text/javascript ">
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
<script nonce="<?= $nonce ?>">
            jQuery(document).ready(function(){
                // binds form submission and fields to the validation engine
                jQuery("#user_data").validationEngine();
				
				
            });    
    </script> 
<script type="text/javascript" nonce="<?= $nonce ?>">
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
	$( document ).on( "change", "#country", function() {
		var country_id = $(this).children('option:selected').data('id');
		var country_name = $(this).val();
		var state_name = '<?php echo $billing_state ?? ''; ?>';
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
	
	$( document ).on( "change", "#state", function() {
		var state_id = $(this).children('option:selected').data('id');
		var city_dropdown = $('#city');
		city_dropdown.empty().append('<option value="">Loading Cities...</option>'); // Clear and add loading message

		if(state_id && ($('#country').val() == 'India')) {
			$("#city").removeAttr("disabled");
			$('#city').css('display','block');
			
			$('#city2').prop('disabled', 'disabled');
			$('#city2').css('display','none');
			
			$.ajax({
				'url' : 'update_cities.php',
				'type' : 'POST',
				'data' : {'state_id' : state_id},
				'success' : function(data){ 
					if(data){
						city_dropdown.html(data);
					}
				},
				'error' : function() {
					city_dropdown.empty().append('<option value="">Error loading cities</option>');
				}
			});
		}
		else {
			city_dropdown.html('<option value="City">Select City</option>');
			city_dropdown.prop('disabled', 'disabled');
			city_dropdown.css('display','none');
			
			$("#city2").removeAttr("disabled");
			$('#city2').css('display','block');
		}
	});
	
</script>
<script>
$(document).ready(function(){
$("#i_agree").change(function(){
if(document.getElementById("i_agree").checked==false){
alert("Unchecking this option would not allow your application to be submitted!");
$("#submit_user_data_new").click(function(event){
event.preventDefault();
});
}
if(document.getElementById("i_agree").checked==true){
$("#submit_user_data_new").click(function(event){
document.getElementById("user_data").submit();
});
}
});
});
</script>
</body>
<!-- END BODY -->
</html>