<?php
/**
 * Behind the Scenes Page
 *
 * Displays "Behind the Scenes" content, organized by seasons.
 * It fetches and displays video and image galleries for different entries.
 *
 * @package     MyFirstMovie
 * @subpackage  Pages
 * @since       1.0.0
 */
declare(strict_types=1);

// Bootstrap the application
require_once 'inc/requires.php';

// Load environment variables from .env file
if (class_exists('Dotenv\Dotenv')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/');
    $dotenv->load();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Initialize objects and variables
$database = new MySQLDB();
$user = new visitor(); // First, create a base visitor
if ($user->check_session()) { // Then, check the session
    $user = new web_user(); // If logged in, elevate to web_user
}

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

$bts_entries = [];
$season_id = null;
$season = [];

// Check for a specific BTS entry ID and determine the season from it
$bts_id = filter_input(INPUT_GET, 'bts_id', FILTER_VALIDATE_INT);
if ($bts_id) {
    $stmt = $database->db->prepare("SELECT * FROM behind_the_scenes WHERE id = ? AND status='1'");
    $stmt->execute([$bts_id]);
    $entry = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($entry) {
        $bts_entries[] = $entry;
        $season_id = $entry['season'];
    }
}

// If no season from a bts_id, determine season from URL, default, or latest
if (!$season_id) {
    $season_id = filter_input(INPUT_GET, 'season_id', FILTER_VALIDATE_INT);
    if (!$season_id) {
        // Try to find the default season first
        $stmt = $database->db->query("SELECT id FROM seasons WHERE is_default = '1' AND status IN ('ACTIVE', '1') LIMIT 1");
        $season_id = $stmt->fetchColumn();
        if (!$season_id) {
            // Fallback to the latest season
            $stmt = $database->db->query("SELECT id FROM seasons WHERE status IN ('ACTIVE', '1') ORDER BY create_date DESC LIMIT 1");
            $season_id = $stmt->fetchColumn();
        }
    }
}

// Initialize navigation and season data
$first_season_id = null;
$last_season_id = null;
$previous_season_id = null;
$next_season_id = null;
$all_images = [];

if ($season_id) {
    // Fetch season details
    $stmt = $database->db->prepare("SELECT * FROM seasons WHERE id = ? AND status IN ('ACTIVE', '1')");
    $stmt->execute([$season_id]);
    $season = $stmt->fetch(PDO::FETCH_ASSOC);

    // If the season is valid, proceed to fetch entries and set up pagination
    if ($season) {
        // Fetch all BTS entries for the season if a specific one wasn't already loaded
        if (empty($bts_entries)) {
            $stmt = $database->db->prepare("SELECT * FROM behind_the_scenes WHERE season = ? AND status='1' ORDER BY day DESC");
            $stmt->execute([$season_id]);
            $bts_entries = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        // --- Performance Optimization: Solve N+1 Query Problem ---
        // 1. Get all BTS entry IDs
        $bts_ids = array_column($bts_entries, 'id');

        // 2. Fetch all images for these entries in a single query
        if (!empty($bts_ids)) {
            $placeholders = implode(',', array_fill(0, count($bts_ids), '?'));
            $stmt = $database->db->prepare("SELECT * FROM behind_the_scenes_images WHERE bts IN ($placeholders)");
            $stmt->execute($bts_ids);
            $images = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // 3. Group images by their parent BTS ID for easy lookup
            foreach ($images as $image) {
                $all_images[$image['bts']][] = $image;
            }
        }

        // --- Pagination Logic (UI labels: Last=Newest, Current=Oldest) ---
        $current_season_date = $season['create_date'];

        // Get the newest season ID for the 'Last' button
        $stmt = $database->db->query("SELECT id FROM seasons WHERE status IN ('ACTIVE', '1') ORDER BY create_date DESC LIMIT 1");
        $first_season_id = $stmt->fetchColumn();

        // Get the oldest season ID for the 'Current' button
        $stmt = $database->db->query("SELECT id FROM seasons WHERE status IN ('ACTIVE', '1') ORDER BY create_date ASC LIMIT 1");
        $last_season_id = $stmt->fetchColumn();

        // Get the next newer season ID for the 'Next' button
        $stmt = $database->db->prepare("SELECT id FROM seasons WHERE status IN ('ACTIVE', '1') AND create_date > ? ORDER BY create_date ASC LIMIT 1");
        $stmt->execute([$current_season_date]);
        $next_season_id = $stmt->fetchColumn();

        // Get the next older season ID for the 'Previous' button
        $stmt = $database->db->prepare("SELECT id FROM seasons WHERE status IN ('ACTIVE', '1') AND create_date < ? ORDER BY create_date DESC LIMIT 1");
        $stmt->execute([$current_season_date]);
        $previous_season_id = $stmt->fetchColumn();
    } else {
        // If the requested season_id is invalid, clear it to prevent errors.
        $season_id = null;
    }
}


?>
<!DOCTYPE html>
<html dir="ltr" lang="en-US">
<head>
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
	<link rel="stylesheet" href="admin/assets/global/plugins/carousel-owl-carousel/owl-carousel/owl.carousel.css" type="text/css" />
	<link rel="stylesheet" href="admin/assets/global/plugins/carousel-owl-carousel/owl-carousel/owl.theme.css" type="text/css" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<!--[if lt IE 9]>
		<script src="http://css3-mediaqueries-js.googlecode.com/svn/trunk/css3-mediaqueries.js"></script>
	<![endif]-->

	<!-- External JavaScripts
	============================================= -->
	<script type="text/javascript" src="js/jquery.js"></script>
	<script type="text/javascript" src="js/plugins.js"></script>
	<script type="text/javascript" src="admin/assets/global/plugins/carousel-owl-carousel/owl-carousel/owl.carousel.min.js"></script>

<!-- Document Title
	============================================= -->
<title>Behind the Scenes | My First Movie</title>

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
              <h3><?php echo isset($season['title']) ? htmlspecialchars($season['title'], ENT_QUOTES, 'UTF-8') : 'Behind the Scenes'; ?></h3>
            </div>
          </div>
          <div class="col-md-5">
                        <?php if ($season_id != $first_season_id): ?>
                <a class="button button-rounded button-reveal button-small button-border tright pull-right" href="behind-the-scenes.php?season_id=<?php echo $first_season_id; ?>"><i class="icon-forward"></i><span>Last</span></a>
            <?php endif; ?>

            <?php if ($next_season_id): ?>
                <a class="button button-rounded button-reveal button-small button-border tright pull-right" href="behind-the-scenes.php?season_id=<?php echo $next_season_id; ?>"><i class="icon-forward"></i><span>Next</span></a>
            <?php endif; ?>
            
            <?php if ($previous_season_id): ?>
                <a class="button button-rounded button-reveal button-small button-border tright pull-right" href="behind-the-scenes.php?season_id=<?php echo $previous_season_id; ?>"><i class="icon-backward"></i><span>Previous</span></a>
            <?php endif; ?>

            <?php if ($season_id != $last_season_id): ?>
                <a class="button button-rounded button-reveal button-small button-border tright pull-right" href="behind-the-scenes.php?season_id=<?php echo $last_season_id; ?>"><i class="icon-backward"></i><span>Current</span></a>
            <?php endif; ?>
          </div>
          <div class="clearfix"></div>
          <div class="accordion accordion-bg clearfix">
          <?php 
            foreach ($bts_entries as $bts) {
                $bts_id = $bts['id'];
		  ?>
            <div class="acctitle"><i class="acc-closed icon-ok-circle"></i><i class="acc-open icon-remove-circle"></i>Week <?php echo htmlspecialchars((string)$bts['day'], ENT_QUOTES, 'UTF-8'); ?></div>
            <div class="acc_content clearfix">
              <div class="row">
                <div class="col-md-2">
                  <div class="scenes-day green"> <span>Week <i><?php echo htmlspecialchars((string)$bts['day'], ENT_QUOTES, 'UTF-8'); ?></i></span> </div>
                </div>
                <div class="col-md-6">
                  <div style="margin-top: 10px;" class="heading-block">
                    <h4><?php echo htmlspecialchars($bts['title'], ENT_QUOTES, 'UTF-8'); ?></h4>
                  </div>
                  <?php echo $bts['display_note']; // Assuming this contains safe HTML. If not, it needs sanitizing. ?>
                </div>
                <div class="col-md-4"> 
                    <?php 
                    // Check if it's an S3 URL or local path
                    $btsScreenshot = $bts['screenshot'];
                    if (strpos($btsScreenshot, 'http') === 0) {
                        // S3 URL or full URL
                        $imageUrl = $btsScreenshot;
                    } else {
                        // Local path - construct proper URL using correct base path
                        $imageUrl = $correct_base_path . "/admin/uploads/bts/" . $btsScreenshot;
                    }
                    ?>
                    <a href="videos/<?php echo htmlspecialchars($bts['video_url'], ENT_QUOTES, 'UTF-8'); ?>" data-lightbox="iframe" class="">
                        <img src="<?php echo htmlspecialchars($imageUrl); ?>" class="img-responsive" alt="<?php echo htmlspecialchars($bts['title'], ENT_QUOTES, 'UTF-8'); ?>" style="width: 430px; height: 300px; object-fit: cover;"
                             onerror="this.src='<?php echo $correct_base_path; ?>/images/author/default.jpg';" />
                        <div class="overlay">
                            <div class="overlay-wrap"><i class="icon-youtube-play"></i></div>
                        </div>
                    </a> 
                </div>
              </div>
              <div class="clearfix"></div>
              <div class="divider"><i class="icon-circle"></i></div>
              <div class="owl-carousel portfolio-carousel">
                <?php if (!empty($bts['video_url'])): ?>
                <div class="oc-item">
                  <div class="iportfolio">
                    <div class="portfolio-image">
                      <a href="videos/<?php echo htmlspecialchars($bts['video_url'], ENT_QUOTES, 'UTF-8'); ?>" data-lightbox="iframe">
                        <?php 
                        // Check if it's an S3 URL or local path
                        $btsScreenshot = $bts['screenshot'];
                        if (strpos($btsScreenshot, 'http') === 0) {
                            // S3 URL or full URL
                            $imageUrl = $btsScreenshot;
                        } else {
                            // Local path - construct proper URL using correct base path
                            $imageUrl = $correct_base_path . "/admin/uploads/bts/" . $btsScreenshot;
                        }
                        ?>
                        <img src="<?php echo htmlspecialchars($imageUrl); ?>" alt="<?php echo htmlspecialchars($bts['title'], ENT_QUOTES, 'UTF-8'); ?>" style="width: 300px; height: 250px; object-fit: cover;"
                             onerror="this.src='<?php echo $correct_base_path; ?>/images/author/default.jpg';" />
                        <div class="overlay">
                          <div class="overlay-wrap"><i class="icon-youtube-play"></i></div>
                        </div>
                      </a>
                    </div>
                  </div>
                </div>
                <?php endif; ?>
                
              	<?php
                    // Use the pre-fetched images for the current BTS entry
                    $current_images = $all_images[$bts['id']] ?? [];
                    foreach($current_images as $image) {
				?>
                <div class="oc-item">
                  <div class="iportfolio">
                    <div class="portfolio-image"> 
                      <?php 
                      // Check if it's an S3 URL or local path
                      $galleryImage = $image['image'];
                      $galleryThumb = $image['image_thumb'];
                      
                      if (strpos($galleryImage, 'http') === 0) {
                          // S3 URL or full URL
                          $imageUrl = $galleryImage;
                          $thumbUrl = $galleryThumb;
                      } else {
                          // Local path - construct proper URL using correct base path
                          $imageUrl = $correct_base_path . "/admin/uploads/bts/" . $galleryImage;
                          $thumbUrl = $correct_base_path . "/admin/uploads/bts/" . $galleryThumb;
                      }
                      ?>
                      <a href="<?php echo htmlspecialchars($imageUrl); ?>" data-lightbox="image"> 
                        <img src="<?php echo htmlspecialchars($thumbUrl); ?>" style="width: 300px; height: 250px; object-fit: cover; padding: 10px;"
                             onerror="this.src='<?php echo $correct_base_path; ?>/images/author/default.jpg';" />
                        <div class="overlay">
                          <div class="overlay-wrap"><i class="icon-line-plus"></i></div>
                        </div>
                      </a> 
                    </div>
                  </div>
                </div>
                <?php 
					}
				?>
              </div>
            </div>
          <?php 
				}
		  ?>
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

<?php include('inc/before_body_close.php'); ?>
</body>
</html>