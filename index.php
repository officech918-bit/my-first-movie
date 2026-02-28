<?php
declare(strict_types=1);

/**
 * Index Page
 *
 * This is the main landing page for MyFirstMovie. It displays a collage of
 * behind-the-scenes moments, testimonials, news, and calls to action.
 * It serves as the central hub for visitors.
 *
 * @package     MyFirstMovie
 * @subpackage  Pages
 * @since       1.0.0
 */

require_once __DIR__ . '/inc/requires.php';

// Load environment variables from .env file
if (class_exists('Dotenv\Dotenv')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/');
    $dotenv->load();
}

// Load S3Uploader for environment detection
if (file_exists(__DIR__ . '/classes/S3Uploader.php')) {
    require_once __DIR__ . '/classes/S3Uploader.php';
    $s3Uploader = new S3Uploader();
}

// --- Force Error Reporting ---
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL & ~E_DEPRECATED);

// Session is now started globally in requires.php

// Generate CSRF token if it doesn't exist
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

date_default_timezone_set('Asia/Kolkata');

// --- Includes and Object Initialization (with hardened paths) ---
$database = new MySQLDB();
$user = new visitor(); // First, create a base visitor
if ($user->check_session()) { // Then, check the session
    $user = new web_user(); // If logged in, elevate to web_user
}

// --- Configuration and Path Setup ---
$sitename = $user->get_sitename();
$sub_location = $user->get_sub_location();
$path = $sitename . ($sub_location ? '/' . $sub_location : '') . '/';

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

/**
 * Get image URL based on environment
 */
function getImageUrl(string $imagePath, string $uploadPath = ''): string {
    if (empty($imagePath)) {
        return 'https://placehold.co/500x375/EFEFEF/AAAAAA&text=No+Image';
    }
    
    if ($GLOBALS['s3Uploader']->isS3Enabled()) {
        // In production, assume S3 URLs are stored
        return $imagePath;
    } else {
        // In local development, convert relative paths to full URLs
        if (strpos($imagePath, 'http') === 0) {
            return $imagePath; // Already a full URL
        }
        
        // Handle different upload paths
        if (!empty($uploadPath) && strpos($imagePath, $uploadPath) !== 0) {
            return $GLOBALS['correct_base_path'] . "/" . $uploadPath . "/" . $imagePath;
        }
        
        // Handle BTS images specifically - they're stored in admin/uploads/bts/
        if (strpos($imagePath, 'bts/') === 0) {
            return $GLOBALS['correct_base_path'] . "/admin/uploads/" . $imagePath;
        }
        
        return $GLOBALS['correct_base_path'] . "/" . $imagePath; // Convert to relative path
    }
}

// --- Data Fetching and Processing ---

// Determine the season ID securely
$season_id = null;

// First, try to get the default season
$stmt = $database->db->prepare("SELECT id FROM seasons WHERE is_default = 1 AND (status = 'ACTIVE' OR status = '1') LIMIT 1");
if ($stmt) {
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($result) {
        $season_id = $result['id'];
    }

}

// If no default season, get the newest one
if ($season_id === null) {
    $stmt = $database->db->prepare("SELECT id FROM seasons WHERE (status='ACTIVE' OR status='1') ORDER BY create_date DESC LIMIT 1");
    if ($stmt) {
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            $season_id = $result['id'];
        }
    }
}

// Fallback to a hardcoded ID if no season is found
if ($season_id === null) {
    $season_id = 1; 
}

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $season_id = (int)$_GET['id'];
}

// Fetch season details using a prepared statement
$season = [];
$stmt = $database->db->prepare("SELECT title FROM seasons WHERE id = ?");
if ($stmt) {
    $stmt->execute([$season_id]);
    $season = $stmt->fetch(PDO::FETCH_ASSOC);
}


