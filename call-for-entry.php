<?php
/**
 * Call for Entry Page
 *
 * This page displays the categories for which entries can be submitted
 * and provides a registration form for new users.
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

// Initialize objects
$database = new MySQLDB();
$user = new visitor();

// Check if a user session already exists
if ($user->check_session()) {
    $user = new web_user();
}

$sitename = $user->get_sitename();
$sub_location = $user->get_sub_location();
$company_name = $user->get_company_name();

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

$path = rtrim($sitename . '/' . $sub_location, '/') . '/';
$direct_path = rtrim($_SERVER['DOCUMENT_ROOT'] . '/' . $sub_location, '/') . '/';

?>
<!DOCTYPE html>
<html dir="ltr" lang="en-US">
<head>
<title>Call for Entry |<?= htmlspecialchars($company_name, ENT_QUOTES, 'UTF-8') ?></title>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<meta name="author" content="SemiColonWeb" />

<link href="https://fonts.googleapis.com/css?family=Lato:300,400,400italic,600,700|Raleway:300,400,500,600,700|Crete+Round:400italic" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="css/bootstrap.css" type="text/css" />
<link rel="stylesheet" href="style.css" type="text/css" />
<link rel="stylesheet" href="css/dark.css" type="text/css" />
<link rel="stylesheet" href="css/font-icons.css" type="text/css" />
<link rel="stylesheet" href="css/animate.css" type="text/css" />
<link rel="stylesheet" href="css/magnific-popup.css" type="text/css" />
<link rel="stylesheet" href="css/responsive.css" type="text/css" />
<meta name="viewport" content="width=device-width, initial-scale=1" />


<style>
.help-block { color: #F00; }

/* Custom Image Sizing */
.category-img-container {
    width: 100%;
    height: 250px; /* Adjust this height to make images bigger or smaller */
    overflow: hidden;
    border-radius: 4px;
    background-color: #f5f5f5;
}

.category-img-container img {
    width: 100%;
    height: 100%;
    object-fit: contain; /* This keeps the aspect ratio while filling the box */
    display: block;
}
</style>

<?php include('inc/before_head_close.php'); ?>
</head>

<body class="stretched">
<?php include('inc/after_body_start.php'); ?>

