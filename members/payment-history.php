<?php declare(strict_types=1);

/**
 * Displays the user's payment history.
 *
 * @package MFM
 * @subpackage Members
 */

require_once __DIR__ . '/inc/requires.php';

// Objects are now created in requires.php
// The user's session is checked in requires.php

if (!$user->check_session()) {
    header("Location: index.php");
    exit();
}

if (!$user->isActive()) {
    header("Location: activate.php");
    exit();
}

$company_name = $user->get_company_name();

// Fetch payment history using a prepared statement.
$stmt = $pdo->prepare("SELECT * FROM ccav_resp WHERE uid = :uid");
$stmt->execute([':uid' => $_SESSION['uid']]);
$payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
<title>Payment History | <?php echo htmlspecialchars($company_name); ?></title>
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
<!--<link rel="stylesheet" href="https://www.myfirstmovie.in/assets/frontend/layout/css/skyform/sky-forms.css" />
<link rel="stylesheet" href="https://www.myfirstmovie.in/assets/frontend/layout/css/skyform/sky-forms-orange.css" /> -->

<link rel="icon" href="<?= $path ?>favicon.png" type="image/png" />
<link rel="shortcut icon" href="<?= $path ?>favicon.png" type="image/png" />
<link rel="apple-touch-icon" href="<?= $path ?>favicon.png">
<link rel="apple-touch-icon" sizes="76x76" href="<?= $path ?>favicon.png">
<link rel="apple-touch-icon" sizes="120x120" href="<?= $path ?>favicon.png">
<link rel="apple-touch-icon" sizes="152x152" href="<?= $path ?>favicon.png">
<link rel="stylesheet" href="source/jquery-labelauty.css" type="text/css" media="screen" charset="utf-8" />
<link rel="stylesheet" href="source/lby-main.css" type="text/css" media="screen" charset="utf-8" />
<?php include('inc/pre-body.php'); ?>
<!-- Body BEGIN -->

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

.enrol-table table,th,td {
  border: 1px solid black;
  text-align: center;
  padding: 10px;
}

.enrol-table th,td {
width: 17%;
}

.enrol-table td:nth-child(4){
white-space: nowrap;
}


</style>



</head>
<!-- Head END -->

<body class="ecommerce">
<!-- BEGIN TOP BAR -->
<?php include('inc/header.php'); ?>
<!-- Header END --> <!-- Header END -->

