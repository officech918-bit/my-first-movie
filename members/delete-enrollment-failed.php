<?php
session_start();
require_once '../inc/requires.php';

use App\Models\Enrollment;
use App\Models\CcavResponse;

// CSRF protection
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die('CSRF token validation failed.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $enrollment_id = (int)$_POST['id'];

    // Find the enrollment
    $enrollment = Enrollment::find($enrollment_id);

    if ($enrollment) {
        // Ensure the enrollment status is 'failed' before proceeding
        if ($enrollment->status === 'failed') {
            // Delete associated CCAvenue response records
            CcavResponse::where('order_id', $enrollment_id)->delete();

            // Delete the enrollment record
            $enrollment->delete();

            $_SESSION['success_message'] = "Failed enrollment and associated payment records deleted successfully.";
        } else {
            $_SESSION['error_message'] = "Cannot delete enrollment with status other than 'failed'.";
        }
    } else {
        $_SESSION['error_message'] = "Enrollment not found.";
    }
} else {
    $_SESSION['error_message'] = "Invalid request.";
}

header('Location: my-enrollments.php');
exit();