// Fetch "Behind the Scenes" data
$bts_items = [];
$stmt = $database->db->prepare("SELECT id, day, title, display_note, screenshot, video_url FROM behind_the_scenes WHERE season = ? AND status='1' ORDER BY day DESC LIMIT 2");
if ($stmt) {
    $stmt->execute([$season_id]);
    $bts_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch Testimonials
$testimonials = [];
$stmt = $database->db->prepare("SELECT logo, testimonial, client_name, company FROM testimonials WHERE status='1' ORDER BY short_order ASC");
if ($stmt) {
    $stmt->execute();
    $testimonials = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch News (Admin and API)
$all_news = [];

// 1. Get Admin News
$stmt = $database->db->prepare("SELECT id, headline, content, image FROM industry_news WHERE status = 1 AND is_admin_news = 1 ORDER BY create_date DESC");
if ($stmt) {
    $stmt->execute();
    $admin_news_result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($admin_news_result) {
        foreach($admin_news_result as $news_item) {
            $all_news[] = [
                'headline' => $news_item['headline'],
                'content' => $news_item['content'],
                'image' => getImageUrl($news_item['image']),
                'url' => 'news-details.php?id=' . $news_item['id'],
                'is_external' => false
            ];
        }
    }
}

// 2. Get API News (with hardened path)
require_once __DIR__ . '/inc/fetch_api_news.php';
$api_news = get_api_news();
if(is_array($api_news)) {
    foreach ($api_news as $news_item) {
        $all_news[] = [
            'headline' => $news_item['headline'],
            'content' => $news_item['content'],
            'image' => !empty($news_item['image']) ? $news_item['image'] : 'https://placehold.co/500x375/EFEFEF/AAAAAA&text=No+Image',
            'url' => $news_item['url'],
            'is_external' => true
        ];
    }
}

// 3. Limit total news items
$all_news = array_slice($all_news, 0, 8);
?>

<!DOCTYPE html>
<html dir="ltr" lang="en-US">
<head>
<title>My First Movie | Home</title>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />

<!-- Stylesheets
	============================================= -->
<link href="https://fonts.googleapis.com/css?family=Lato:300,400,400italic,600,700|Raleway:300,400,500,600,700|Crete+Round:400italic" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="css/bootstrap.css" type="text/css" />
<link rel="stylesheet" href="style.css" type="text/css" />
<link rel="stylesheet" href="css/custom-style.css" type="text/css" />
<link rel="stylesheet" href="css/dark.css" type="text/css" />
<link rel="stylesheet" href="css/font-icons.css" type="text/css" />
<link rel="stylesheet" href="css/animate.css" type="text/css" />
<link rel="stylesheet" href="css/magnific-popup.css" type="text/css" />
<link rel="stylesheet" href="css/responsive.css" type="text/css" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<!--[if lt IE 9]>
		<script src="https://css3-mediaqueries-js.googlecode.com/svn/trunk/css3-mediaqueries.js"></script>
	<![endif]-->

<!-- External JavaScripts
	============================================= -->
<script type="text/javascript" src="js/jquery.js" nonce="<?= $nonce ?>"></script>
<script type="text/javascript" src="js/plugins.js" nonce="<?= $nonce ?>"></script>

<!-- Footer Scripts
	============================================= -->
<!-- <script type="text/javascript" src="js/functions.js" nonce="<?= $nonce ?>"></script> -->

<!-- Document Title
	============================================= -->
<?php include('inc/before_head_close.php'); ?> 
</head>

<body class="stretched">

<!-- Document Wrapper
	============================================= -->
<div id="wrapper" class="clearfix animsition">
	<?php include('inc/after_body_start.php'); ?>  
  <!-- Top Bar
		============================================= --> 
  <!-- #top-bar end --> 
  
  <!-- Header
		============================================= -->
  <?php include('inc/header.php'); ?> 
  <!-- #header end -->
  
  <section id="slider" class="slider-parallax swiper_wrapper full-screen clearfix">
    <div class="swiper-container swiper-parent">
      <div class="swiper-wrapper">
        <div class="swiper-slide dark">
          <div class="container clearfix">
            <div class="slider-caption slider-caption-center">
              <h2 data-caption-animate="fadeInUp">You could be the Next One</h2>
              <p data-caption-animate="fadeInUp" data-caption-delay="200">Don’t keep your story to you only…let the world see it on a movie! <br>
                A contest for Common man with an uncommon story… First time in India… Begins   Now</p>
              <br>
              <a href="members/dashboard.php" data-caption-animate="fadeInUp" data-caption-delay="400"  class="button button-border button-white button-light button-large button-rounded tright nomargin"><span>Submit to Our Selection Panel</span> <i class="icon-angle-right"></i></a> </div>
          </div>
          <div class="video-wrap">
            <video preload="auto" loop autoplay muted playsinline poster="images/videos/poster.jpg">
              <source src='images/videos/myfirstmovie-vedio-1.webm' type='video/webm' />
              <source src='images/videos/myfirstmovie-vedio-1.mp4' type='video/mp4' />
              <track src="images/videos/captions.vtt" kind="captions" srclang="en" label="English" />
            </video>
            <div class="video-overlay" style="background-color: rgba(0,0,0,0.55);"></div>
          </div>
        </div>
      </div>
    </div>
    <script nonce="<?= $nonce ?>">
			jQuery(document).ready(function($){
				var swiperSlider = new Swiper('.swiper-parent',{
					paginationClickable: false,
					slidesPerView: 1,
					grabCursor: true,
					loop: true,
					onSwiperCreated: function(swiper){
						$('[data-caption-animate]').each(function(){
							var $toAnimateElement = $(this);
							var toAnimateDelay = $(this).attr('data-caption-delay');
							var toAnimateDelayTime = 0;
							if( toAnimateDelay ) { toAnimateDelayTime = Number( toAnimateDelay ) + 750; } else { toAnimateDelayTime = 750; }
							if( !$toAnimateElement.hasClass('animated') ) {
								$toAnimateElement.addClass('not-animated');
								var elementAnimation = $toAnimateElement.attr('data-caption-animate');
								setTimeout(function() {
									$toAnimateElement.removeClass('not-animated').addClass( elementAnimation + ' animated');
								}, toAnimateDelayTime);
							}
						});
						SEMICOLON.slider.swiperSliderMenu();
					},
					onSlideChangeStart: function(swiper){
						$('[data-caption-animate]').each(function(){
							var $toAnimateElement = $(this);
							var elementAnimation = $toAnimateElement.attr('data-caption-animate');
							$toAnimateElement.removeClass('animated').removeClass(elementAnimation).addClass('not-animated');
						});
						SEMICOLON.slider.swiperSliderMenu();
					},
					onSlideChangeEnd: function(swiper){
						$('#slider').find('.swiper-slide').each(function(){
							if($(this).find('video').length > 0) { $(this).find('video').get(0).pause(); }
							if($(this).find('.yt-bg-player').length > 0) { $(this).find('.yt-bg-player').pauseYTP(); }
						});
						$('#slider').find('.swiper-slide:not(".swiper-slide-active")').each(function(){
							if($(this).find('video').length > 0) {
								if($(this).find('video').get(0).currentTime != 0 ) $(this).find('video').get(0).currentTime = 0;
							}
							if($(this).find('.yt-bg-player').length > 0) {
								$(this).find('.yt-bg-player').getPlayer().seekTo( $(this).find('.yt-bg-player').attr('data-start') );
							}
						});
						if( $('#slider').find('.swiper-slide.swiper-slide-active').find('video').length > 0 ) { $('#slider').find('.swiper-slide.swiper-slide-active').find('video').get(0).play(); }
						if( $('#slider').find('.swiper-slide.swiper-slide-active').find('.yt-bg-player').length > 0 ) { $('#slider').find('.swiper-slide.swiper-slide-active').find('.yt-bg-player').playYTP(); }

						$('#slider .swiper-slide.swiper-slide-active [data-caption-animate]').each(function(){
							var $toAnimateElement = $(this);
							var toAnimateDelay = $(this).attr('data-caption-delay');
							var toAnimateDelayTime = 0;
							if( toAnimateDelay ) { toAnimateDelayTime = Number( toAnimateDelay ) + 300; } else { toAnimateDelayTime = 300; }
							if( !$toAnimateElement.hasClass('animated') ) {
								$toAnimateElement.addClass('not-animated');
								var elementAnimation = $toAnimateElement.attr('data-caption-animate');
								setTimeout(function() {
									$toAnimateElement.removeClass('not-animated').addClass( elementAnimation + ' animated');
								}, toAnimateDelayTime);
							}
						});
					}
				});

				$('#slider-arrow-left').on('click', function(e){
					e.preventDefault();
					swiperSlider.swipePrev();
				});

				$('#slider-arrow-right').on('click', function(e){
					e.preventDefault();
					swiperSlider.swipeNext();
				});
			});
		</script> 
  </section>
  
  <!-- Content
		============================================= -->
  <section id="content">
    <div class="content-wrap nopadding">
      <div class=" section nomargin " style="padding-bottom:0px;">
        <div class="container clearfix">
          <div class="row clearfix">
            <div class="col-lg-8">
              <div class="heading-block">
                <h1>Welcome to My First Movie!</h1>
              </div>
              <p class="lead" style="text-align:justify;">MyfirstMovie.in is a movie production business founded by Mr Satyam Raj (a film maker with more than 12 years of experience in the field of post production and movie making). The purpose of this business is to produce movies for story-tellers who do not know what to do with their stories. The entire concept of this business is explained below.</p>
              <a class="button button-border button-rounded button-large noleftmargin" href="about-mfm.php">Learn More</a> </div>
            <div class="col-lg-4">
              <div style="position: relative; margin-bottom: -60px;" class="ohidden" data-height-lg="350" data-height-md="400"
               data-height-sm="350" data-height-xs="287" data-height-xxs="183"> <?= lazy_image('images/others/satyam raj.jpgg', 'Satyam Raj', 'img-responsive', ['data-animate' => 'fadeInUp', 'data-delay' => '100']) ?> </div>
            </div>
          </div>
        </div>
      </div>
      <div class="container clearfix bottommargin-sm topmargin-sm">
        <div class="col_two_third nobottommargin">
          <div class="fancy-title title-border">
            <h4>Recent behind the scenes <?php if (!empty($season['title'])): ?>[<?= htmlspecialchars($season['title']) ?>]<?php endif; ?></h4>
          </div>
          
          <?php if (!empty($bts_items)): ?>
            <?php foreach ($bts_items as $index => $bts): ?>
              <div class="col_half <?= ($index === 1) ? 'col_last' : '' ?> nobottommargin">
                <div class="ipost clearfix">
                  <div class="entry-image">
                    <a href="<?= !empty($bts['video_url']) ? htmlspecialchars($bts['video_url']) : 'behind-the-scenes.php?id=' . $bts['id'] ?>" <?= !empty($bts['video_url']) ? 'data-lightbox="iframe"' : '' ?> class="">
                      <?php 
                      // DEBUG: Show BTS data
                      echo "<!-- DEBUG BTS DATA: " . print_r($bts, true) . " -->";
                      
                      // Use the getImageUrl function for environment-aware URLs
                      // For BTS images, we need to add the bts/ prefix if it's not already there
                      $btsImage = $bts['screenshot'];
                      echo "<!-- DEBUG Original BTS Image: '$btsImage' -->";
                      
                      if (!empty($btsImage) && strpos($btsImage, 'bts/') !== 0) {
                          // Only add bts/ prefix if it doesn't already start with bts/
                          $btsImage = 'bts/' . $btsImage;
                          echo "<!-- DEBUG Modified BTS Image: '$btsImage' -->";
                      }
                      
                      $imageUrl = getImageUrl($btsImage);
                      echo "<!-- DEBUG Final Image URL: '$imageUrl' -->";
                      echo "<!-- DEBUG S3 Enabled: " . ($s3Uploader && $s3Uploader->isS3Enabled() ? 'YES' : 'NO') . " -->";
                      echo "<!-- DEBUG Base Path: '$correct_base_path' -->";
                      
                      // Check if file exists (local or S3)
                      if ($s3Uploader && $s3Uploader->isS3Enabled()) {
                          // S3 mode: Check if S3 object exists
                          $s3Key = str_replace($s3Uploader->getS3BaseUrl() . '/', '', $imageUrl);
                          echo "<!-- DEBUG S3 Key Check: '$s3Key' -->";
                          
                          if (!$s3Uploader->fileExists($s3Key)) {
                              echo "<!-- DEBUG S3 File Exists: NO -->";
                              
                              // Try to find similar S3 files
                              $s3Prefix = 'bts/';
                              $s3Files = $s3Uploader->listFiles($s3Prefix);
                              echo "<!-- DEBUG Available S3 Files: " . print_r($s3Files, true) . " -->";
                              
                              // Look for similar files (same type: bts_title_)
                              $searchBase = pathinfo($bts['screenshot'], PATHINFO_FILENAME);
                              foreach ($s3Files as $s3File) {
                                  $fileBase = pathinfo($s3File, PATHINFO_FILENAME);
                                  if (strpos($fileBase, 'bts_title_') === 0 && strpos($searchBase, 'bts_title_') === 0) {
                                      echo "<!-- DEBUG Found Similar S3 File: '$s3File' -->";
                                      $btsImage = 'bts/' . basename($s3File);
                                      $imageUrl = $s3Uploader->getFileUrl($btsImage);
                                      echo "<!-- DEBUG Using Similar S3 File: '$imageUrl' -->";
                                      break;
                                  }
                              }
                          } else {
                              echo "<!-- DEBUG S3 File Exists: YES -->";
                          }
                      } else {
                          // Local mode: Check if local file exists
                          $localPath = __DIR__ . "/admin/uploads/" . $btsImage;
                          echo "<!-- DEBUG Local Path Check: '$localPath' -->";
                          echo "<!-- DEBUG File Exists: " . (file_exists($localPath) ? 'YES' : 'NO') . " -->";
                          
                          // If file doesn't exist, try to find a similar file
                          if (!file_exists($localPath)) {
                              $btsDir = __DIR__ . "/admin/uploads/bts";
                              if (is_dir($btsDir)) {
                                  $files = scandir($btsDir);
                                  echo "<!-- DEBUG Available Local Files: " . print_r(array_diff($files, ['.', '..']), true) . " -->";
                                  
                                  foreach ($files as $file) {
                                      if ($file !== '.' && $file !== '..') {
                                          // Try to match by removing extension and comparing base names
                                          $fileBase = pathinfo($file, PATHINFO_FILENAME);
                                          $searchBase = pathinfo($bts['screenshot'], PATHINFO_FILENAME);
                                          
                                          // Check if the base names match (ignoring the random hash part)
                                          if (strpos($fileBase, 'bts_title_') === 0 && strpos($searchBase, 'bts_title_') === 0) {
                                              $altPath = $btsDir . '/' . $file;
                                              echo "<!-- DEBUG Found Similar Local File: '$file' -->";
                                              echo "<!-- DEBUG Similar File Exists: " . (file_exists($altPath) ? 'YES' : 'NO') . " -->";
                                              if (file_exists($altPath)) {
                                                  $btsImage = 'bts/' . $file;
                                                  $imageUrl = getImageUrl($btsImage);
                                                  echo "<!-- DEBUG Using Similar Local File: '$imageUrl' -->";
                                                  break;
                                              }
                                          }
                                      }
                                  }
                              }
                          }
                      }
                      ?>
                      <?= lazy_image($imageUrl, $bts['title'], 'img-responsive', ['style' => 'height: 300px; width: 100%; object-fit: cover;']) ?>
                      <?php if (!empty($bts['video_url'])): ?>
                        <div class="overlay"><div class="overlay-wrap"><i class="icon-youtube-play"></i></div></div>
                      <?php endif; ?>
                    </a>
                  </div>
                  <div class="entry-title">
                    <h3><a href="behind-the-scenes.php?id=<?= $bts['id'] ?>">Week <?= htmlspecialchars($bts['day']) ?> - <?= htmlspecialchars($bts['title']) ?></a></h3>
                  </div>
                  <div class="entry-content">
                    <p><?= htmlspecialchars(substr($bts['display_note'], 0, 100)) ?>...<a href="behind-the-scenes.php?id=<?= $bts['id'] ?>">read more</a></p>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <p>No "behind the scenes" content is available for this season.</p>
          <?php endif; ?>

          <div class="clear"></div>
        </div>
        <div class="col_one_third nobottommargin col_last">
          <div class="fancy-title title-border">
            <h4>What people said about us</h4>
          </div>
          <div class="fslider testimonial nopadding noborder noshadow" data-animation="slide" data-arrows="false">
            <div class="flexslider">
              <div class="slider-wrap">
                <?php if (!empty($testimonials)): ?>
                  <?php foreach ($testimonials as $testimonial): ?>
                    <div class="slide">
                      <div class="testi-image">
                        <a href="#">
                          <img src="<?= getImageUrl($testimonial['logo']) ?>" alt="<?= htmlspecialchars($testimonial['client_name']) ?>" />
                        </a>
                      </div>
                      <div class="testi-content">
                        <p><?= htmlspecialchars($testimonial['testimonial']) ?></p>
                        <div class="testi-meta">
                          <?= htmlspecialchars($testimonial['client_name']) ?>
                          <span><?= htmlspecialchars($testimonial['company']) ?></span>
                        </div>
                      </div>
                    </div>
                  <?php endforeach; ?>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
        <div class="clear"></div>
      </div>

      

          <div class="section notopmargin notopborder">
        <div class="container clearfix">
          <div class="heading-block left">
            <h3>Industry News</h3>
          </div>

          <style>
            /* Custom Responsive Fixes */
            .news-grid {
                display: flex;
                flex-wrap: wrap;
                margin: 0 -15px;
                
            }
            .news-item {
                padding: 15px;
                display: flex;
                flex-direction: column;
            }
            .news-item .portfolio-image {
                overflow: hidden;
                aspect-ratio: 4/3; /* Keeps all images uniform height */
            }
            .news-item .portfolio-image img {
                width: 100%;
                height: 100%;
                object-fit: cover;
            }
            .news-content {
                font-size: 14px;
                font-family: arial, sans-serif;
                line-height: 1.5;
                margin-top: 10px;
                display: -webkit-box;
                -webkit-line-clamp: 3; /* Show 3 lines then ellipsis */
                -webkit-box-orient: vertical;
                overflow: hidden;
                height: 63px; /* Fixed height for alignment */
            }
            .portfolio-desc h3 {
                font-size: 18px;
                margin-bottom: 8px;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }
            .read-more-link {
                display: inline-block;
                margin-top: 10px;
                font-weight: 600;
                font-size: 13px;
                color: #376cb1;
            }

            /* Responsive Grid adjustments */
            @media (max-width: 767px) {
                .news-item { width: 100%; } /* Mobile: 1 Column */
            }
            @media (min-width: 768px) and (max-width: 991px) {
                .news-item { width: 50%; } /* Tablet: 2 Columns */
            }
            @media (min-width: 992px) {
                .news-item { width: 25%; } /* Laptop/Desktop: 4 Columns */
            }
          </style>

          <div id="portfolio" class="news-grid clearfix">
            <?php if (!empty($all_news)): ?>
              <?php foreach ($all_news as $news): ?>
                <article class="news-item">
                    <div class="portfolio-image">
                        <a href="<?= htmlspecialchars($news['url']) ?>" <?= $news['is_external'] ? 'target="_blank" rel="noopener noreferrer"' : '' ?>>
                            <?= lazy_image($news['image'], $news['headline']) ?>
                        </a>
                        <div class="portfolio-overlay">
                            <a href="<?= htmlspecialchars($news['url']) ?>" <?= $news['is_external'] ? 'target="_blank" rel="noopener noreferrer"' : '' ?> class="center-icon"><i class="icon-line-plus"></i></a>
                        </div>
                    </div>
                    <div class="portfolio-desc">
                        <h3><a href="<?= htmlspecialchars($news['url']) ?>" <?= $news['is_external'] ? 'target="_blank" rel="noopener noreferrer"' : '' ?>><?= htmlspecialchars($news['headline']) ?></a></h3>
                        <div class="news-content"><?= htmlspecialchars(mb_strimwidth(strip_tags($news['content']), 0, 150, '...'), ENT_QUOTES, 'UTF-8') ?></div>
                        <a href="<?= htmlspecialchars($news['url']) ?>" <?= $news['is_external'] ? 'target="_blank" rel="noopener noreferrer"' : '' ?> class="read-more-link">Read More <i class="icon-angle-right"></i></a>
                    </div>
                </article>
              <?php endforeach; ?>
            <?php else: ?>
              <p>No news available at the moment.</p>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <div class="col_full common-height">
        <div style="background-color: rgb(140, 198, 62); height: 355px;" class="col-md-4 dark col-padding ohidden">
          <div>
            <h3 style="font-weight: 600;" class="uppercase">Why MFM</h3>
            <p style="line-height: 1.8;">It's an old saying "If your don't build your dreams, someone else will hire you to build their's". There is a rebel hiding inside us for a very long time, wanting to get out and...</p>
            <a class="button button-border button-light button-rounded uppercase nomargin" href="why-mfm.php">Read More</a> <i class="icon-star2 bgicon"></i> </div>
        </div>
        <div style="background-color: rgb(0, 137, 209); height: 355px;" class="col-md-4 dark col-padding ohidden">
          <div>
            <h3 style="font-weight: 600;" class="uppercase">The Core Team</h3>
            <p style="line-height: 1.8;">We define team as Together Everyone Achieves More. Our strength comes from the team and it takes a combined effort to execute a process as complex as movie making. </p>
            <a class="button button-border button-light button-rounded uppercase nomargin" href="the-core-team.php">Read More</a> <i class="icon-star2 bgicon"></i> </div>
        </div>
        <div style="background-color: rgb(237, 27, 36); height: 355px;" class="col-md-4 dark col-padding ohidden">
          <div>
            <h3 style="font-weight: 600;" class="uppercase">The Panalists</h3>
            <p style="line-height: 1.8;">We look up to the people who are at the pinnacle of what we strive to become. And there's no greater satisfaction than having to be mentored and guided by such dignitaries. </p>
            <a class="button button-border button-light button-rounded uppercase nomargin" href="the-panalists.php">Read More</a> <i class="icon-star2 bgicon"></i> </div>
        </div>
        <div class="clear"></div>
      </div>
      <div class="section nomargin" style="padding:50px 0 0px 0;">
        <div class="container clear-bottommargin clearfix">
          <div class="col_one_third">
            <div data-animate="fadeIn" class="feature-box fbox-small fbox-plain fadeIn animated">
              <div class="fbox-icon"> <a href="#"><i class="icon-star2"></i></a> </div>
              <h3>Call for Entries</h3>
              <p>Every few months MyFirstMovie will call for entries from some of the best minds and talent out there in the world to connect... <a href="how-it-works.php">Read More</a></p>
            </div>
          </div>
          <div class="col_one_third">
            <div data-delay="200" data-animate="fadeIn" class="feature-box fbox-small fbox-plain fadeIn animated">
              <div class="fbox-icon"> <a href="#"><i class="icon-video"></i></a> </div>
              <h3>Selection</h3>
              <p>Once we have the entries with us we will allow our panelist to select the best ones for the upcoming project... <a href="how-it-works.php">Read More</a></p>
            </div>
          </div>
          <div class="col_one_third col_last">
            <div data-delay="400" data-animate="fadeIn" class="feature-box fbox-small fbox-plain fadeIn animated">
              <div class="fbox-icon"> <a href="#"><i class="icon-star2"></i></a> </div>
              <h3>Pre-production</h3>
              <p>After selecting the best story to work on and the crew like DOP, Choreographer, Actors, Make-up, etc., from the entries we get,  <a href="how-it-works.php">Read More</a></p>
            </div>
          </div>
          <div class="clear"></div>
          <div class="col_one_third">
            <div data-delay="600" data-animate="fadeIn" class="feature-box fbox-small fbox-plain fadeIn animated">
              <div class="fbox-icon"> <a href="how-it-works.html"><i class="icon-video"></i></a> </div>
              <h3>Movie Production</h3>
              <p>After finalising the Screenplay, the cast and crew, locations, equipment’s, costumes, etc., it's now time for what everyone's... <a href="how-it-works.php">Read More</a> </p>
            </div>
          </div>
          <div class="col_one_third">
            <div data-delay="800" data-animate="fadeIn" class="feature-box fbox-small fbox-plain fadeIn animated">
              <div class="fbox-icon"> <a href="how-it-works.html"><i class="icon-star2"></i></a> </div>
              <h3>Post Production</h3>
              <p>We have completed the shooting with utmost hard work and dedication. Phew, what a relief... Not so soon. Whoever said movie making...  <a href="how-it-works.php">Read More</a></p>
            </div>
          </div>
          <div class="col_one_third col_last">
            <div data-delay="1000" data-animate="fadeIn" class="feature-box fbox-small fbox-plain fadeIn animated">
              <div class="fbox-icon"> <a href="how-it-works.html"><i class="icon-video"></i></a> </div>
              <h3>Premiere</h3>
              <p>What a relief! After such a tedious schedule of shooting and post production out final product is ready. So what do we do with it... <a href="how-it-works.php">Read More</a></p>
            </div>
          </div>
        </div>
      </div>
      <div class="clear"></div>
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
<script type="text/javascript" src="js/functions.js" nonce="<?= $nonce ?>"></script> 
<script type="text/javascript" src="js/jquery.pulsate.js" nonce="<?= $nonce ?>"></script> 
<script nonce="<?= $nonce ?>">
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
<script async src="https://cdn.curator.io/published/35925d53-84c2-4a42-9666-105f0d474cec.js" nonce="<?= $nonce ?>"></script>

</body>
</html>