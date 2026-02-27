<?php
/**
 * Member File Download Page.
 *
 * @package MFM
 * @subpackage Members
 */
declare(strict_types=1);

require_once 'inc/requires.php';

if (!$user->check_session()) {
    header("location: index.php");
    exit();
}

if (!$user->isActive()) {
    header("location: activate.php");
    exit();
}

$user_id = (int)$_SESSION['uid'];
$date = date('Y-m-d');

// Sanitize and validate input
$subscription_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$order_id = filter_input(INPUT_GET, 'order_id', FILTER_VALIDATE_INT);
$type = filter_input(INPUT_GET, 'type', FILTER_SANITIZE_STRING);

if (!$subscription_id || !$order_id || !$type) {
    header("location: dashboard.php?error=invalid_params");
    exit();
}

$pdf_from_db = null;

$base_query = "SELECT s.pdf, o.order_status
    FROM orders_subscription os
    JOIN subscriptions s ON os.subscription_id = s.subcription_id
    JOIN orders o ON os.order_id = o.order_id
    WHERE os.subscription_id = ? AND os.user_id = ? AND os.order_id = ? AND os.expire_date >= ?";

if ($type === 'single') {
    $stmt = $database->prepare($base_query . " AND os.pricing_slab = 'Single Issue'");
    $stmt->bind_param('iiis', $subscription_id, $user_id, $order_id, $date);
} elseif ($type === 'multi') {
    $stmt = $database->prepare($base_query);
    $stmt->bind_param('iiis', $subscription_id, $user_id, $order_id, $date);
} else {
    header("location: dashboard.php?error=invalid_type");
    exit();
}

$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $data = $result->fetch_assoc();
    if ($data['order_status'] === 'Success') {
        $pdf_from_db = $data['pdf'];

        // Update download count. Assumes a UNIQUE key on (user_id, subcription_id).
        $stmt_download = $database->prepare("INSERT INTO downloads (user_id, subcription_id, order_id, downloads, donwload_date) VALUES (?, ?, ?, 1, ?) ON DUPLICATE KEY UPDATE downloads = downloads + 1, donwload_date = ?");
        $stmt_download->bind_param('iiiss', $user_id, $subscription_id, $order_id, $date, $date);
        $stmt_download->execute();
        $stmt_download->close();
    }
}
$stmt->close();

if ($pdf_from_db) {
    $filename = basename($pdf_from_db);
    $base_dir = realpath(__DIR__ . '/../docs');
    $filepath = $base_dir . DIRECTORY_SEPARATOR . $filename;

    // Security check: ensure the final path is within the intended directory and the file exists.
    if ($base_dir && strpos(realpath($filepath), $base_dir) === 0 && file_exists($filepath)) {
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: public");
        header("Content-Description: File Transfer");
        header("Content-type: application/octet-stream");
        header("Content-Disposition: attachment; filename=\"" . $filename . "\"");
        header("Content-Transfer-Encoding: binary");
        header("Content-Length: " . filesize($filepath));
        
        // Clean output buffer before sending file
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        readfile($filepath);
        exit();
    }
}

// If file not found or access denied, redirect
header("location: dashboard.php?error=file_not_found");
exit();

?>