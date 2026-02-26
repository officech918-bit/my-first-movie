<?php
/**
 * How It Works Page
 *
 * This is a static informational page explaining the process of MyFirstMovie.
 * It outlines the six steps from call for entries to the premiere.
 *
 * @package     MyFirstMovie
 * @subpackage  Pages
 * @since       1.0.0
 */
declare(strict_types=1);

// Bootstrap the application for consistent header/footer and assets
require_once 'inc/requires.php';
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}


$database = new MySQLDB();
$user = new visitor(); // First, create a base visitor
if ($user->check_session()) { // Then, check the session
    $user = new web_user(); // If logged in, elevate to web_user
}
$company_name = $user->get_company_name();
$path = rtrim($user->get_sitename() . '/' . $user->get_sub_location(), '/') . '/';

?>
<!DOCTYPE html>
<html dir="ltr" lang="en-US">
<head>
<title>How It Works | <?php echo htmlspecialchars($company_name, ENT_QUOTES, 'UTF-8'); ?></title>
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
		<script src="http://css3-mediaqueries-js.googlecode.com/svn/trunk/css3-mediaqueries.js"></script>
	<![endif]-->

<!-- External JavaScripts
	============================================= -->
<script type="text/javascript" src="js/jquery.js"></script>
<script type="text/javascript" src="js/plugins.js"></script>

<!-- Document Title
	============================================= -->

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
      <h1>How it works</h1>
      <span>Everything you need to know about our Company</span>
      <ol class="breadcrumb">
        <li><a href="index.php">Home</a></li>
        <li class="active">How it works</li>
      </ol>
    </div>
  </section>
  <!-- #page-title end --> 
  
  <!-- Content
		============================================= -->
  <section id="content">
    <div class="content-wrap">
      <div class="container clearfix">
        <div class="col_full nomargin">
          <p class="lead">These days everyone has a story to tell. Either from their own experience or their friend’s or relative’s experience. Everyone has that one plot or a story which they can proudly say “is pe to picture ban sakti hai”. MyFirstMovie.in will do just that, we will produce movies for people who have good stories to tell and don’t know what to do with their stories.</p>
          <p class="lead">Each season will be for three months each. There will be four such seasons per year. The first season will call for entries from writers/storytellers.</p>
          <div class="divider"><i class="icon-circle"></i></div>
        </div>
      </div>
      <div style="padding-bottom:0px; padding-top:30px;" class="section nomargin ">
        <div class="container clearfix">
          <div class="row clearfix">
            <div class="col-lg-12">
              <div class="heading-block center">
                <h3> The Six Steps to Fame</h3>
              </div>
              <div class="tabs side-tabs tabs-bordered clearfix" id="tab-5">
                <ul class="tab-nav clearfix">
                  <li><a href="#tabs-01">1. Call for Entries</a></li>
                  <li><a href="#tabs-02">2. Selection</a></li>
                  <li><a href="#tabs-03">3. Pre-production</a></li>
                  <li><a href="#tabs-04">4. Movie Production</a></li>
                  <li><a href="#tabs-05">5. Post Production</a></li>
                  <li><a href="#tabs-06">6. Premiere</a></li>
                </ul>
                <div class="tab-container">

								<div class="tab-content clearfix" id="tabs-01">
                                <div class="heading-block" style="margin-top: 10px;">
								<h4>Call for Entries</h4>
							</div>
									<?= lazy_image('images/team/3.jpg', 'Call for Entries', 'pull-right', ['style' => 'margin-left:25px;', 'width' => '220']) ?>Every few months MyFirstMovie will call for entries from some of the best minds and talent out there in the world to connect. People passionate about the movie making process and wanting to be a part of it.
								</div>
								<div class="tab-content clearfix" id="tabs-02">
                                <div class="heading-block" style="margin-top: 10px;">
								<h4>Selection</h4>
							</div>
									<?= lazy_image('images/team/3.jpg', 'Selection', 'pull-right', ['style' => 'margin-left:25px;', 'width' => '220']) ?>Once we have the entries with us we will allow our panelist to select the best ones for the upcoming project. Since MyFirstMovie will be producing the movie we would like to best minds to be working on it so that we make the best movie for the world to see.
                            
								</div>
								<div class="tab-content clearfix" id="tabs-03">
                                <div class="heading-block" style="margin-top: 10px;">
								<h4>Pre-Production</h4>
							</div>
									<?= lazy_image('images/team/3.jpg', 'Pre-Production', 'pull-right', ['style' => 'margin-left:25px;', 'width' => '220']) ?>After selecting the best story to work on and the crew like DOP, Choreographer, Actors, Make-up, etc., from the entries we get, we will start on with the pre production process of movie making. The story will be developed into a full screenplay and the casting, crew and location will be finalised. 
                                    
								</div>
								<div class="tab-content clearfix" id="tabs-04">
                                <div class="heading-block" style="margin-top: 10px;">
								<h4>Movie Shooting</h4>
							</div>
									<?= lazy_image('images/team/3.jpg', 'Movie Shooting', 'pull-right', ['style' => 'margin-left:25px;', 'width' => '220']) ?>After finalising the Screenplay, the cast and crew, locations, equipment’s, costumes, etc., it's now time for what everyone's been waiting for "Shooting". We start our shooting schedule and go on the floor for that dream project. Movie shooting is not an easy game and requires a hardworking team. The dedication and commitment of all the cast and crew members will make the movie shooting a success.
                                   
								</div>
                                <div class="tab-content clearfix" id="tabs-05">
                                <div class="heading-block" style="margin-top: 10px;">
								<h4>Post-Production</h4>
							</div>
									<?= lazy_image('images/team/3.jpg', 'Post-Production', 'pull-right', ['style' => 'margin-left:25px;', 'width' => '220']) ?>We have completed the shooting with utmost hard work and dedication. Phew, what a relief... Not so soon. Whoever said movie making is a piece of cake hasn't probably tasted it. Because now comes another round of hard work and it's called Post Production. We start with the editing of the movie that we have shot. The editor will piece together all the rushes from the shooting and make it into a movie based on our script. The Direction department will work in coordination with the post production studio to bring alive the movie. Once the editing is lines up and locked, it's time to do the sound design of the movie. In sound design, various sounds are added to the visual like footsteps, ambience sound, water sound, hitting sound, door open, engine start, etc. The Dubbing is the process where the artist lends his/her voice to the character in the movie. Once all kinds of sounds have been added we proceed for the Mixing part wherein we mix all sound onto one single track. Then comes the Color Correction process and lastly the master out.
                                    
								</div>
                                <div class="tab-content clearfix" id="tabs-06">
                                <div class="heading-block" style="margin-top: 10px;">
								<h4>Premiere</h4>
							</div>
									<?= lazy_image('images/team/3.jpg', 'Premiere', 'pull-right', ['style' => 'margin-left:25px;', 'width' => '220']) ?>What a relief! After such a tedious schedule of shooting and post production out final product is ready. So what do we do with it... We premiere it for the entire cast and crew and also call the press for some publicity. 
								</div>

							</div>
              </div>
              <p class="lead">We will be ready for next season and by the time we complete the post-production of the first movie we will announce the Call for Entries for the next season.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
  <!-- #content end -->
  <?php include('inc/register-cta.php'); ?>
  
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