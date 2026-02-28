<?php

/**
 * Displays the history of winners for all seasons.
 *
 * This page retrieves and shows a list of completed seasons and the
 * winners associated with each season. It uses a tabbed interface
 * to separate winners by season.
 *
 * @package MFM
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

// Optimized data fetching to avoid N+1 queries
$stmt = $database->db->prepare("SELECT id, title, display_note, status FROM seasons ORDER BY short_order ASC");
$stmt->execute();
$allSeasons = $stmt->fetchAll(PDO::FETCH_ASSOC);

$seasonIds = !empty($allSeasons) ? array_column($allSeasons, 'id') : [];
$winnersByCatBySeason = [];
$categoriesBySeason = [];
$seasonsWithWinners = [];

if (!empty($seasonIds)) {
    $placeholders = str_repeat('?,', count($seasonIds) - 1) . '?';

    // Fetch all winners for all relevant seasons in one query
    $stmt = $database->db->prepare(
        "SELECT w.id, w.user_id, w.category_id, w.image, w.description, w.rank_position, w.title, u.first_name, u.last_name, c.title AS category_title, w.season_id
         FROM winners w
         JOIN web_users u ON u.uid = w.user_id
         JOIN categories c ON c.id = w.category_id
         WHERE w.season_id IN ($placeholders)
         ORDER BY w.id DESC"
    );
    $stmt->execute($seasonIds);
    $allWinners = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($allWinners as $w) {
        $winnersByCatBySeason[$w['season_id']][$w['category_id']][] = $w;
        $seasonsWithWinners[$w['season_id']] = true;
    }

    // Fetch all categories for all relevant seasons in one query
    $stmt = $database->db->prepare(
        "SELECT sc.season_id, c.id, c.title
         FROM season_categories sc
         JOIN categories c ON c.id=sc.cat_id
         WHERE sc.season_id IN ($placeholders) AND c.status=1
         ORDER BY c.short_order ASC"
    );
    $stmt->execute($seasonIds);
    $allCategories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($allCategories as $cat) {
        $categoriesBySeason[$cat['season_id']][] = $cat;
    }
}

// Filter seasons to be displayed: only those that are completed or have winners
$completedSeasons = array_filter($allSeasons, function($s) use ($seasonsWithWinners) {
    return $s['status'] === 'COMPLETED' || !empty($seasonsWithWinners[$s['id']]);
});

?>
<!DOCTYPE html>
<html dir="ltr" lang="en-US">
<head>
<title>Winners History</title>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="css/bootstrap.css" type="text/css" />
<link rel="stylesheet" href="style.css" type="text/css" />
<link rel="stylesheet" href="css/dark.css" type="text/css" />
<link rel="stylesheet" href="css/font-icons.css" type="text/css" />
<link rel="stylesheet" href="css/animate.css" type="text/css" />
<link rel="stylesheet" href="css/magnific-popup.css" type="text/css" />
<link rel="stylesheet" href="css/responsive.css" type="text/css" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<?php include('inc/before_head_close.php'); ?>
<script type="text/javascript" src="js/jquery.js" defer></script>
<script type="text/javascript" src="js/plugins.js" defer></script>
</head>
<body class="stretched">
<?php include('inc/after_body_start.php'); ?>
<div id="" class="clearfix">
  <?php include('inc/header.php'); ?>
  <section id="page-title" class="page-title-mini">
    <div class="container clearfix">
      <h1>Winners History</h1>
      <span>Browse winners from completed seasons</span>
      <ol class="breadcrumb">
        <li><a href="index.php">Home</a></li>
        <li class="active">Winners History</li>
      </ol>
    </div>
  </section>
  <section id="content">
    <div class="content-wrap">
      <div class="container clearfix">
        <?php if (empty($completedSeasons)): ?>
          <div class="alert alert-info">No winners history found.</div>
        <?php else: ?>
          <?php foreach ($completedSeasons as $season): ?>
            <div class="col_full" style="margin-bottom:30px;">
              <div class="heading-block">
                <h3><?= e($season['title']) ?></h3>
                <?php if (!empty($season['display_note'])): ?><span><?= e($season['display_note']) ?></span><?php endif; ?>
              </div>
              <?php
                $seasonCategories = $categoriesBySeason[$season['id']] ?? [];
                $seasonWinnersByCat = $winnersByCatBySeason[$season['id']] ?? [];
              ?>
              <?php foreach ($seasonCategories as $cat): ?>
                <div class="col-md-12" style="margin-bottom:25px;">
                  <h4><?= e($cat['title']) ?></h4>
                  <div class="row">
                    <?php $winnersInCat = $seasonWinnersByCat[$cat['id']] ?? []; ?>
                    <?php if (empty($winnersInCat)): ?>
                      <div class="col-md-12"><em>No winners yet in this category.</em></div>
                    <?php else: ?>
                      <?php foreach ($winnersInCat as $win): ?>
                        <?php $img = !empty($win['image']) ? getImageUrl($win['image']) : ''; ?>
                        <div class="col-md-4" style="margin-bottom:15px;">
                          <div class="text-center">
                            <?php if (!empty($img)): ?>
                              <?= lazy_image($img, e($win['title'] ?? $win['category_title'] ?? ''), 'image_fade', ['style' => 'width:160px;height:160px;border-radius:50%;object-fit:cover;']) ?>
                            <?php endif; ?>
                            <div style="margin-top:8px;">
                              <div>
                                <?php $rp = (int)($win['rank_position'] ?? 0); ?>
                                <?php if ($rp === 1): ?><span class="label label-warning">1 Gold</span>
                                <?php elseif ($rp === 2): ?><span class="label label-info">2 Silver</span>
                                <?php elseif ($rp === 3): ?><span class="label label-default">3 Bronze</span>
                                <?php endif; ?>
                              </div>
                              <?php if (!empty($win['title'])): ?><div><strong><?= e($win['title']) ?></strong></div><?php endif; ?>
                              <div><span class="label label-default">UID: <?= (int)$win['user_id'] ?></span></div>
                              <?php if (!empty($win['description'])): ?><div><?= e($win['description']) ?></div><?php endif; ?>
                            </div>
                          </div>
                        </div>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </section>
  <?php include('inc/footer.php'); ?>
</div>
</body>
</html>