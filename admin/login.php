<?php
header("X-Frame-Options: SAMEORIGIN");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: strict-origin-when-cross-origin");
date_default_timezone_set('Asia/Kolkata');

// Rate Limiting Configuration
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_TIME', 300); // 5 minutes

// --- Helper function for failed login attempts ---
function handle_failed_login() {
    if (!isset($_SESSION['login_attempts'])) {
        $_SESSION['login_attempts'] = 1;
    } else {
        $_SESSION['login_attempts']++;
    }

    if ($_SESSION['login_attempts'] >= MAX_LOGIN_ATTEMPTS) {
        $_SESSION['login_attempts_time'] = time(); // Start lockout
    }
}

// --- Main Logic ---
$login_error = '';
$is_locked_out = false;

// 1. Check if the user is currently locked out
if (isset($_SESSION['login_attempts_time']) && (time() - $_SESSION['login_attempts_time'] < LOCKOUT_TIME)) {
    $is_locked_out = true;
    $remaining_time = LOCKOUT_TIME - (time() - $_SESSION['login_attempts_time']);
    $login_error = "Too many failed login attempts. Please try again in " . ceil($remaining_time / 60) . " minutes.";
} else {
    // If lockout has expired, clear the session variables
    if (isset($_SESSION['login_attempts_time'])) {
        unset($_SESSION['login_attempts']);
        unset($_SESSION['login_attempts_time']);
    }
}

include('inc/requires.php');
$database = new MySQLDB();
$user = new visitor();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// 2. Redirect if already logged in
if ($user->check_session() && isset($_SESSION['user_type']) && ($_SESSION['user_type'] === 'admin' || $_SESSION['user_type'] === 'webmaster')) {
    header("location: dashboard.php");
    exit();
}

