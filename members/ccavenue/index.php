<?php
require_once('../inc/requires.php');

// Get the correct base path from current request
$script = $_SERVER['SCRIPT_NAME'] ?? '';
$basePath = '';
if ($script) {
    $parts = explode('/', trim($script, '/'));
    if (!empty($parts)) {
        $basePath = '/' . $parts[0]; // This will give us /myfirstmovie3
    }
}
$correct_base_path = $basePath;

// Get members path dynamically for URL generation
$scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$requestUri = $_SERVER['REQUEST_URI'];

// Extract the actual path from the current request
$uriParts = explode('/', trim($requestUri, '/'));
$membersIndex = array_search('members', $uriParts);

if ($membersIndex !== false) {
    $members_path = $scheme . '://' . $host . '/' . implode('/', array_slice($uriParts, 0, $membersIndex + 1)) . '/';
} else {
    $members_path = $scheme . '://' . $host . '/members/'; // fallback
}

$database = new MySQLDB();

// Check if billing details are available in the session
if (isset($_SESSION['billing_details'])) {
    $billing_details = $_SESSION['billing_details'];

    // Extract details from session
    $uid = $_SESSION['uid'] ?? null; // Assuming uid is also in session
    $enrollment_id = $billing_details['enrollment_id'];
    $enrollment_title = $billing_details['enrollment_title'];
    $order_id = $billing_details['order_id'];
    $amount = $billing_details['amount'];
    $redirect_url = $billing_details['redirect_url'];
    $cancel_url = $billing_details['cancel_url'];

    // Fetch user details
    $users = null;
    if ($uid) {
        $user_result = $database->query("SELECT * FROM web_users WHERE uid=?", [$uid]);
        $users = $database->fetch_array($user_result);
    }

    // Ensure we have the necessary data before proceeding
    if (!$users) {
        $_SESSION['error_message'] = "User information not found for payment processing.";
        header("Location: " . $members_path . "my-enrollments.php");
        exit();
    }

    // Clear billing details from session after use
    unset($_SESSION['billing_details']);
?>
<style>
.ccavenue_form {
display: none;
}
</style>
<div class="ccavenue_form">
<form method="post" id="customerData" name="customerData" action="ccavRequestHandler.php">
        <table width="40%" height="100" border='1' align="center"><caption><font size="4" color="blue"><b>Integration Kit</b></font></caption></table>
            <table width="50%" height="100" border='1' align="center">
                <tr>
                    <td>Parameter Name:</td><td>Parameter Value:</td>
                </tr>
                <tr>
                    <td colspan="2"> Compulsory information</td>
                </tr>
                <tr>
                    <td>Merchant Id   :</td><td><input type="text" name="merchant_id" value="<?php echo getenv('CCAV_MERCHANT_ID'); ?>"/></td>
                </tr>
                <tr>
                    <td>Order Id  :</td><td><input type="text" name="order_id" value="<?php echo e($order_id); ?>"/></td>
                </tr>
                <tr>
                    <td>Amount    :</td><td><input type="text" name="amount" value="<?php echo e($amount); ?>"/></td>
                </tr>
                <tr>
                    <td>Currency  :</td><td><input type="text" name="currency" value="INR"/></td>
                </tr>
                <tr>
                    <td>Redirect URL  :</td><td><input type="text" name="redirect_url" value="<?php echo e($redirect_url); ?>"/></td>
                </tr>
                <tr>
                    <td>Cancel URL    :</td><td><input type="text" name="cancel_url" value="<?php echo e($cancel_url); ?>"/></td>
                </tr>
                <tr>
                    <td>Language  :</td><td><input type="text" name="language" value="EN"/></td>
                </tr>
                <tr>
                    <td colspan="2">Billing information(optional):</td>
                </tr>
                <tr>
                    <td>Billing Name  :</td><td><input type="text" name="billing_name" value="<?php echo htmlspecialchars($users['first_name'], ENT_QUOTES)." ".htmlspecialchars($users['last_name'], ENT_QUOTES); ?>"/></td>
                </tr>
                <tr>
                    <td>Billing Address   :</td><td><input type="text" name="billing_address" value="<?php echo e($billing_details['billing_address']); ?>"/></td>
                </tr>
                <tr>
                    <td>Billing City  :</td><td><input type="text" name="billing_city" value="<?php echo e($billing_details['billing_city']); ?>"/></td>
                </tr>
                <tr>
                    <td>Billing State :</td><td><input type="text" name="billing_state" value="<?php echo e($billing_details['billing_state']); ?>"/></td>
                </tr>
                <tr>
                    <td>Billing Zip   :</td><td><input type="text" name="billing_zip" value="<?php echo e($billing_details['billing_zip']); ?>"/></td>
                </tr>
                <tr>
                    <td>Billing Country   :</td><td><input type="text" name="billing_country" value="<?php echo e($billing_details['billing_country']); ?>"/></td>
                </tr>
                <tr>
                    <td>Billing Tel   :</td><td><input type="text" name="billing_tel" value="<?php echo htmlspecialchars($users['contact'], ENT_QUOTES); ?>"/></td>
                </tr>
                <tr>
                    <td>Billing Email :</td><td><input type="text" name="billing_email" value="<?php echo htmlspecialchars($users['email'], ENT_QUOTES); ?>"/></td>
                </tr>
                <tr>
                    <td colspan="2">Shipping information(optional)</td>
                </tr>
                <tr>
                    <td>Shipping Name :</td><td><input type="text" name="delivery_name" value=""/></td>
                </tr>
                <tr>
                    <td>Shipping Address  :</td><td><input type="text" name="delivery_address" value=""/></td>
                </tr>
                <tr>
                    <td>shipping City :</td><td><input type="text" name="delivery_city" value=""/></td>
                </tr>
                <tr>
                    <td>shipping State    :</td><td><input type="text" name="delivery_state" value=""/></td>
                </tr>
                <tr>
                    <td>shipping Zip  :</td><td><input type="text" name="delivery_zip" value=""/></td>
                </tr>
                <tr>
                    <td>shipping Country  :</td><td><input type="text" name="delivery_country" value=""/></td>
                </tr>
                <tr>
                    <td>Shipping Tel  :</td><td><input type="text" name="delivery_tel" value=""/></td>
                </tr>
                <tr>
                    <td>Merchant Param1   :</td><td><input type="text" name="merchant_param1" value="additional Info."/></td>
                </tr>
                
                  
                <tr>
                    <td></td><td><INPUT TYPE="submit" value="CheckOut"></td>
                </tr>
            </table>
          </form>
</div>
<script nonce="<?= $nonce ?>">
document.getElementById("customerData").submit();
</script>
<noscript>
    <input type="submit" value="Click here to proceed to payment"/>
</noscript>
<?php
} else {
    // If accessed directly or without the necessary session data, redirect
    $_SESSION['error_message'] = "Invalid access to payment gateway. Please initiate payment from your enrollment details.";
    header("Location: " . $members_path . "my-enrollments.php");
    exit();
}
?>