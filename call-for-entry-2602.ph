<?php 
session_start();
date_default_timezone_set('Asia/Kolkata');
$date = date("Y-m-d H:i:s", time());
$time = date("d-m-Y H:i", time());

//get class files
include('inc/requires.php');

//create objects
$database = new MySQLDB();
$user = new visitor();
$is_error = false;
	
if($user->check_session())
{	
	$user = new web_user();
} 



$sitename = $user->get_sitename();
$sub_location = $user->get_sub_location();
$admin_location = $user->get_admin_location();

$from_email = $user->get_from_email();
$to_email = $user->get_to_email();
$company_name = $user->get_company_name();

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

?>
<!DOCTYPE html>
<html dir="ltr" lang="en-US">
<head>
<title>Call for Entry |<?php echo $company_name ?></title>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<meta name="author" content="SemiColonWeb" />

<!-- Stylesheets
	============================================= -->
<link href="https://fonts.googleapis.com/css?family=Lato:300,400,400italic,600,700|Raleway:300,400,500,600,700|Crete+Round:400italic" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="css/bootstrap.css" type="text/css" />
<link rel="stylesheet" href="style.css" type="text/css" />
<link rel="stylesheet" href="css/dark.css" type="text/css" />
<link rel="stylesheet" href="css/font-icons.css" type="text/css" />
<link rel="stylesheet" href="css/animate.css" type="text/css" />
<link rel="stylesheet" href="css/magnific-popup.css" type="text/css" />
<link rel="stylesheet" href="css/responsive.css" type="text/css" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<!--[if lt IE 9]>
		<script src="https://css3-mediaqueries-js.googlecode.com/svn/trunk/css3-mediaqueries.js"></script>
	<![endif]-->

<!-- validation -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jQuery-Validation-Engine/2.6.4/jquery-1.8.2.min.js"></script>
<script src="members/assets/frontend/layout/scripts/jquery.validationEngine-en.js" type="text/javascript"></script>
<script src="members/assets/frontend/layout/scripts/jquery.validationEngine.js" type="text/javascript"></script>
<script>
var jq = $.noConflict();
		jq(document).ready(function(){
			// binds form submission and fields to the validation engine
			jq("#register").validationEngine();
		});
	</script>

<!-- External JavaScripts
	============================================= -->
<script type="text/javascript" src="js/jquery.js"></script>
<script type="text/javascript" src="js/plugins.js"></script>

<!-- Document Title
	============================================= -->

<!-- validation -->
<link href="members/assets/frontend/layout/css/validationEngine.jquery.css" rel="stylesheet">
<script src='https://www.google.com/recaptcha/api.js'></script>
<style>
.help-block {
	color: #F00;
}
</style>
<?php include('inc/before_head_close.php'); ?>
</head>

<body class="stretched">
<?php include('inc/after_body_start.php'); ?>
<!-- Document Wrapper
	============================================= -->