// 3. Process login form submission if not locked out
if (isset($_POST['login']) && !$is_locked_out) {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $login_error = "CSRF token validation failed. Please try again.";
    } else {
        $email = trim($_POST['email']);
        $password = $_POST['user_pass'];

        $user_data = $user->checkPassword($email, $password);

        if ($user_data && isset($user_data['user_type'])) {
            if ($user_data['user_type'] === 'admin' || $user_data['user_type'] === 'webmaster') {
                // On successful login, clear any previous attempt tracking
                unset($_SESSION['login_attempts']);
                unset($_SESSION['login_attempts_time']);

                // Regenerate session ID to prevent session fixation
                session_regenerate_id(true);

                $user->create_session(
                    $user_data['uid'],
                    $user_data['email'],
                    $user_data['user_type'],
                    array()
                );
                header("location: dashboard.php");
                exit();
            } else {
                $login_error = 'Access Denied. Admins or Webmasters only.';
                handle_failed_login();
            }
        } else {
            $login_error = 'Invalid credentials or account type.';
            handle_failed_login();
        }
    }
}
?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
    <meta charset="UTF-8">
    <title>Admin Login | My First Movie</title>

    <link href="assets/global/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet">

    <style>
    /* RESET */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: "Segoe UI", Tahoma, sans-serif;
    }

    /* NIGHT SKY + STARS */
    body {
        height: 100vh;
        overflow: hidden;
        display: flex;
        justify-content: center;
        align-items: center;
        background:
            radial-gradient(1px 1px at 10% 20%, white, transparent),
            radial-gradient(1.5px 1.5px at 30% 80%, white, transparent),
            radial-gradient(1px 1px at 50% 40%, white, transparent),
            radial-gradient(1.2px 1.2px at 70% 30%, white, transparent),
            radial-gradient(1px 1px at 90% 70%, white, transparent),
            linear-gradient(to bottom, #0b1026, #141e30 40%, #243b55);
    }

    /* HEAVY SNOW */
    body::before,
    body::after {
        content: "";
        position: absolute;
        inset: -200%;
        background-image:
            radial-gradient(3px 3px at 10% 20%, white, transparent),
            radial-gradient(4px 4px at 20% 80%, white, transparent),
            radial-gradient(2px 2px at 30% 40%, white, transparent),
            radial-gradient(3px 3px at 40% 60%, white, transparent),
            radial-gradient(4px 4px at 50% 20%, white, transparent),
            radial-gradient(2px 2px at 60% 90%, white, transparent),
            radial-gradient(3px 3px at 70% 50%, white, transparent),
            radial-gradient(4px 4px at 80% 30%, white, transparent),
            radial-gradient(2px 2px at 90% 70%, white, transparent);
        animation: snow 14s linear infinite;
        z-index: 1;
    }

    body::after {
        animation-duration: 24s;
        opacity: 0.6;
        filter: blur(1px);
    }

    @keyframes snow {
        0% { transform: translateY(0); }
        100% { transform: translateY(50%); }
    }

    /* LOGIN BOX */
    .login-container {
        width: 480px;
        padding: 40px;
        background: rgba(255,255,255,0.9);
        border-radius: 18px;
        box-shadow: 0 30px 60px rgba(0,0,0,0.5);
        z-index: 10;
        position: relative;
    }

    /* HEADER */
    .login-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
    }

    #logo {
        background: #fff;
        padding: 10px 18px;
        border-radius: 10px;
        box-shadow: 0 6px 18px rgba(0,0,0,0.2);
    }

    #logo img {
        max-width: 150px;
    }

    .back-link a {
        color: #333;
        font-weight: 500;
        text-decoration: none;
    }

    .back-link a:hover {
        text-decoration: underline;
    }

    /* TEXT */
    .login-container h2 {
        text-align: center;
        font-weight: 600;
        color: #222;
    }

    .login-container p {
        text-align: center;
        color: #555;
        margin-bottom: 20px;
    }

    /* FORM */
    .form-group label {
        font-weight: 600;
        color: #333;
    }

    .form-control {
        height: 46px;
        border-radius: 8px;
        border: 1px solid #ddd;
    }

    .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 2px rgba(102,126,234,0.25);
    }

    /* BEAUTIFUL LOGIN BUTTON */
    .btn-primary {
        height: 48px;
        border-radius: 30px;
        background: linear-gradient(135deg, #667eea, #764ba2);
        border: none;
        font-weight: 600;
        letter-spacing: 0.6px;
        color: #fff;
        position: relative;
        overflow: hidden;
        transition: all 0.35s ease;
        box-shadow: 0 12px 30px rgba(102,126,234,0.45);
    }

    .btn-primary::before {
        content: "";
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(
            120deg,
            transparent,
            rgba(255,255,255,0.6),
            transparent
        );
        transition: 0.6s;
    }

    .btn-primary:hover::before {
        left: 100%;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 20px 45px rgba(118,75,162,0.7);
    }

    .btn-primary:focus {
        outline: none;
        box-shadow:
            0 0 0 3px rgba(118,75,162,0.35),
            0 0 30px rgba(118,75,162,0.8);
    }

    .btn-primary:active {
        transform: scale(0.96);
    }

    /* ALERT */
    .alert-danger {
        border-radius: 8px;
        text-align: center;
    }

    /* SCENE */
    .scene {
        position: absolute;
        bottom: 0;
        width: 100%;
        height: 45%;
        z-index: 2;
        pointer-events: none;
    }

    .mountains {
        position: absolute;
        bottom: 20%;
        width: 100%;
        height: 40%;
        background: linear-gradient(to top, #1b2735, #2c3e50);
        clip-path: polygon(
            0 100%, 10% 60%, 20% 80%, 30% 50%,
            40% 75%, 50% 45%, 60% 70%, 70% 40%,
            80% 65%, 90% 50%, 100% 100%
        );
    }

    .river {
        position: absolute;
        bottom: 15%;
        width: 100%;
        height: 10%;
        background: linear-gradient(to right, #1e3c72, #2a5298);
        opacity: 0.6;
    }

    .land {
        position: absolute;
        bottom: 0;
        width: 100%;
        height: 20%;
        background: #0b3d2e;
    }

    .trees {
        position: absolute;
        bottom: 18%;
        width: 100%;
        height: 15%;
        background:
            repeating-linear-gradient(
                to right,
                transparent,
                transparent 40px,
                #0b3d2e 40px,
                #0b3d2e 55px
            );
    }

    .home {
        position: absolute;
        bottom: 20%;
        left: 70%;
        width: 40px;
        height: 30px;
        background: #c0392b;
    }

    .home::before {
        content: "";
        position: absolute;
        top: -20px;
        left: -10px;
        width: 60px;
        height: 20px;
        background: #922b21;
        clip-path: polygon(50% 0, 0 100%, 100% 100%);
    }
    </style>
    </head>

    <body>

    <div class="login-container">

        <div class="login-header">
            <div id="logo">
                <a href="../index.php">
                    <img src="../images/logo.png" alt="My First Movie">
                </a>
            </div>
            <div class="back-link">
                <a href="../index.php">← Back to Main Website</a>
            </div>
        </div>

        <form method="post">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            <h2><?php echo $user->get_company_name(); ?></h2>
            <p>Admin Panel Login</p>

            <?php if (!empty($login_error)) { ?>
                <div class="alert alert-danger"><?php echo $login_error; ?></div>
            <?php } ?>

            <div class="form-group">
                <label>Email</label>
                <input type="email" class="form-control" name="email" required placeholder="Enter your email" <?php if ($is_locked_out) echo 'disabled'; ?>>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" class="form-control" name="user_pass" required placeholder="Enter your password" <?php if ($is_locked_out) echo 'disabled'; ?>>
            </div>

            <button type="submit" name="login" class="btn btn-primary btn-block" <?php if ($is_locked_out) echo 'disabled'; ?>>
                Login
            </button>
        </form>
    </div>

    <div class="scene">
        <div class="mountains"></div>
        <div class="river"></div>
        <div class="land"></div>
        <div class="trees"></div>
        <div class="home"></div>
    </div>

    </body>
    </html>
