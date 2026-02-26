<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/database.php';

use App\Models\WebUser;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Security checks
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

if (!isset($_SESSION['user_type']) || !in_array($_SESSION['user_type'], ['webmaster', 'admin'])) {
    http_response_code(403);
    exit('Forbidden');
}

if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    http_response_code(403);
    exit('Invalid CSRF token');
}

$userId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$userId) {
    http_response_code(400);
    exit('Invalid user ID');
}

$user = WebUser::find($userId);

if (!$user) {
    http_response_code(404);
    exit('User not found');
}

$user->admin_approved = 1;
$user->save();

// Send approval email
$mail = new PHPMailer(true);

try {
    //Server settings
    $mail->isSMTP();
    $mail->Host = 'smtp.example.com'; // Replace with your SMTP host
    $mail->SMTPAuth = true;
    $mail->Username = 'user@example.com'; // Replace with your SMTP username
    $mail->Password = 'secret'; // Replace with your SMTP password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    //Recipients
    $mail->setFrom('info@dolphinrfid.in', 'Dolphin RFID');
    $mail->addAddress($user->email, $user->first_name . ' ' . $user->last_name);

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Account Approved - Dolphin RFID';
    $mail->Body    = 'Dear ' . $user->first_name . ' ' . $user->last_name . ',<br><br>Your account has been approved. You can now log in to access the private area.<br><br>Let us know if you face any difficulty.<br><br><strong>Team Dolphin RFID</strong>';
    $mail->AltBody = 'Dear ' . $user->first_name . ' ' . $user->last_name . ',\n\nYour account has been approved. You can now log in to access the private area.\n\nLet us know if you face any difficulty.\n\nTeam Dolphin RFID';

    $mail->send();
    echo 'User approved and email sent successfully.';
} catch (Exception $e) {
    error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
    // Don't expose detailed error to user
    http_response_code(500);
    exit('Failed to send approval email.');
}

header('Location: all-web-users.php?status=approved');
exit();