<div id="wrapper" class="clearfix"> 
  
  <?php include('inc/header.php'); ?>
  
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

  <section id="content">
    <div class="content-wrap">
      <div class="container clearfix">
        <div class="col_full">
          <?php 
              $stmt = $database->db->prepare("SELECT * FROM categories WHERE status='1'");
              $stmt->execute();
              $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
              if(count($result) > 0) {
                foreach($result as $category) {
                    // Check if it's an S3 URL or local path
                    $categoryImg = $category['cat_img'];
                    if (empty($categoryImg)) {
                        $img_path = "https://via.placeholder.com/400x300?text=No+Image+Available";
                    } elseif (strpos($categoryImg, 'http') === 0) {
                        // S3 URL or full URL
                        $img_path = $categoryImg;
                    } else {
                        // Local path - check if already includes uploads/categories/
                        if (strpos($categoryImg, 'uploads/categories/') === 0) {
                            // Already includes the path, just prepend base path
                            $img_path = $correct_base_path . "/" . $categoryImg;
                        } else {
                            // Just the filename, prepend full path
                            $img_path = $correct_base_path . "/uploads/categories/" . $categoryImg;
                        }
                    }
                  ?>
          <div class="row mb-5">
            <div class="col-md-4">
                <div class="category-img-container">
                    <?= lazy_image($img_path, htmlspecialchars($category['title'], ENT_QUOTES, 'UTF-8'), 'img-responsive') ?>
                </div>
            </div>
            <div class="col-md-8">
              <div style="margin-top: 10px;" class="heading-block">
                <h4><?= htmlspecialchars($category['title'], ENT_QUOTES, 'UTF-8') ?></h4>
              </div>
              <div class="entry-content">
                <?php echo $category['display_note'] ?>
              </div>
              <div class="clear"></div>
              <a class="button button-border button-rounded button-small noleftmargin pull-right" href="members/register.php">Apply now!</a> 
            </div>
          </div>
          <div class="divider"><i class="icon-circle"></i></div>
          <?php 
                }
              }
            ?>
        </div>
      </div>

      <div style="padding-bottom:30px; padding-top:30px;" class="section nomargin ">
        <div class="container clearfix">
          <div class="row clearfix">
            <div class="col-lg-12">
              <div class="heading-block fancy-title nobottomborder title-bottom-border">
                <h4>Enroll here its <span>free to register</span>.</h4>
              </div>
              <?php if($user->check_session()) { ?>
                <div class="col-md-12">
                    <p style="font-size:16px;">You are already registered and logged in. Please proceed to your <a href="<?php echo $path; ?>members/dashboard.php">dashboard</a> to manage your enrollments.</p>
                </div>
              <?php } else { ?>
              <div class="col-md-8">
                <form class="form-horizontal col-md-offset-1 padding-left-0 nobottommargin" role="form" method="post" action="members/register.php" name="register" id="register">
                  <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                  <fieldset>
                    <div class="form-group">
                      <label for="first_name" class="col-lg-4 control-label">First Name <span class="require">*</span></label>
                      <div class="col-lg-8">
                        <input type="text" class="sm-form-control form-control validate[required,maxSize[30]]" id="first_name" name="first_name" value="">
                      </div>
                    </div>

                    <div class="form-group">
                      <label for="last_name" class="col-lg-4 control-label">Last Name <span class="require">*</span></label>
                      <div class="col-lg-8">
                        <input type="text" class="sm-form-control form-control validate[required,maxSize[30]]" id="last_name" name="last_name" value="">
                      </div>
                    </div>

                    <div class="form-group">
                      <label for="email" class="col-lg-4 control-label">Email <span class="require">*</span></label>
                      <div class="col-lg-8">
                        <input type="text" class="sm-form-control form-control validate[required,custom[email]]" id="email" name="email" value="">
                      </div>
                    </div>

                    <div class="form-group">
                        <label for="contact" class="col-lg-4 control-label">Mobile <span class="require">*</span></label>
                        <div class="col-lg-8">
                          <input type="text" class="form-control validate[required,custom[integer]]" id="contact" name="contact" value="">
                        </div>
                    </div>

                    <div class="form-group">
                      <label for="password1" class="col-lg-4 control-label">Password <span class="require">*</span></label>
                      <div class="col-lg-8">
                        <input type="password" class="sm-form-control form-control validate[required,minSize[6],maxSize[30]]" id="password1" name="password1">
                      </div>
                    </div>

                    <div class="form-group">
                      <label for="password2" class="col-lg-4 control-label">Confirm password <span class="require">*</span></label>
                      <div class="col-lg-8">
                        <input type="password" class="sm-form-control form-control validate[required,equals[password1]]" id="password2" name="password2">
                      </div>
                    </div>

                    <div class="form-group">
                      <label class="col-lg-4 control-label"></label>
                      <div class="col-lg-8">
                        <div class="g-recaptcha" data-sitekey="6LcHoEgUAAAAADPvvxBLBpMVNrElu5EFLEegSM7b"></div>
                      </div>
                    </div>

                    <div class="form-group">
                      <label class="col-lg-4 control-label"></label>
                      <div class="col-lg-8">
                        <label>
                          <input type="checkbox" class="validate[required]" name="i_agree" id="i_agree">
                          I Agree to <a href="<?php echo htmlspecialchars($path, ENT_QUOTES, 'UTF-8'); ?>terms-and-conditions" target="_blank">Terms &amp; Conditions</a> of <?= htmlspecialchars($company_name, ENT_QUOTES, 'UTF-8') ?></label>
                      </div>
                    </div>
                  </fieldset>
                  <div class="row">
                    <div class="col-lg-8 col-md-offset-4 padding-left-0">
                      <button type="submit" name="register" class="button button-3d nomargin">Create an account</button>
                    </div>
                  </div>
                </form>
              </div>
              <div class="col-md-4"> 
                <?= lazy_image('images/team/3.jpg', 'Team', 'img-responsive', ['style' => 'width:100%; height:auto; border-radius:4px;']) ?>
              </div>
              <?php } ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <?php include('inc/register-cta.php'); ?>
  <?php include('inc/footer.php'); ?>
  
</div>

<div id="gotoTop" class="icon-angle-up"></div>

<script type="text/javascript" src="js/jquery.js" nonce="<?= $nonce ?>"></script>
<script type="text/javascript" src="js/plugins.js" nonce="<?= $nonce ?>"></script>
<script type="text/javascript" src="js/functions.js" nonce="<?= $nonce ?>"></script> 
<script type="text/javascript" src="js/jquery.pulsate.js" nonce="<?= $nonce ?>"></script> 
<script nonce="<?= $nonce ?>">
    var jq = $.noConflict();
    jq(document).ready(function(){
      jq("#register").validationEngine();
      
      jq("#pulse").pulsate({color:"#09f"});
      // ... rest of your pulsate scripts
    });
</script>
<?php include('inc/before_body_close.php'); ?>
</body>
</html>