<div id="wrapper" class="clearfix"> 
  
  <!-- Header
		============================================= -->
  <?php include('inc/header.php'); ?>
  <!-- #header end --> 
  
  <!-- Page Title
		============================================= -->
  <section id="page-title" class="page-title-mini">
    <div class="container clearfix">
      <h1>Call for Entry</h1>
      <span>Everything you need to know about our Company</span>
      <ol class="breadcrumb">
        <li><a href="index.php">Home</a></li>
        <li class="active">Call for Entry</li>
      </ol>
    </div>
  </section>
  <!-- #page-title end --> 
  
  <!-- Content
		============================================= -->
  <section id="content">
    <div class="content-wrap">
      <div class="container clearfix">
        <div class="col_full">
          <?php 
							$result = mysql_query("SELECT * FROM categories WHERE status='1'");
							$num = mysql_num_rows($result);
							if($num > 0) {
								while($category = mysql_fetch_assoc($result)) {
									?>
          <div class="row">
            <div class="col-md-3"><img src="<?php echo $category['cat_img'] ?>" class="img-responsive" /></div>
            <div class="col-md-9">
              <div style="margin-top: 10px;" class="heading-block">
                <h4><?php echo $category['title'] ?></h4>
              </div>
              <?php echo $category['display_note'] ?> <a class="button button-border button-rounded button-small noleftmargin pull-right" href="members/register.php">Apply now!</a> </div>
          </div>
          <div class="divider"><i class="icon-circle"></i></div>
          <?php 
								}
							}
						?>
          
          <!--<div class="row">
                        	<div class="col-md-3"><img src="images/team/3.jpg" class="img-responsive" /></div>
                            <div class="col-md-9">
                            	<div style="margin-top: 10px;" class="heading-block">
								<h4>Camera Man</h4>
                                
							</div>
                                <p>Content cominsoon here content cominsoon here content cominsoon here content cominsoon here content cominsoon here content cominsoon here content cominsoon here content cominsoon here content cominsoon here content cominsoon here content cominsoon here content cominsoon here content cominsoon here content cominsoon here content cominsoon here.</p>
                                <a class="button button-border button-rounded button-small noleftmargin pull-right" href="#">Apply now!</a>
                            </div>
                        </div> --> 
        </div>
      </div>
      <div style="padding-bottom:30px; padding-top:30px;" class="section nomargin ">
        <div class="container clearfix">
          <div class="row clearfix">
            <div class="col-lg-12">
              <div class="heading-block fancy-title nobottomborder title-bottom-border">
                <h4>Enroll here its <span>free to register</span>.</h4>
              </div>
              <div class="col-md-8">
                <div id="contact-form-result" data-notify-type="success" data-notify-msg="<i class=icon-ok-sign></i> Message Sent Successfully!"></div>
                <form class="form-horizontal col-md-offset-1 padding-left-0 nobottommargin" role="form" method="post" action="members/register.php" name="register" id="register">
                  <fieldset>
                    <div class="form-group <?php if(($is_error) && $error['first_name'] != '') echo 'has-error' ?>">
                      <label for="firstname" class="col-lg-4 control-label">First Name <span class="require">*</span></label>
                      <div class="col-lg-8">
                        <input type="text" class="sm-form-control form-control validate[required,,maxSize[30]]" id="first_name" name="first_name" value="<?php echo $first_name ?>">
                        <?php if(($is_error) && $error['first_name'] != '') echo '<span class="help-block">'.$error['first_name'].'</span>' ?>
                      </div>
                    </div>
                    <div class="form-group <?php if(($is_error) && $error['last_name'] != '') echo 'has-error' ?>">
                      <label for="lastname" class="col-lg-4 control-label">Last Name <span class="require">*</span></label>
                      <div class="col-lg-8">
                        <input type="text" class="sm-form-control form-control validate[required,maxSize[30]]" id="last_name" name="last_name" value="<?php echo $last_name ?>">
                        <?php if(($is_error) && $error['last_name'] != '') echo '<span class="help-block">'.$error['last_name'].'</span>' ?>
                      </div>
                    </div>
                    <div class="form-group <?php if(($is_error) && $error['email'] != '') echo 'has-error' ?>">
                      <label for="email" class="col-lg-4 control-label">Email <span class="require">*</span></label>
                      <div class="col-lg-8">
                        <input type="text" class="sm-form-control form-control validate[required,custom[email]]" id="email" name="email" value="<?php echo $email ?>">
                        <?php if(($is_error) && $error['email'] != '') echo '<span class="help-block">'.$error['email'].'</span>' ?>
                      </div>
                    </div>
                    <div class="form-group <?php if(($is_error) && $error['contact'] != '') echo 'has-error' ?>">
                    <label for="contact" class="col-lg-4 control-label">Mobile <span class="require">*</span></label>
                    <div class="col-lg-8">
                      <input type="text" class="form-control validate[required,custom[integer], minSize[10], maxSize[10]]" id="contact" name="contact" value="<?php echo $contact ?>">
                      <?php if(($is_error) && $error['contact'] != '') echo '<span class="help-block">'.$error['contact'].'</span>' ?>
                    </div>
                  </div>
                    <div class="form-group <?php if(($is_error) && $error['password1'] != '') echo 'has-error' ?>">
                      <label for="password" class="col-lg-4 control-label">Password <span class="require">*</span></label>
                      <div class="col-lg-8">
                        <input type="password" class="sm-form-control form-control validate[required,minSize[6],maxSize[30]]" id="password1" name="password1">
                        <?php if(($is_error) && $error['password1'] != '') echo '<span class="help-block">'.$error['password1'].'</span>' ?>
                      </div>
                    </div>
                    <div class="form-group <?php if(($is_error) && $error['password2'] != '') echo 'has-error' ?>">
                      <label for="confirm-password" class="col-lg-4 control-label">Confirm password <span class="require">*</span></label>
                      <div class="col-lg-8">
                        <input type="password" class="sm-form-control form-control validate[required,equals[password1]]" id="password2" name="password2">
                        <?php if(($is_error) && $error['password2'] != '') echo '<span class="help-block">'.$error['password2'].'</span>' ?>
                      </div>
                    </div>
                    <div class="form-group checkbox <?php if(($is_error) && $error['i_agree'] != '') echo 'has-error' ?>">
                      <label class="col-lg-4 control-label"></label>
                      <div class="col-lg-8">
                        <div class="g-recaptcha" data-sitekey="6LfCFxoTAAAAADUwhgMu6MsDGTHiwkHvNjh92Xka"></div>
                        <?php if(($is_error) && $error['captcha'] != '') echo '<span class="help-block">'.$error['captcha'].'</span>' ?>
                      </div>
                    </div>
                    <div class="form-group checkbox <?php if(($is_error) && $error['i_agree'] != '') echo 'has-error' ?>" style="margin-top:10px;">
                      <label class="col-lg-4 control-label"></label>
                      <div class="col-lg-8">
                        <label>
                          <input type="checkbox" class="validate[required]" name="i_agree" id="i_agree">
                          I Agree to <a href="<?php echo $path ?>terms-and-conditions" target="_blank">Terms &amp; Conditions</a> of <?php echo $company_name  ?></label>
                        <?php if(($is_error) && $error['i_agree'] != '') echo '<span class="help-block">'.$error['i_agree'].'</span>' ?>
                      </div>
                    </div>
                  </fieldset>
                  <div class="row">
                    <div class="col-lg-8 col-md-offset-4 padding-left-0 padding-top-20">
                      
                      <button type="submit" name="register" class="button button-3d nomargin">Create an account</button>
                    </div>
                  </div>
                </form>
                
              </div>
              <div class="col-md-4"> <img class="img-responsive" src="images/team/3.jpg" width="400" style="height:230px;"> </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
  <!-- #content end -->
  <section>
    <div class="section dark parallax notopmargin nobottommargin noborder" style="padding: 30px 0; background:#0089D1;">
      <div class="container clearfix">
        <div class="slider-caption slider-caption-center" style="position: relative;">
          <div class="fadeInUp animated" data-animate="fadeInUp">
            <h2 style="font-size: 30px; margin-bottom: 15px;">Its free to Register and explore the world of MFM</h2>
            <p style="font-size: 20px; font-weight: 400; line-height: 1.4 !important; margin-bottom: 10px;">Content cominsoon here content cominsoon here content cominsoon here content cominsoon here demo content cominsoon here content cominsoon here demo content cominsoon here</p>
            <a href="#" class="button button-border button-rounded button-white button-light button-large noleftmargin nobottommargin" style="margin-top: 20px;">Register Now!</a> </div>
        </div>
      </div>
    </div>
  </section>
  
  <!-- Footer
		============================================= -->
  <?php include('inc/footer.php'); ?>
  <!-- #footer end --> 
  
</div>
<!-- #wrapper end --> 

<!-- Go To Top
	============================================= -->
<div id="gotoTop" class="icon-angle-up"></div>

<!-- Footer Scripts
	============================================= --> 
<script type="text/javascript" src="js/functions.js"></script> 
<script type="text/javascript" src="js/jquery.pulsate.js"></script> 
<script>
    $(function () {
     $("#pulse").pulsate({color:"#09f"});
     $(".pulse1").pulsate({glow:false});
     $(".pulse2").pulsate({color:"#09f"});
     $(".pulse3").pulsate({reach:100});
     $(".pulse4").pulsate({speed:2500});
     $(".pulse5").pulsate({pause:1000});
     $(".pulse6").pulsate({onHover:true});
    });
  </script>
<?php include('inc/before_body_close.php'); ?>
</body>
</html>