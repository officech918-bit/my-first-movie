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

$season_id = "";
if(isset($_GET['id'])) {
	$season_id = $_GET['id'];
} else {
	$result3 = mysql_query("SELECT id FROM seasons WHERE status='1' ORDER BY create_date DESC");
	if(mysql_num_rows($result3) > 0) {
		$temp = mysql_fetch_array($result3);
		$season_id = $temp['id'];
	}
	else $season_id = 1;
}

//prepare the menu
$id_array = array();
$result4 = mysql_query('SELECT id FROM seasons ORDER BY create_date DESC');
while($temp_ids = mysql_fetch_array($result4)){
	array_push($id_array, $temp_ids['id']);
}

$first = 0;
$last = key(array_slice( $id_array, -1, 1, TRUE ));
$current = array_search("$season_id", $id_array);
$previous = '';
$next = '';
if($first != $current) {
	$previous = $current-1;
}


	$next = $current+1;





$season  = array();
$res = mysql_query("SELECT * FROM seasons WHERE id='$season_id' ORDER BY create_date DESC");
if(mysql_num_rows($res) > 0) $season = mysql_fetch_array($res);

?>
<!DOCTYPE html>
<html dir="ltr" lang="en-US">
<head>
<title>My First Movie | Behinde the scence</title>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<meta name="author" content="SemiColonWeb" />

<!-- Stylesheets
	============================================= -->
