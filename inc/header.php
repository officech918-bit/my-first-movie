<?php
$path = '';
if (strpos($_SERVER['REQUEST_URI'], '/members/') !== false) {
    $path = '../';
}
?>
<header id="header" class="transparent-header full-header" data-sticky-class="not-dark">
    <div id="header-wrap">
      <div class="container clearfix">
        <div id="primary-menu-trigger"><i class="icon-reorder"></i></div>
        
        <!-- Logo
					============================================= -->
        <div id="logo">
            <a href="index.php" class="standard-logo" data-dark-logo="images/logo-dark.png"><img src="images/logo.png" alt="My First Movie"></a>
            <a href="index.php" class="retina-logo" data-dark-logo="images/logo-dark@2x.png"><img src="images/logo@2x.png" alt="My First Movie"></a>
        </div>
        <!-- #logo end --> 
        
        <!-- Primary Navigation
					============================================= -->
        <nav id="primary-menu" class="dark">
          <?php
            // Use parse_url to be resilient to query strings and future URL rewriting
            $current_page = basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
          ?>
          <ul>
            <li <?= ($current_page == 'index.php') ? 'class="current"' : '' ?>><a href="index.php">
              <div>Home</div>
              </a></li>
            <li <?= ($current_page == 'about-mfm.php') ? 'class="current"' : '' ?>><a href="about-mfm.php">
              <div>About MFM</div>
              </a></li>
            <li <?= ($current_page == 'how-it-works.php') ? 'class="current"' : '' ?>><a href="how-it-works.php">
              <div>How it works</div>
              </a></li>
            <li <?= ($current_page == 'call-for-entry.php') ? 'class="current"' : '' ?>><a href="call-for-entry.php">
              <div id="pulse">Call for Entry</div>
              </a></li>
            <li <?= ($current_page == 'behind-the-scenes.php') ? 'class="current"' : '' ?>><a href="behind-the-scenes.php">
              <div>Behind the scenes</div>
              </a></li>
            <li <?= ($current_page == 'selecteds.php') ? 'class="current"' : '' ?>><a href="selecteds.php">
              <div>Selecteds</div>
              </a></li>
            
            <!-- Mobile Login Menu Item -->
            <li class="mobile-login-menu d-none d-md-none d-lg-none d-xl-none">
              <a href="#"><div><i class="icon-user"></i> Account</div></a>
              <ul>
                <?php if ($user->check_session() && $user instanceof web_user): ?>
                  <li><a href="<?= htmlspecialchars($path) ?>members/dashboard.php">
                    <div><i class="icon-home"></i> My Dashboard</div>
                  </a></li>
                  <li><a href="<?= htmlspecialchars($path) ?>members/my-enrollments.php">
                    <div><i class="icon-list"></i> My Enrollments</div>
                  </a></li>
                  <!-- <li><a href="<?= htmlspecialchars($path) ?>members/profile.php">
                    <div><i class="icon-user"></i> Profile</div>
                  </a></li> -->
                  <li><a href="<?= htmlspecialchars($path) ?>members/logout.php">
                    <div><i class="icon-sign-out"></i> Logout</div>
                  </a></li>
                <?php else: ?>
                  <li><a href="<?= htmlspecialchars($path) ?>members/index.php">
                    <div><i class="icon-sign-in"></i> Login</div>
                  </a></li>
                  <li><a href="<?= htmlspecialchars($path) ?>members/register.php">
                    <div><i class="icon-plus"></i> Register</div>
                  </a></li>
                <?php endif; ?>
              </ul>
            </li>
        
          </ul>
          
          <!-- Top
						============================================= -->
          <!-- <div id="top-cart">
            <a href="#" id="top-cart-trigger"><i class="icon-user"></i></a>
            <div class="top-cart-content">
              <div class="top-cart-action clearfix">
                <?php if ($user->check_session() && $user instanceof web_user): ?>
                  <label style="margin-top: 10px;" class="clearfix">
                    <a style="float: left; color: rgb(255, 255, 255); width: 100%; font-size: 13px;" href="<?= htmlspecialchars($path) ?>members/dashboard.php">
                      Hi! <?= htmlspecialchars($user->get('first_name')) ?> | My Account
                    </a>
                  </label>
                  <a href="<?= htmlspecialchars($path) ?>members/logout.php">Logout</a>
                <?php else: ?>
                  <form id="top-login" action="<?= htmlspecialchars($path) ?>members/index.php" role="form" method="post">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                    <div class="input-group" id="top-login-username">
                      <span class="input-group-addon"><i class="icon-user"></i></span>
                      <input type="email" name="email" class="form-control" placeholder="Email address" required>
                    </div>
                    <div class="input-group" id="top-login-password">
                      <span class="input-group-addon"><i class="icon-key"></i></span>
                      <input type="password" name="user_pass" class="form-control" placeholder="Password" required>
                    </div>
                    <input class="btn btn-danger btn-block" type="submit" name="login" value="Sign in">
                    <label style="margin-top: 10px;" class="clearfix">
                      Not yet Registered! <a style="float: left; color: rgb(255, 255, 255); width: 35%; font-size: 13px;" href="members/register.php">Click Here</a>
                    </label>
                  </form>
                <?php endif; ?>
              </div>
            </div>
          </div> -->
        </nav>
        <!-- #primary-menu end --> 
        
      </div>
    </div>
  </header>