<div class="main">
  <div class="page-head">
    <div class="container"> 
      <!-- BEGIN PAGE TITLE -->
      <div class="page-title">
        <h1>Payment History</h1>
      </div>
      <ul class="page-breadcrumb breadcrumb pull-right">
        <li><a href="dashboard.php"><?php echo htmlspecialchars($company_name); ?></a></li>
        <li class="active">Payment History</li>
      </ul>
      <!-- END PAGE TITLE --> 
    </div>
  </div>
  <div class="container"> 
    
    <!-- BEGIN SIDEBAR & CONTENT -->
    <div class="row margin-bottom-40"> 
      <!-- BEGIN SIDEBAR -->
      <div class="sidebar col-md-3 col-sm-3">
        <?php include('inc/left-menu.php'); ?>
      </div>
      <!-- END SIDEBAR --> 
      
      <!-- BEGIN CONTENT -->
      <div class="col-md-9 col-sm-9 user_right_area">
        <div class="portlet light">
          <div class="portlet-title tabbable-line">
            <div class="caption font-green-sharp"> <i class="icon-speech font-green-sharp"></i> <span class="caption-subject bold uppercase"> My Enrollment</span> <span class="caption-helper">apply now...</span> </div>
            <?php include('inc/top-menu.php'); ?>
          </div>
          <?php
          if (!empty($payments)) {
          ?>
          <table class="enrol-table">
            <tbody>
              <tr>
                <th>
                  Sr. No.
                </th>
                <th>
                  Order Identification Number
                </th>
                <th>
                  Category Applied
                </th>
                <th>
                  Fees Allotted
                </th>
                <th>
                  Payment Status
                </th>
                <th>
                  Transaction Feedback
                </th>
                <th>
                  Date & Time of Transaction
                </th>
                <th>
                  IP Address
                </th>
              </tr>
              <?php
              $sr_no = 1;
              foreach($payments as $enrol_result) {
              ?>
              <tr>
                <td>
                  <?php echo $sr_no++; ?>
                </td>
                <td>
                  <?php echo htmlspecialchars($enrol_result['order_id']); ?>
                </td>
                <td>
                  <?php echo htmlspecialchars($enrol_result['title']); ?>
                </td>
                <td>
                  <?php echo "Rs. " . htmlspecialchars((string)$enrol_result['amount']) . " /-"; ?>
                </td>
                <td>
                  <?php echo empty($enrol_result['status']) ? "Unknown Error. Payment Failed" : htmlspecialchars($enrol_result['status']); ?>
                </td>
                <td>
                  <?php echo htmlspecialchars($enrol_result['msg']); ?>
                </td>
                <td>
                  <?php echo '<span class="nowrap">' . date('dS F , Y', (int)$enrol_result['dt']) . '</span>' . "\n" . '<span class="nowrap">' . date('h:i:s A', (int)$enrol_result['dt']) . '</span>'; ?>
                </td>
                
                <td>
                  <?php echo htmlspecialchars($enrol_result['billing_ip']); ?>
                </td>
              </tr>
              <?php }
                    } else {
               ?>
               <h3 class="msgz">You have not enrolled in any category yet!</h3>
               <?php
               }
               // $stmt->close(); // Not needed for PDO
               ?>
            </tbody>
          </table>
          
         
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
    <script src="assets/global/plugins/respond.min.js"></script>  
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
<!-- END CORE PLUGINS --> 

<!-- validation --> 
<script src="assets/frontend/layout/scripts/jquery.validationEngine-en.js" type="text/javascript"></script> 
<script src="assets/frontend/layout/scripts/jquery.validationEngine.js" type="text/javascript"></script> 
<script>
		jQuery(document).ready(function(){
			// binds form submission and fields to the validation engine
			jQuery("#enrollment").validationEngine();
		});
	</script> 

<!--<script type="text/javascript" src="jquery-1.4.min.js"></script> --> 
<script type="text/javascript" src="jquery.sheepItPlugin.js"></script> 
<script type="text/javascript">
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

<script type="text/javascript" src="assets/global/plugins/bootstrap-wysihtml5/wysihtml5-0.3.0.js"></script> 
<script type="text/javascript" src="assets/global/plugins/bootstrap-wysihtml5/bootstrap-wysihtml5.js"></script> 
<script type="text/javascript" src="assets/global/plugins/ckeditor/ckeditor.js"></script> 

<!-- END PAGE LEVEL PLUGINS --> 

<!-- BEGIN PAGE LEVEL PLUGINS --> 

<!-- END PAGE LEVEL PLUGINS--> 

<!-- BEGIN PAGE LEVEL STYLES --> 
<script src="assets/global/scripts/metronic.js" type="text/javascript"></script> 
<script src="assets/admin/layout/scripts/layout.js" type="text/javascript"></script> 
<script src="assets/admin/layout/scripts/quick-sidebar.js" type="text/javascript"></script> 
<script src="assets/admin/layout/scripts/demo.js" type="text/javascript"></script> 
<!--<script src="assets/admin/pages/scripts/form-validation.js"></script>  --> 
<!-- END PAGE LEVEL STYLES --> 

<!--<script type="text/javascript" src="source/jquery-latest.js"></script>--> 
<script type="text/javascript" src="source/jquery-labelauty.js"></script> 
<script>
		$(document).ready(function(){
			$(".to-labelauty").labelauty({ minimum_width: "155px" });
			$(".to-labelauty-icon").labelauty({ label: false });
		});
	</script> 
<script>
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
<script>
$(document).ready(function(){

$("#btnc").click(function(){
window.location.href = "<?= $path ?>dashboard.php";
});

});
</script>


</body>
<!-- END BODY -->
</html>