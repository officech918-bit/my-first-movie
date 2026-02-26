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
<title>My First Movie | Selecteds</title>
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

<STYLE>
.comment {
	margin: 0px;
}
a.morelink {
	text-decoration: none;
	outline: none;
}
.morecontent span {
	display: none;
}
</STYLE>

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
      <h1>Selecteds</h1>
      <span>Everything you need to know about our Company</span>
      <ol class="breadcrumb">
        <li><a href="index.html">Home</a></li>
        <li class="active">Selecteds</li>
      </ol>
    </div>
  </section>
  <!-- #page-title end --> 
  
  <!-- Content
		============================================= -->
  <section id="content">
    <div class="content-wrap">
      <div class="container clearfix" style="padding-bottom:40px;">
        <div class="row clearfix">
          <div class="col-md-12">
            <div class="heading-block">
              <h3>Session Title</h3>
            </div>
          </div>
          <div class="col-lg-3">
            <div data-height-xxs="183" data-height-xs="287" class="ohidden"> <img alt="" data-delay="100" data-animate="fadeInUp" src="images/others/1.jpg" class="fadeInUp animated">
              <h4 style="margin:10px 0 5px 0;">Name goes here</h4>
              <h6>- Designation</h6>
            </div>
          </div>
          <div class="col-lg-9">
            <p style="text-align:justify;">Content cominsoon here content cominsoon here content cominsoon here content cominsoon here content cominsoon here content cominsoon here content cominsoon here content cominsoon here content cominsoon here content cominsoon here content cominsoon here content cominsoon here content cominsoon here content cominsoon here content cominsoon here soon here content cominsoon here content cominsoon here content cominsoon here content cominsoon. <br>
              Content cominsoon here content cominsoon here content cominsoon here content cominsoon here content cominsoon here content cominsoon here content cominsoon here. </p>
          </div>
          <div class="clearfix"></div>
          <div class="divider"><i class="icon-circle"></i></div>
          <div id="oc-portfolio" class="owl-carousel portfolio-carousel">
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
      </div>
      <div style="padding-bottom:40px; padding-top:30px;" class="section nomargin ">
        <div class="container clearfix">
          <div class="row clearfix">
            <div class="col-md-12">
              <div class="heading-block">
                <h3>Session Title</h3>
              </div>
            </div>
            <div class="col-lg-3">
              <div data-height-xxs="183" data-height-xs="287" class="ohidden"> <img alt="" data-delay="100" data-animate="fadeInUp" src="images/others/1.jpg" class="fadeInUp animated"> </div>
            </div>
            <div class="col-lg-9">
              <p style="text-align:justify;">Content cominsoon here content cominsoon here content cominsoon here content cominsoon here content cominsoon here content cominsoon here content cominsoon here content cominsoon here content cominsoon here content cominsoon here content cominsoon here content cominsoon here content cominsoon here content cominsoon here content cominsoon here soon here content cominsoon here content cominsoon here content cominsoon here content cominsoon. <br>
                Content cominsoon here content cominsoon here content cominsoon here content cominsoon here content cominsoon here content cominsoon here content cominsoon here. </p>
            </div>
            <div class="clearfix"></div>
            <div class="divider"><i class="icon-circle"></i></div>
            <div id="oc-portfolio1" class="owl-carousel portfolio-carousel">
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
<SCRIPT>
$(document).ready(function() {
	var showChar = 600;
	var ellipsestext = "...";
	var moretext = "more";
	var lesstext = "less";
	$('.more').each(function() {
		var content = $(this).html();

		if(content.length > showChar) {

			var c = content.substr(0, showChar);
			var h = content.substr(showChar-1, content.length - showChar);

			var html = c + '<span class="moreelipses">'+ellipsestext+'</span>&nbsp;<span class="morecontent"><span>' + h + '</span>&nbsp;&nbsp;<a href="" class="morelink">'+moretext+'</a></span>';

			$(this).html(html);
		}

	});

	$(".morelink").click(function(){
		if($(this).hasClass("less")) {
			$(this).removeClass("less");
			$(this).html(moretext);
		} else {
			$(this).addClass("less");
			$(this).html(lesstext);
		}
		$(this).parent().prev().toggle();
		$(this).prev().toggle();
		return false;
	});
});
</SCRIPT> 
<script type="text/javascript">

						jQuery(document).ready(function($) {

							var ocPortfolio = $("#oc-portfolio, #oc-portfolio1");

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
<?php include('inc/before_body_close.php'); ?>
</body>
</html>