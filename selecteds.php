<?php

/**
 * Selecteds (Winners and Categories) Page
 *
 * This page displays the winners and categories for all seasons.
 * It features a tabbed navigation to switch between different seasons
 * and shows active categories for enrollment or past winners.
 *
 * @package     MyFirstMovie
 * @subpackage  Pages
 * @since       1.0.0
 */

declare(strict_types=1);

session_start();
date_default_timezone_set('Asia/Kolkata');

//get class files
require_once 'inc/requires.php';

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

//get class files
require_once 'inc/requires.php';

$database = new MySQLDB();
$user = new visitor();

if ($user->check_session()) {
    $user = new web_user();
}

$sitename = $user->get_sitename();
$sub_location = $user->get_sub_location();

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
function getImageUrl(string $imagePath): string {
    if (empty($imagePath)) {
        return 'https://via.placeholder.com/400x300?text=No+Image+Available';
    }
    
    global $s3Uploader;
    if ($s3Uploader->isS3Enabled()) {
        // In production, assume S3 URLs are stored
        return $imagePath;
    } else {
        // In local development, convert relative paths to full URLs
        if (strpos($imagePath, 'http') === 0) {
            return $imagePath; // Already a full URL
        }
        global $correct_base_path;
        
        // Handle winner images specifically - they're stored in admin/uploads/winners/
        if (strpos($imagePath, 'uploads/winners/') === 0) {
            return $correct_base_path . "/admin/" . $imagePath;
        }
        
        return $correct_base_path . "/" . $imagePath; // Convert to relative path
    }
}

//create objects

