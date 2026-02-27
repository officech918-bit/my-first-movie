<?php
require_once('inc/requires.php');

// Restrict access to local machine for safety
$is_local = in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1']);
if (!$is_local) {
    http_response_code(403);
    exit('Forbidden: run this tool only from localhost. Remove this file after use.');
}

$database = new MySQLDB();
$message = '';
$error = '';

// Ensure required columns exist in web_users
try {
    $conn = $database->db;
    // Check for user_type
    $stmt = $conn->prepare("SELECT 1 FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'web_users' AND column_name = 'user_type'");
    if ($stmt) {
        $stmt->execute();
        $hasUserType = $stmt->get_result()->num_rows > 0;
        $stmt->close();
        if (!$hasUserType) {
            $conn->query("ALTER TABLE web_users ADD COLUMN user_type VARCHAR(20) NOT NULL DEFAULT 'user'");
        }
    }
    // Check for hash_code
    $stmt = $conn->prepare("SELECT 1 FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'web_users' AND column_name = 'hash_code'");
    if ($stmt) {
        $stmt->execute();
        $hasHashCode = $stmt->get_result()->num_rows > 0;
        $stmt->close();
        if (!$hasHashCode) {
            $conn->query("ALTER TABLE web_users ADD COLUMN hash_code VARCHAR(255) NOT NULL");
        }
    }
} catch (Throwable $e) {
    // Soft-fail schema check; show as warning but continue
    $error = 'Schema check warning: ' . $e->getMessage();
}

// CSRF setup
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf = $_SESSION['csrf_token'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($csrf, $_POST['csrf_token'])) {
        $error = 'CSRF token validation failed.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm'] ?? '';
        $user_type = $_POST['user_type'] ?? 'admin';

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Enter a valid email.';
        } else if (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters.';
        } else if ($password !== $confirm) {
            $error = 'Passwords do not match.';
        } else if (!in_array($user_type, ['admin','webmaster'], true)) {
            $error = 'Invalid user type.';
        } else {
            // Update web_users with a new password hash and ensure correct user_type
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $database->db->prepare("UPDATE web_users SET hash_code = ?, user_type = ?, activation_status = 1, admin_approved = 1, status = 1 WHERE email = ?");
            if ($stmt) {
                $stmt->bind_param('sss', $hash, $user_type, $email);
                $stmt->execute();
                if ($stmt->affected_rows > 0) {
                    $message = "Password reset successful. Try logging in as $user_type now.";
                } else {
                    $error = 'No matching user found in web_users for this email.';
                }
                $stmt->close();
            } else {
                $error = 'Failed to prepare database statement.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Admin Password (Temporary Tool)</title>
    <link href="assets/global/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { padding: 40px; max-width: 640px; margin: 0 auto; }
        .box { border: 1px solid #ddd; border-radius: 6px; padding: 20px; }
    </style>
    <base href="<?php echo $admin_path; ?>">
</head>
<body>
    <div class="box">
        <h3>Reset Admin/Webmaster Password</h3>
        <p>Use this only from localhost, then delete this file: admin/reset_admin_password.php</p>
        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php elseif ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8'); ?>">
            <div class="form-group">
                <label>Email</label>
                <input type="email" class="form-control" name="email" required>
            </div>
            <div class="form-group">
                <label>New Password</label>
                <input type="password" class="form-control" name="password" required minlength="6">
            </div>
            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" class="form-control" name="confirm" required minlength="6">
            </div>
            <div class="form-group">
                <label>User Type</label>
                <select class="form-control" name="user_type">
                    <option value="admin">admin</option>
                    <option value="webmaster">webmaster</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Reset Password</button>
        </form>
    </div>
</body>
</html>
