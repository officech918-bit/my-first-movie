<!-- Google Tag Manager -->
<noscript><iframe src="//www.googletagmanager.com/ns.html?id=GTM-M2NXPG"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'//www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-M2NXPG');</script>
<!-- End Google Tag Manager -->

<?php
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

// Get members path dynamically for CSS/JS loading
$scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$requestUri = $_SERVER['REQUEST_URI'];

// Extract the actual path from the current request
$uriParts = explode('/', trim($requestUri, '/'));
$membersIndex = array_search('members', $uriParts);

if ($membersIndex !== false) {
    $members_path = $scheme . '://' . $host . '/' . implode('/', array_slice($uriParts, 0, $membersIndex + 1)) . '/';
} else {
    $members_path = $scheme . '://' . $host . '/members/'; // fallback
}
?>

<!-- BEGIN TOP BAR -->
    <div class="pre-header">
        <div class="container">
            <div class="row">
                <!-- BEGIN TOP BAR MENU -->
                <div class="col-md-12 col-sm-12 additional-nav">
                    <ul class="list-unstyled list-inline pull-right" id="header_login_sec">
                    	<?php if($user->check_session()) echo '<li><a href="'.$members_path.'dashboard.php">Hi! '.$user->get('first_name').' | My Account</a></li>
						<li><a href="'.$members_path.'logout.php">Logout</a></li>';  
							else echo '<li><a href="'.$members_path.'">Log In</a></li>
                        <li><a href="'.$members_path.'register.php">Registration</a></li>';
			?>
                        
                    </ul>
                </div>
                <!-- END TOP BAR MENU -->
            </div>
        </div>        
    </div>
    <!-- END TOP BAR -->
    <!-- BEGIN HEADER -->
    <div class="header">
      <div class="container">
        <a class="site-logo" href="<?php echo $correct_base_path; ?>/index.php"><img src="<?php echo $members_path; ?>assets/frontend/layout/img/logos/logo.png" alt="My First Movie"></a>

        <a href="javascript:void(0);" class="mobi-toggler"><i class="fa fa-bars"></i></a>
        
         <!-- BEGIN CART -->
        <div class="top-cart-block">
          <div class="top-cart-info" id="top-cart-info">
            <a href="<?php echo $correct_base_path; ?>/index.php" class="top-cart-info-count" style="">Back to Website</a>
          </div>
          <i class="fa fa-home"></i>
        </div>
        <!--END CART -->

          </div>
         
        </div>
        <!--END CART -->
        <!-- BEGIN NAVIGATION -->
          <div class="header-navigation pull-right font-transform-inherit">
          
        </div>        
        <!-- END NAVIGATION -->