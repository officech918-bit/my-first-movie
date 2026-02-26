<?php declare(strict_types=1);

/**
 * Handles AJAX requests for fetching category fees.
 *
 * This script is called via AJAX to retrieve the fee for a given category title.
 * It ensures input is sanitized and uses a prepared statement for security.
 *
 * @package MFM
 * @subpackage Members
 */

require_once __DIR__ . '/inc/requires.php';

// Sanitize input from POST request.
$categoryTitle = filter_input(INPUT_POST, 'checkedValue', FILTER_SANITIZE_STRING);

if ($categoryTitle) {
    $stmt = $database->db->prepare("SELECT fee FROM categories WHERE title = ? AND status = '1'");
    $stmt->execute([$categoryTitle]);
    if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Ensure fee is properly converted to float and formatted
        $fee = (float)$row['fee'];
        // Echo fee with proper formatting, ensuring it's properly escaped.
        echo number_format($fee, 2, '.', '');
    } else {
        // It's good practice to return a default value if nothing is found.
        echo "0.00";
    }

} else {
    // Handle cases where 'checkedValue' is not provided or is empty.
    echo "0.00";
}