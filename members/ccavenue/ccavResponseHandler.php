<?php
require_once('../inc/requires.php');
$database = new MySQLDB();
$cn = $database->db;
include('Crypto.php');

use App\Models\Enrollment; // Add this line
    
    $workingKey=$_ENV['CCAV_WORKING_KEY'] ?? null;     //Working Key should be provided here.
    $encResponse=$_POST["encResp"];         //This is the response sent by the CCAvenue Server
    $rcvdString=decrypt($encResponse,$workingKey);      //Crypto Decryption used as per the specified working key.
    
    $decryptValues=explode('&', $rcvdString);
    $response = [];
    foreach ($decryptValues as $value) {
        $information = explode('=', $value);
        if (count($information) == 2) {
            $response[$information[0]] = urldecode($information[1]);
        }
    }

    $order_status = $response['order_status'] ?? '';
    $order_id     = $response['order_id'] ?? '';
    $amount       = $response['amount'] ?? '';
    $tracking_id  = $response['tracking_id'] ?? '';
    $bank_ref_no  = $response['bank_ref_no'] ?? '';
    
    echo "<center>";
    if($order_status==="Success")
    {
        $msg = "Thank you for the payment. Your credit card has been charged and your transaction is successful. We will contact you soon.";
        echo "<br>".$msg;
        echo "<br/><br/><span style='text-align:center;'><a href='../dashboard.php'>Back to Dashboard</a></span>";
        $act = 1;
         
    }
    else if($order_status==="Aborted")
    {
        $msg = "We will keep you posted regarding the status of your payment through e-mail";
        echo "<br>".$msg;
        echo "<br/><br/><span style='text-align:center;'><a href='../dashboard.php'>Back to Dashboard</a></span>";
        $act = 0;
     
    }
    else if($order_status==="Failure")
    {
        $msg = "Sorry,the transaction has been declined.";
        echo "<br>".$msg;
        echo "<br/><br/><span style='text-align:center;'><a href='../dashboard.php'>Back to Dashboard</a></span>";
        $act = 0;
    }
    else
    {   
    
        $msg = "Security Error. Illegal access detected"; 
        echo "<br>".$msg;
        echo "<br/><br/><span style='text-align:center;'><a href='../dashboard.php'>Back to Dashboard</a></span>";
        $act = 0;
     
    }
 
    echo "</center>";
    
    // Update enrollment status if payment was successful and validated
    if ($order_status === "Success" && !empty($order_id)) {
        $enrollment = Enrollment::find($order_id);
        if ($enrollment && $enrollment->fee == $amount) {
            if ($enrollment->status !== 'completed') {
                $enrollment->status = 'completed';
                $enrollment->payment_tracking_id = $tracking_id;
                $enrollment->payment_ref_no = $bank_ref_no;
                $enrollment->save();
            }
        } else {
            // Log this for security review, but don't expose details to user
            error_log("CCAVenue Response Error: Amount mismatch or invalid enrollment for order_id: " . $order_id);
            // Optionally, you might want to update the enrollment status to 'failed' or 'fraud_suspected'
        }
    }

    $time = date("Y-m-d H:i:s"); // Use current time for logging

    // Prepare data for ccav_resp insertion
    $uid_to_log = $_SESSION['uid'] ?? 0; // Fallback if uid is not in session
    $category_to_log = $response['merchant_param1'] ?? ($enrollment->title ?? 'N/A'); // Use merchant_param1 or enrollment title

    $stmt = $database->db->prepare("INSERT INTO ccav_resp(`uid`, `order_id`, `title`, `amount`, `billing_name`, `billing_address`, `billing_city`, `billing_state`, `billing_zip`, `billing_country`, `billing_tel`, `billing_email`, `billing_ip`, `status`, `msg`, `dt`, `act`, `tracking_id`, `bank_ref_no`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->execute([
        $uid_to_log,
        $order_id,
        $category_to_log,
        $amount,
        $response['billing_name'] ?? '',
        $response['billing_address'] ?? '',
        $response['billing_city'] ?? '',
        $response['billing_state'] ?? '',
        $response['billing_zip'] ?? '',
        $response['billing_country'] ?? '',
        $response['billing_tel'] ?? '',
        $response['billing_email'] ?? '',
        $_SERVER['REMOTE_ADDR'], // Use server remote address as billing_ip is not in CCAvenue response
        $order_status,
        $msg,
        $time,
        $act,
        $tracking_id,
        $bank_ref_no
    ]);
    
?>