<link href="http://fonts.googleapis.com/css?family=Lato:300,400,400italic,600,700|Raleway:300,400,500,600,700|Crete+Round:400italic" rel="stylesheet" type="text/css" />
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
      <h1>Behind the scenes</h1>
      <span>Everything you need to know about our Company</span>
      <ol class="breadcrumb">
        <li><a href="index.html">Home</a></li>
        <li class="active">Behind the scenes</li>
      </ol>
    </div>
  </section>
  <!-- #page-title end --> 
  
  <!-- Content
		============================================= -->
  <section id="content">
    <div class="content-wrap">
      <div class="container clearfix">
        <div class="col_full" style="margin-bottom:30px;">
          <div class="col-md-7">
            <div class="heading-block">
              <h3><?php echo $season['title'] ?></h3>
            </div>
          </div>
          <div class="col-md-5">
          	<?php if($last != $current) { ?>
          	<a class="button button-rounded button-reveal button-small button-border tright pull-right" href="behind-the-scenes.php?id=<?php echo $id_array["$last"]; ?>"><i class="icon-forward"></i><span>Last</span></a> 
          	<?php } ?>
            
            <?php if($last != $current) { ?>
            <a class="button button-rounded button-reveal button-small button-border tright pull-right" href="behind-the-scenes.php?id=<?php echo $id_array["$next"]; ?>"><i class="icon-forward"></i><span>Next</span></a>
            <?php } ?>
            
            <?php if($first != $current) { ?>
            <a class="button button-rounded button-reveal button-small button-border tright pull-right" href="behind-the-scenes.php?id=<?php echo $id_array["$previous"]; ?>"><i class="icon-backward"></i><span>Previous</span></a>
            <?php } ?>
            
            <?php if($first != $current) { ?>
            <a class="button button-rounded button-reveal button-small button-border tright pull-right" href="behind-the-scenes.php?id=<?php echo $id_array["$first"]; ?>"><i class="icon-backward"></i><span>Current</span></a>
            <?php } ?>
          </div>
          <div class="clearfix"></div>
          <div class="accordion accordion-bg clearfix">
          <?php 
		  	$result = mysql_query("SELECT * FROM behind_the_scenes WHERE season = '$season_id' AND status='1' ORDER BY day DESC");
			if(mysql_num_rows($result) > 0) {
				while($bts = mysql_fetch_assoc($result)) {	
				$bts_id = $bts['id']
		  ?>
            <div class="acctitle"><i class="acc-closed icon-ok-circle"></i><i class="acc-open icon-remove-circle"></i>Week <?php echo $bts['day']; ?></div>
            <div class="acc_content clearfix">
              <div class="row">
                <div class="col-md-2">
                  <div class="scenes-day green"> <span>Week <i><?php echo $bts['day']; ?></i></span> </div>
                </div>
                <div class="col-md-6">
                  <div style="margin-top: 10px;" class="heading-block">
                    <h4><?php echo $bts['title']; ?></h4>
                  </div>
                  <!--<p>content</p> -->
                  <?php echo $bts['display_note']; ?>
                </div>
                <div class="col-md-4"> <a href="videos/<?php echo $bts['video_url']; ?>" data-lightbox="iframe" class=""><img src="<?php echo $bts['screenshot']; ?>" class="img-responsive" alt="<?php echo $bts['title']; ?>">
                  <div class="overlay">
                    <div class="overlay-wrap"><i class="icon-youtube-play"></i></div>
                  </div>
                  </a> </div>
              </div>
              <div class="clearfix"></div>
              <div class="divider"><i class="icon-circle"></i></div>
              <div id="oc-portfolio1" class="owl-carousel portfolio-carousel">
              	<?php
					$result2 = mysql_query("SELECT * FROM behind_the_scenes_images WHERE bts='$bts_id'");
					if(mysql_num_rows($result2) > 0) {
						while($image = mysql_fetch_assoc($result2)) {
						
						
				?>
                <div class="oc-item">
                  <div class="iportfolio">
                    <div class="portfolio-image"> <a href="<?php echo $image['image'] ?>" data-lightbox="image"> <img src="<?php echo $image['image_thumb'] ?>">
                      <div class="overlay">
                        <div class="overlay-wrap"><i class="icon-line-plus"></i></div>
                      </div>
                      </a> </div>
                  </div>
                </div>
                <?php 
						}
					}
				?>
                
                <!--<div class="oc-item">
                  <div class="iportfolio">
                    <div class="portfolio-image"> <a href="images/portfolio/4/1.jpg" data-lightbox="image"> <img src="images/portfolio/4/1.jpg" alt="Single Image">
                      <div class="overlay">
                        <div class="overlay-wrap"><i class="icon-line-plus"></i></div>
                      </div>
                      </a> </div>
                  </div>
                </div>
                <div class="oc-item">
                  <div class="iportfolio">
                    <div class="portfolio-image"> <a href="images/portfolio/4/1.jpg" data-lightbox="image"> <img src="images/portfolio/4/1.jpg" alt="Single Image">
                      <div class="overlay">
                        <div class="overlay-wrap"><i class="icon-line-plus"></i></div>
                      </div>
                      </a> </div>
                  </div>
                </div>
                <div class="oc-item">
                  <div class="iportfolio">
                    <div class="portfolio-image"> <a href="images/portfolio/4/1.jpg" data-lightbox="image"> <img src="images/portfolio/4/1.jpg" alt="Single Image">
                      <div class="overlay">
                        <div class="overlay-wrap"><i class="icon-line-plus"></i></div>
                      </div>
                      </a> </div>
                  </div>
                </div>
                <div class="oc-item">
                  <div class="iportfolio">
                    <div class="portfolio-image"> <a href="images/portfolio/4/1.jpg" data-lightbox="image"> <img src="images/portfolio/4/1.jpg" alt="Single Image">
                      <div class="overlay">
                        <div class="overlay-wrap"><i class="icon-line-plus"></i></div>
                      </div>
                      </a> </div>
                  </div>
                </div>
                <div class="oc-item">
                  <div class="iportfolio">
                    <div class="portfolio-image"> <a href="images/portfolio/4/1.jpg" data-lightbox="image"> <img src="images/portfolio/4/1.jpg" alt="Single Image">
                      <div class="overlay">
                        <div class="overlay-wrap"><i class="icon-line-plus"></i></div>
                      </div>
                      </a> </div>
                  </div>
                </div>
                <div class="oc-item">
                  <div class="iportfolio">
                    <div class="portfolio-image"> <a href="images/portfolio/4/1.jpg" data-lightbox="image"> <img src="images/portfolio/4/1.jpg" alt="Single Image">
                      <div class="overlay">
                        <div class="overlay-wrap"><i class="icon-line-plus"></i></div>
                      </div>
                      </a> </div>
                  </div>
                </div>
                <div class="oc-item">
                  <div class="iportfolio">
                    <div class="portfolio-image"> <a href="images/portfolio/4/1.jpg" data-lightbox="image"> <img src="images/portfolio/4/1.jpg" alt="Single Image">
                      <div class="overlay">
                        <div class="overlay-wrap"><i class="icon-line-plus"></i></div>
                      </div>
                      </a> </div>
                  </div>
                </div> -->
              </div>
            </div>
          <?php 
				}
			}
		  ?>
            
            <!--<div class="acctitle"><i class="acc-closed icon-ok-circle"></i><i class="acc-open icon-remove-circle"></i>Day 3</div>
            <div class="acc_content clearfix">
              <div class="row">
                <div class="col-md-2">
                  <div class="scenes-day blue"> <span>DAY <i>4</i></span> </div>
                </div>
                <div class="col-md-6">
                  <div style="margin-top: 10px;" class="heading-block">
                    <h4>Title comingsoon here</h4>
                  </div>
                  <p>Content cominsoon here content cominsoon here content cominsoon here content cominsoon here content cominsoon here content cominsoon here content cominsoon here content cominsoon here content cominsoon here content cominsoon here content cominsoon here content cominsoon here content demo cominsoon here.</p>
                </div>
                <div class="col-md-4"> <a href="images/videos/explore.mp4" data-lightbox="iframe" class=""><img src="images/team/3.jpg" class="img-responsive" alt="Youtube Video">
                  <div class="overlay">
                    <div class="overlay-wrap"><i class="icon-youtube-play"></i></div>
                  </div>
                  </a> </div>
              </div>
              <div class="clearfix"></div>
              <div class="divider"><i class="icon-circle"></i></div>
              <div id="oc-portfolio2" class="owl-carousel portfolio-carousel">
                <div class="oc-item">
                  <div class="iportfolio">
                    <div class="portfolio-image"> <a href="images/portfolio/4/1.jpg" data-lightbox="image"> <img src="images/portfolio/4/1.jpg" alt="Single Image">
                      <div class="overlay">
                        <div class="overlay-wrap"><i class="icon-line-plus"></i></div>
                      </div>
                      </a> </div>
                  </div>
                </div>
                <div class="oc-item">
                  <div class="iportfolio">
                    <div class="portfolio-image"> <a href="images/portfolio/4/1.jpg" data-lightbox="image"> <img src="images/portfolio/4/1.jpg" alt="Single Image">
                      <div class="overlay">
                        <div class="overlay-wrap"><i class="icon-line-plus"></i></div>
                      </div>
                      </a> </div>
                  </div>
                </div>
                <div class="oc-item">
                  <div class="iportfolio">
                    <div class="portfolio-image"> <a href="images/portfolio/4/1.jpg" data-lightbox="image"> <img src="images/portfolio/4/1.jpg" alt="Single Image">
                      <div class="overlay">
                        <div class="overlay-wrap"><i class="icon-line-plus"></i></div>
                      </div>
                      </a> </div>
                  </div>
                </div>
                <div class="oc-item">
                  <div class="iportfolio">
                    <div class="portfolio-image"> <a href="images/portfolio/4/1.jpg" data-lightbox="image"> <img src="images/portfolio/4/1.jpg" alt="Single Image">
                      <div class="overlay">
                        <div class="overlay-wrap"><i class="icon-line-plus"></i></div>
                      </div>
                      </a> </div>
                  </div>
                </div>
                <div class="oc-item">
                  <div class="iportfolio">
                    <div class="portfolio-image"> <a href="images/portfolio/4/1.jpg" data-lightbox="image"> <img src="images/portfolio/4/1.jpg" alt="Single Image">
                      <div class="overlay">
                        <div class="overlay-wrap"><i class="icon-line-plus"></i></div>
                      </div>
                      </a> </div>
                  </div>
                </div>
                <div class="oc-item">
                  <div class="iportfolio">
                    <div class="portfolio-image"> <a href="images/portfolio/4/1.jpg" data-lightbox="image"> <img src="images/portfolio/4/1.jpg" alt="Single Image">
                      <div class="overlay">
                        <div class="overlay-wrap"><i class="icon-line-plus"></i></div>
                      </div>
                      </a> </div>
                  </div>
                </div>
                <div class="oc-item">
                  <div class="iportfolio">
                    <div class="portfolio-image"> <a href="images/portfolio/4/1.jpg" data-lightbox="image"> <img src="images/portfolio/4/1.jpg" alt="Single Image">
                      <div class="overlay">
                        <div class="overlay-wrap"><i class="icon-line-plus"></i></div>
                      </div>
                      </a> </div>
                  </div>
                </div>
                <div class="oc-item">
                  <div class="iportfolio">
                    <div class="portfolio-image"> <a href="images/portfolio/4/1.jpg" data-lightbox="image"> <img src="images/portfolio/4/1.jpg" alt="Single Image">
                      <div class="overlay">
                        <div class="overlay-wrap"><i class="icon-line-plus"></i></div>
                      </div>
                      </a> </div>
                  </div>
                </div>
              </div>
            </div>
            
            <div class="acctitle"><i class="acc-closed icon-ok-circle"></i><i class="acc-open icon-remove-circle"></i>Day 2</div>
            <div class="acc_content clearfix">
              <div class="row">
                <div class="col-md-2">
                  <div class="scenes-day yellow"> <span>DAY <i>2</i></span> </div>
                </div>
                <div class="col-md-6">
                  <div style="margin-top: 10px;" class="heading-block">
                    <h4>Title comingsoon here</h4>
                  </div>
                  <p>Content cominsoon here content cominsoon here content cominsoon here content cominsoon here content cominsoon here content cominsoon here content cominsoon here content cominsoon here content cominsoon here content cominsoon here content cominsoon here content cominsoon here content demo cominsoon here.</p>
                </div>
                <div class="col-md-4"> <a href="images/videos/explore.mp4" data-lightbox="iframe" class=""><img src="images/team/3.jpg" class="img-responsive" alt="Youtube Video">
                  <div class="overlay">
                    <div class="overlay-wrap"><i class="icon-youtube-play"></i></div>
                  </div>
                  </a> </div>
              </div>
              <div class="clearfix"></div>
              <div class="divider"><i class="icon-circle"></i></div>
              <div id="oc-portfolio3" class="owl-carousel portfolio-carousel">
                <div class="oc-item">
                  <div class="iportfolio">
                    <div class="portfolio-image"> <a href="images/portfolio/4/1.jpg" data-lightbox="image"> <img src="images/portfolio/4/1.jpg" alt="Single Image">
                      <div class="overlay">
                        <div class="overlay-wrap"><i class="icon-line-plus"></i></div>
                      </div>
                      </a> </div>
                  </div>
                </div>
                <div class="oc-item">
                  <div class="iportfolio">
                    <div class="portfolio-image"> <a href="images/portfolio/4/1.jpg" data-lightbox="image"> <img src="images/portfolio/4/1.jpg" alt="Single Image">
                      <div class="overlay">
                        <div class="overlay-wrap"><i class="icon-line-plus"></i></div>
                      </div>
                      </a> </div>
                  </div>
                </div>
                <div class="oc-item">
                  <div class="iportfolio">
                    <div class="portfolio-image"> <a href="images/portfolio/4/1.jpg" data-lightbox="image"> <img src="images/portfolio/4/1.jpg" alt="Single Image">
                      <div class="overlay">
                        <div class="overlay-wrap"><i class="icon-line-plus"></i></div>
                      </div>
                      </a> </div>
                  </div>
                </div>
                <div class="oc-item">
                  <div class="iportfolio">
                    <div class="portfolio-image"> <a href="images/portfolio/4/1.jpg" data-lightbox="image"> <img src="images/portfolio/4/1.jpg" alt="Single Image">
                      <div class="overlay">
                        <div class="overlay-wrap"><i class="icon-line-plus"></i></div>
                      </div>
                      </a> </div>
                  </div>
                </div>
                <div class="oc-item">
                  <div class="iportfolio">
                    <div class="portfolio-image"> <a href="images/portfolio/4/1.jpg" data-lightbox="image"> <img src="images/portfolio/4/1.jpg" alt="Single Image">
                      <div class="overlay">
                        <div class="overlay-wrap"><i class="icon-line-plus"></i></div>
                      </div>
                      </a> </div>
                  </div>
                </div>
                <div class="oc-item">
                  <div class="iportfolio">
                    <div class="portfolio-image"> <a href="images/portfolio/4/1.jpg" data-lightbox="image"> <img src="images/portfolio/4/1.jpg" alt="Single Image">
                      <div class="overlay">
                        <div class="overlay-wrap"><i class="icon-line-plus"></i></div>
                      </div>
                      </a> </div>
                  </div>
                </div>
                <div class="oc-item">
                  <div class="iportfolio">
                    <div class="portfolio-image"> <a href="images/portfolio/4/1.jpg" data-lightbox="image"> <img src="images/portfolio/4/1.jpg" alt="Single Image">
                      <div class="overlay">
                        <div class="overlay-wrap"><i class="icon-line-plus"></i></div>
                      </div>
                      </a> </div>
                  </div>
                </div>
                <div class="oc-item">
                  <div class="iportfolio">
                    <div class="portfolio-image"> <a href="images/portfolio/4/1.jpg" data-lightbox="image"> <img src="images/portfolio/4/1.jpg" alt="Single Image">
                      <div class="overlay">
                        <div class="overlay-wrap"><i class="icon-line-plus"></i></div>
                      </div>
                      </a> </div>
                  </div>
                </div>
              </div>
            </div>
            
            <div class="acctitle"><i class="acc-closed icon-ok-circle"></i><i class="acc-open icon-remove-circle"></i>Day 1</div>
            <div class="acc_content">
              <div class="row">
                <div class="col-md-2">
                  <div class="scenes-day red"> <span>DAY <i>1</i></span> </div>
                </div>
                <div class="col-md-6">
                  <div style="margin-top: 10px;" class="heading-block">
                    <h4>Title comingsoon here</h4>
                  </div>
                  <p>Content cominsoon here content cominsoon here content cominsoon here content cominsoon here content cominsoon here content cominsoon here content cominsoon here content cominsoon here content cominsoon here content cominsoon here content cominsoon here content cominsoon here content demo cominsoon here.</p>
                </div>
                <div class="col-md-4"> <a href="images/videos/explore.mp4" data-lightbox="iframe" class=""><img src="images/team/3.jpg" class="img-responsive" alt="Youtube Video">
                  <div class="overlay">
                    <div class="overlay-wrap"><i class="icon-youtube-play"></i></div>
                  </div>
                  </a> </div>
              </div>
              <div class="clear"></div>
              <div class="divider"><i class="icon-circle"></i></div>
              <div id="oc-portfolio4" class="owl-carousel portfolio-carousel">
                <div class="oc-item">
                  <div class="iportfolio">
                    <div class="portfolio-image"> <a href="images/portfolio/4/1.jpg" data-lightbox="image"> <img src="images/portfolio/4/1.jpg" alt="Single Image">
                      <div class="overlay">
                        <div class="overlay-wrap"><i class="icon-line-plus"></i></div>
                      </div>
                      </a> </div>
                  </div>
                </div>
                <div class="oc-item">
                  <div class="iportfolio">
                    <div class="portfolio-image"> <a href="images/portfolio/4/1.jpg" data-lightbox="image"> <img src="images/portfolio/4/1.jpg" alt="Single Image">
                      <div class="overlay">
                        <div class="overlay-wrap"><i class="icon-line-plus"></i></div>
                      </div>
                      </a> </div>
                  </div>
                </div>
                <div class="oc-item">
                  <div class="iportfolio">
                    <div class="portfolio-image"> <a href="images/portfolio/4/1.jpg" data-lightbox="image"> <img src="images/portfolio/4/1.jpg" alt="Single Image">
                      <div class="overlay">
                        <div class="overlay-wrap"><i class="icon-line-plus"></i></div>
                      </div>
                      </a> </div>
                  </div>
                </div>
                <div class="oc-item">
                  <div class="iportfolio">
                    <div class="portfolio-image"> <a href="images/portfolio/4/1.jpg" data-lightbox="image"> <img src="images/portfolio/4/1.jpg" alt="Single Image">
                      <div class="overlay">
                        <div class="overlay-wrap"><i class="icon-line-plus"></i></div>
                      </div>
                      </a> </div>
                  </div>
                </div>
                <div class="oc-item">
                  <div class="iportfolio">
                    <div class="portfolio-image"> <a href="images/portfolio/4/1.jpg" data-lightbox="image"> <img src="images/portfolio/4/1.jpg" alt="Single Image">
                      <div class="overlay">
                        <div class="overlay-wrap"><i class="icon-line-plus"></i></div>
                      </div>
                      </a> </div>
                  </div>
                </div>
                <div class="oc-item">
                  <div class="iportfolio">
                    <div class="portfolio-image"> <a href="images/portfolio/4/1.jpg" data-lightbox="image"> <img src="images/portfolio/4/1.jpg" alt="Single Image">
                      <div class="overlay">
                        <div class="overlay-wrap"><i class="icon-line-plus"></i></div>
                      </div>
                      </a> </div>
                  </div>
                </div>
                <div class="oc-item">
                  <div class="iportfolio">
                    <div class="portfolio-image"> <a href="images/portfolio/4/1.jpg" data-lightbox="image"> <img src="images/portfolio/4/1.jpg" alt="Single Image">
                      <div class="overlay">
                        <div class="overlay-wrap"><i class="icon-line-plus"></i></div>
                      </div>
                      </a> </div>
                  </div>
                </div>
                <div class="oc-item">
                  <div class="iportfolio">
                    <div class="portfolio-image"> <a href="images/portfolio/4/1.jpg" data-lightbox="image"> <img src="images/portfolio/4/1.jpg" alt="Single Image">
                      <div class="overlay">
                        <div class="overlay-wrap"><i class="icon-line-plus"></i></div>
                      </div>
                      </a> </div>
                  </div>
                </div>
              </div>
            </div> -->
            
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
<script type="text/javascript">

						jQuery(document).ready(function($) {

							var ocPortfolio = $("#oc-portfolio1, #oc-portfolio2, #oc-portfolio3, #oc-portfolio4");

							ocPortfolio.owlCarousel({
								margin: 20,
								nav: true,
								navText: ['<i class="icon-angle-left"></i>','<i class="icon-angle-right"></i>'],
								autoplay: false,
								autoplayHoverPause: true,
								dots: false,
								responsive:{
									0:{ items:1 },
									600:{ items:2 },
									1000:{ items:4 },
									1200:{ items:6 }
								}
							});
							
						});



					</script> 
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