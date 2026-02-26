<?php

/**
 * Member Login Page.
 *
 * Handles user authentication. If the user is already logged in, it redirects
 * them to the dashboard. Otherwise, it displays a login form and processes
 * the credentials upon submission.
 *
 * @package MFM
 * @subpackage Members
 */

declare(strict_types=1);

require_once __DIR__ . '/inc/requires.php';

// Objects are now created in requires.php
// CSRF token is now generated in requires.php

$login_error = '';
$user = New VISITOR;

// If user is already logged in, re direct to their dashboard.
if ($user->check_session()) {
    header("Location: dashboard.php");
    exit();
}

// Process login form submission.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    // Manually verify CSRF token for better security and clarity.
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $login_error = 'A security error occurred. Please try again.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['user_pass'] ?? '';

        if (empty($email) || empty($password)) {
            $login_error = 'Email and password are required.';
        } else {
            $user_data = $user->checkPassword($email, $password);

            if ($user_data) {
                // Password is correct, regenerate session ID and create session.
                session_regenerate_id(true);
                $user->create_session($user_data['uid'], $user_data['email'], $user_data['user_type']);
                header("Location: dashboard.php");
                exit();
            } else {
                $login_error = 'Invalid email or password!';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html dir="ltr" lang="en-US">
<head>
<title>My First Movie | Login</title>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<link rel="shortcut icon" href="favicon.ico">

<!-- Fonts START -->
<link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700|PT+Sans+Narrow|Source+Sans+Pro:200,300,400,600,700,900&amp;subset=all" rel="stylesheet" type="text/css">
<!-- Fonts END -->

<!-- Global styles START -->
<link href="assets/global/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet">
<link href="assets/global/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet">
<!-- Global styles END -->

<!-- Page level plugin styles START -->
<link href="assets/global/plugins/uniform/css/uniform.default.css" rel="stylesheet" type="text/css">
<!-- Page level plugin styles END -->

<!-- Theme styles START -->
<link href="assets/global/css/components.css" rel="stylesheet">
<link href="assets/frontend/layout/css/style.css" rel="stylesheet">
<link href="assets/frontend/layout/css/style-responsive.css" rel="stylesheet">
<link href="assets/frontend/layout/css/themes/red.css" rel="stylesheet" id="style-color">
<link href="assets/frontend/layout/css/custom.css" rel="stylesheet">
<!-- Theme styles END -->
</head>
<body class="stretched">
    <!-- Document Wrapper
    ============================================= -->
    <div id="wrapper" class="clearfix">
      <!-- Header
        ============================================= -->
      <?php require_once __DIR__ . '/inc/header.php'; ?>
      <!-- #header end -->

      <!-- Content
        ============================================= -->
      <section id="content">
        <div class="main">
      <div class="container">
        <div class="row">
          <div class="col-md-9">
            <div class="content-form-page">
              <div class="row">
                <div class="col-md-7 col-sm-7">
                  <form class="form-horizontal" role="form"  action="" method="post" id="login-form" name="login-form">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                    <fieldset>
                      <legend>Log In</legend>
                      <?php
                        if (isset($_SESSION['reset_msg'])) {
                          $is_error = $_SESSION['is_error'] ?? false;
                          $alert_class = $is_error ? 'alert-danger' : 'alert-success';
                          echo '<div class="alert ' . htmlspecialchars($alert_class) . '">' . htmlspecialchars($_SESSION['reset_msg']) . '</div>';
                          unset($_SESSION['reset_msg'], $_SESSION['is_error']);
                        }
                      ?>
                      <?php if (!empty($login_error)) : ?>
                    <div class="alert alert-danger">
                      <?php echo htmlspecialchars($login_error); ?>
                    </div>
                    <?php endif; ?>
                      <div class="form-group">
                        <label for="email" class="col-lg-4 control-label">Email <span class="require">*</span></label>
                        <div class="col-lg-8">
                          <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                      </div>
                      <div class="form-group">
                        <label for="password" class="col-lg-4 control-label">Password <span class="require">*</span></label>
                        <div class="col-lg-8">
                          <input type="password" class="form-control" id="user_pass" name="user_pass" required>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-lg-8 col-md-offset-4 padding-left-0">
                          <a href="forgotton-password.php">Forgot password?</a>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-lg-8 col-md-offset-4 padding-left-0 padding-top-20">
                          <button type="submit" class="btn btn-primary" id="login-form-submit" name="login" value="login">Login</button>
                          <button type="button" class="btn btn-default" onclick="window.location.href='index.php';">Cancel</button>
                        </div>
                      </div>
                    </fieldset>
                  </form>
                </div>
                <div class="col-md-4 col-sm-4 pull-right">
                  <div class="form-info">
                    <h2><em>Important</em> Information</h2>
                    <p>Login to access the main user dashboard and manage your account.</p>

                    <button type="button" class="btn btn-default">More details</button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
      </section>
        <!-- #content end -->
        <!-- Footer
        ============================================= -->
      <?php require_once __DIR__ . '/inc/footer.php'; ?>
        <!-- #footer end -->
    </div>
    <!-- #wrapper end -->
    <!-- Go To Top
    ============================================= -->
    <div id="gotoTop" class="icon-angle-up"></div>
</body>
</html>