?>
<!DOCTYPE html>
<html dir="ltr" lang="en-US">
<head>
    <title>Selecteds | My First Movie</title>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <link href="https://fonts.googleapis.com/css?family=Lato:300,400,600,700|Raleway:300,400,500,600,700" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="css/bootstrap.css" type="text/css" />
    <link rel="stylesheet" href="style.css" type="text/css" />
    <link rel="stylesheet" href="css/dark.css" type="text/css" />
    <link rel="stylesheet" href="css/font-icons.css" type="text/css" />
    <link rel="stylesheet" href="css/animate.css" type="text/css" />
    <link rel="stylesheet" href="css/responsive.css" type="text/css" />

    <style>
        /* Modern UI Enhancements */
        :root {
            --gold: #FFD700;
            --silver: #C0C0C0;
            --bronze: #CD7F32;
            --primary: #2c3e50;
        }

        .season-tab-nav { border-bottom: 2px solid #eee; margin-bottom: 30px; }
        .nav-tabs > li > a { border: none; font-weight: 600; color: #777; transition: all 0.3s; }
        .nav-tabs > li.active > a, .nav-tabs > li.active > a:focus { border: none; border-bottom: 3px solid var(--primary); color: var(--primary); background: transparent; }

        .winner-card {
            background: #fff;
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            transition: transform 0.3s ease;
            height: 100%;
            margin-bottom: 20px;
            border: 1px solid #f1f1f1;
        }
        .winner-card:hover { transform: translateY(-10px); }
        
        .winner-img {
            width: 140px;
            height: 140px;
            border-radius: 50%;
            object-fit: cover;
            margin: 0 auto 15px;
            border: 4px solid #fff;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .cat-card {
            background: #f9f9f9;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 30px;
            transition: all 0.3s;
        }
        .cat-card:hover { box-shadow: 0 15px 35px rgba(0,0,0,0.1); }
        .cat-card img { height: 200px; object-fit:; width: 100%; }
        .cat-card .p-3 { padding: 15px; }

        .medal-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 10px;
        }
        .medal-1 { background: var(--gold); color: #000; }
        .medal-2 { background: var(--silver); color: #000; }
        .medal-3 { background: var(--bronze); color: #fff; }

        .section-title {
            position: relative;
            padding-bottom: 15px;
            margin-bottom: 30px;
            font-weight: 700;
        }
        .section-title::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 50px;
            height: 3px;
            background: var(--primary);
        }
        .grayscale { filter: grayscale(100%); }
    </style>

    <?php include('inc/before_head_close.php'); ?>
</head>

<body class="stretched">
<?php include('inc/after_body_start.php'); ?>

<div id="wrapper" class="clearfix"> 
    <?php include('inc/header.php'); ?>
  
    <section id="page-title" class="page-title-mini">
        <div class="container clearfix">
            <h1>Selecteds</h1>
            <span>Meet our winners and explore current categories</span>
            <ol class="breadcrumb">
                <li><a href="index.php">Home</a></li>
                <li class="active">Selecteds</li>
            </ol>
        </div>
    </section>

    <section id="content">
        <div class="content-wrap">
            <div class="container clearfix">
                
                <?php
                // Fetch Seasons Logic
                $stmt = $database->db->prepare("SELECT id, title, display_note, short_order, status, IFNULL(is_default, 0) AS is_default FROM seasons ORDER BY short_order ASC");
                $stmt->execute();
                $seasons = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $currentSeason = null;
                if (!empty($seasons)) {
                    foreach ($seasons as $s) {
                        if ((int)$s['is_default'] === 1) { $currentSeason = $s; break; }
                    }
                    if (!$currentSeason) $currentSeason = $seasons[0];
                }
                ?>

                <?php if (!empty($seasons)): ?>
                <ul class="nav nav-tabs season-tab-nav" role="tablist">
                    <?php foreach ($seasons as $season): 
                        $isActiveSeason = ($currentSeason && $season['id'] == $currentSeason['id']);
                        $rawStatus = $season['status'];
                        $status = is_numeric($rawStatus)
                            ? (($rawStatus==1)?'ACTIVE':(($rawStatus==2)?'COMPLETED':'UPCOMING'))
                            : strtoupper($rawStatus);
                        $labelClass = ($status == 'ACTIVE') ? 'label-success' : (($status == 'UPCOMING') ? 'label-default' : 'label-default');
                    ?>
                        <li role="presentation" class="<?php echo $isActiveSeason ? 'active' : ''; ?>">
                            <a href="#season-<?php echo $season['id']; ?>" aria-controls="season-<?php echo $season['id']; ?>" role="tab" data-toggle="tab">
                                <?php echo htmlspecialchars($season['title']); ?>
                                <span class="label <?php echo $labelClass; ?>" style="margin-left:5px; font-size:9px;"><?php echo $status; ?></span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <div class="tab-content">
                    <?php foreach ($seasons as $season):
                        $isActivePane = ($currentSeason && $season['id'] == $currentSeason['id']);
                        $rawStatusPane = $season['status'];
                        $statusPane = is_numeric($rawStatusPane)
                            ? (($rawStatusPane==1)?'ACTIVE':(($rawStatusPane==2)?'COMPLETED':'UPCOMING'))
                            : strtoupper($rawStatusPane);
                        $isUpcoming = ($statusPane === 'UPCOMING');
                        $isActiveStatus = ($statusPane === 'ACTIVE');
                        $isCompleted = ($statusPane === 'COMPLETED');
                    ?>
                    <div role="tabpanel" class="tab-pane <?php echo $isActivePane ? 'active' : ''; ?>" id="season-<?php echo e((string) $season['id']); ?>">

                        <?php if ($isActiveStatus): ?>
                        <div class="heading-block border-bottom-0">
                            <h3 class="section-title">Open Categories</h3>
                        </div>
                        <div class="row">
                            <?php
                            $stmt = $database->db->prepare("SELECT c.* FROM season_categories sc JOIN categories c ON c.id = sc.cat_id WHERE sc.season_id = ? AND c.status = 1 ORDER BY c.short_order ASC");
                            $stmt->execute([$season['id']]);
                            $activeCategories = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($activeCategories as $cat):
                                $catImg = !empty($cat['cat_img_thumb']) ? getImageUrl($cat['cat_img_thumb']) : '';
                            ?>
                            <div class="col-sm-6 col-md-4 col-lg-3">
                                <div class="cat-card">
                                    <?php if ($catImg): ?>
                                        <?= lazy_image($catImg, e($cat['title'])) ?>
                                    <?php endif; ?>
                                    <div class="p-3">
                                        <h4><?= e($cat['title']) ?></h4>
                                        <a href="call-for-entry.php" class="button button-mini button-circle button-dark">Enroll Now</a>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php elseif ($isUpcoming): ?>
                            <div class="style-msg alertmsg"><div class="sb-msg"><i class="icon-warning-sign"></i><strong>Coming Soon!</strong> This season hasn't started yet. Stay tuned.</div></div>
                        <?php elseif ($isCompleted): ?>
                            <div class="style-msg alertmsg"><div class="sb-msg"><i class="icon-warning-sign"></i><strong>Season Completed.</strong> Enrollment is closed.</div></div>
                            <div class="heading-block border-bottom-0">
                                <h3 class="section-title">Categories</h3>
                            </div>
                            <div class="row">
                                <?php
                                $stmt = $database->db->prepare("SELECT c.* FROM season_categories sc JOIN categories c ON c.id = sc.cat_id WHERE sc.season_id = ? AND c.status = 1 ORDER BY c.short_order ASC");
                                $stmt->execute([$season['id']]);
                                $completedCategories = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                foreach ($completedCategories as $cat):
                                    $catImg = !empty($cat['cat_img_thumb']) ? getImageUrl($cat['cat_img_thumb']) : '';
                                ?>
                                <div class="col-sm-6 col-md-4 col-lg-3">
                                    <div class="cat-card">
                                        <?php if ($catImg): ?>
                                            <?= lazy_image($catImg, e($cat['title'])) ?>
                                        <?php endif; ?>
                                        <div class="p-3">
                                            <h4><?= e($cat['title']) ?></h4>
                                            <a href="call-for-entry.php" class="button button-mini button-circle button-dark">Enroll Now</a>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <div class="heading-block border-bottom-0" style="margin-top: 50px;">
                            <h3 class="section-title"><?= e($season['title']) ?> Winners</h3>
                        </div>

                        <div class="row">
                            <?php
                            $stmt = $database->db->prepare("SELECT w.*, c.title AS category_title FROM winners w JOIN categories c ON c.id = w.category_id WHERE w.season_id = ? ORDER BY w.rank_position ASC");
                            $stmt->execute([$season['id']]);
                            $winners = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            if (empty($winners)): ?>
                                <div class="col-12"><p class="alert alert-light">Winners for this season will be announced soon.</p></div>
                            <?php else:
                                foreach ($winners as $win):
                                    $rp = (int)$win['rank_position'];
                                    $medalClass = ($rp === 1) ? 'medal-1' : (($rp === 2) ? 'medal-2' : 'medal-3');
                                    $medalText = ($rp === 1) ? 'Gold Winner' : (($rp === 2) ? 'Silver Winner' : 'Bronze Winner');
                                ?>
                                <div class="col-sm-6 col-md-4">
                                    <div class="winner-card">
                                        <span class="medal-badge <?= $medalClass ?>"><?= $medalText ?></span>
                                        <?php if (!empty($win['image'])): ?>
                                            <?= lazy_image(getImageUrl($win['image']), e($win['title']), 'winner-img') ?>
                                        <?php else: ?>
                                            <?= lazy_image('images/author/default.jpg', e($win['title']), 'winner-img') ?>
                                        <?php endif; ?>
                                        <h4 class="mb-1"><?= e($win['title']) ?></h4>
                                        <p class="text-muted small">Category: <?= e($win['category_title']) ?></p>
                                        <hr>
                                        <p><?= e($win['description']) ?></p>
                                    </div>
                                </div>
                            <?php endforeach;
                            endif;
                            ?>
                        </div>

                        <?php if ($isActiveStatus): ?>
                        <div class="text-center mt-5">
                            <a href="winners-history.php" class="button button-border button-rounded button-large">View Hall of Fame</a>
                        </div>
                        <?php endif; ?>

                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

            </div>
        </div>
    </section>

    <?php include('inc/register-cta.php'); ?>
    <?php include('inc/footer.php'); ?>
</div>

<div id="gotoTop" class="icon-angle-up"></div>

<script type="text/javascript" src="js/jquery.js"></script>
<script type="text/javascript" src="js/plugins.js"></script>
<script type="text/javascript" src="js/functions.js"></script>

<?php include('inc/before_body_close.php'); ?>
</body>
</html>