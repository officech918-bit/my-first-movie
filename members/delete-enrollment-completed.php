<?php
session_start();
require_once '../inc/requires.php';
require_once 'ccavenue/Crypto.php'; // Include CCAvenue encryption/decryption utility

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
        // Ensure the enrollment status is 'completed' before proceeding with refund
        if ($enrollment->status === 'completed') {
            // Fetch the corresponding CCAvenue response record
            $ccavResponse = CcavResponse::where('order_id', $enrollment_id)
                                        ->where('act', 1) // Only consider successful payments
                                        ->first();

            if ($ccavResponse) {
                // --- CCAvenue Refund API Integration ---
                $workingKey = $user->get_cc_working_key(); // Get working key from user settings
                $accessCode = $user->get_cc_access_code(); // Assuming you have a method to get access code

                // Prepare refund request data
                // The exact parameters might vary based on CCAvenue's API version.
                // This is a common set of parameters for a refund.
                $refund_data = [
                    'command' => 'refundOrder',
                    'reference_no' => $ccavResponse->order_id, // Original order ID
                    'refund_amount' => $ccavResponse->amount, // Amount to refund
                    'refund_reason' => 'User requested cancellation',
                    // Add other necessary parameters as per CCAvenue documentation
                ];

                $merchant_data = http_build_query($refund_data);
                $encrypted_data = encrypt($merchant_data, $workingKey);

                $api_url = "https://api.ccavenue.com/apis/servlet/DoWebTrans"; // CCAvenue API endpoint

                $post_data = [
                    'enc_request' => $encrypted_data,
                    'access_code' => $accessCode,
                    'command' => 'refundOrder',
                    'request_type' => 'JSON', // Or XML, depending on what you prefer/expect
                    'response_type' => 'JSON',
                    'version' => '1.2' // Check CCAvenue documentation for correct version
                ];

                // Use cURL to send the request
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $api_url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_VERBOSE, 1);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
                $response = curl_exec($ch);

                if (curl_errno($ch)) {
                    $_SESSION['error_message'] = 'CCAvenue API error:' . curl_error($ch);
                    curl_close($ch);
                } else {
                    curl_close($ch);
                    // Decrypt the response
                    $decrypted_response = decrypt($response, $workingKey);
                    $response_array = json_decode($decrypted_response, true);

                    // Process CCAvenue response
                    if (isset($response_array['status']) && $response_array['status'] === 'Success') {
                        // Refund successful
                        $enrollment->status = 'refunded'; // Or 'cancelled'
                        $enrollment->save();

                        // Optionally update ccavResponse status or add a new refund record
                        $ccavResponse->act = 2; // Assuming '2' means refunded
                        $ccavResponse->save();

                        $_SESSION['success_message'] = "Enrollment cancelled and amount refunded successfully. Your amount will be returned shortly.";
                    } else {
                        // Refund failed
                        $error_msg = $response_array['message'] ?? 'Unknown refund error from CCAvenue.';
                        $_SESSION['error_message'] = "Refund failed: " . $error_msg;
                    }
                }
            } else {
                $_SESSION['error_message'] = "No successful payment record found for this enrollment.";
            }
        } else {
            $_SESSION['error_message'] = "Cannot refund enrollment with status other than 'completed'.";
        }
    } else {
        $_SESSION['error_message'] = "Enrollment not found.";
    }
} else {
    $_SESSION['error_message'] = "Invalid request.";
}

header('Location: my-enrollments.php');
exit();