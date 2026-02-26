<?php
require_once('../inc/requires.php');
include('Crypto.php');

if(empty($_POST['order_id']) || empty($_POST['amount'])){
    die("Invalid payment request.");
}

// Check if amount is greater than 0
if (!isset($_POST['amount']) || (float)$_POST['amount'] <= 0) {
    die("Invalid amount. Amount must be greater than 0.");
}

// Define the fields expected by CCAvenue
$fields = [
    'merchant_id',
    'order_id',
    'amount',
    'currency',
    'redirect_url',
    'cancel_url',
    'language',
    'billing_name',
    'billing_email',
    'billing_tel',
    'billing_address',
    'billing_city',
    'billing_state',
    'billing_zip',
    'billing_country',
    'delivery_name',
    'delivery_address',
    'delivery_city',
    'delivery_state',
    'delivery_zip',
    'delivery_country',
    'delivery_tel',
    'delivery_email',
    'merchant_param1',
    'merchant_param2',
    'merchant_param3',
    'merchant_param4',
    'merchant_param5',
    'promo_code',
    'customer_identifier'
];

// Access environment variables via $_ENV superglobal
$working_key = $_ENV['CCAV_WORKING_KEY'] ?? null; //Shared by CCAVENUES
$access_code = $_ENV['CCAV_ACCESS_CODE'] ?? null; //Shared by CCAVENUES
$merchant_id_env = $_ENV['CCAV_MERCHANT_ID'] ?? null; // Merchant ID from .env
$ccavenue_transaction_url = $_ENV['CCAV_TRANSACTION_URL'] ?? 'https://secure.ccavenue.com/transaction/transaction.do?command=initiateTransaction'; // Default to production URL

// --- Start Debug Logging ---
$log_file = __DIR__ . '/ccavenue_debug.log';
$log_message = "[" . date("Y-m-d H:i:s") . "] CCAvenue Debug Info:\n";
$log_message .= "  CCAV_WORKING_KEY (from _ENV): " . ($working_key ? "Loaded" : "NOT SET") . "\n";
$log_message .= "  CCAV_ACCESS_CODE (from _ENV): " . ($access_code ? "Loaded" : "NOT SET") . "\n";
$log_message .= "  CCAV_MERCHANT_ID (from _ENV): " . ($merchant_id_env ? "Loaded: " . $merchant_id_env : "NOT SET") . "\n";
if (isset($_POST['merchant_id'])) {
    $log_message .= "  Merchant ID from POST: " . $_POST['merchant_id'] . "\n";
} else {
    $log_message .= "  Merchant ID NOT found in POST data.\n";
}
$log_message .= "  _POST['order_id']: " . ($_POST['order_id'] ?? 'NOT SET') . "\n"; // Log order_id from POST
$log_message .= "----------------------------------------\n";
$log_message .= "  Full _POST array: " . print_r($_POST, true) . "\n"; // Log entire $_POST array
$log_message .= "----------------------------------------\n\n";
file_put_contents($log_file, $log_message, FILE_APPEND);
// --- End Debug Logging ---

$merchant_data = '';

// Define the fields expected by CCAvenue
$fields = [
    'merchant_id',
    'order_id',
    'amount',
    'currency',
    'redirect_url',
    'cancel_url',
    'language',
    'billing_name',
    'billing_email',
    'billing_tel',
    'billing_address',
    'billing_city',
    'billing_state',
    'billing_zip',
    'billing_country',
    'delivery_name',
    'delivery_address',
    'delivery_city',
    'delivery_state',
    'delivery_zip',
    'delivery_country',
    'delivery_tel',
    'delivery_email',
    'merchant_param1',
    'merchant_param2',
    'merchant_param3',
    'merchant_param4',
    'merchant_param5',
    'promo_code',
    'customer_identifier'
];

// Always include merchant_id from $_ENV, as it's required by CCAvenue
// and might not be present in $_POST from the client-side form.
if ($merchant_id_env) {
    $merchant_data .= 'merchant_id=' . urlencode($merchant_id_env) . '&';
} else {
    // Log an error if merchant_id_env is not set, which shouldn't happen if .env is loaded
    file_put_contents($log_file, "[" . date("Y-m-d H:i:s") . "] ERROR: CCAV_MERCHANT_ID is NOT SET in _ENV when preparing merchant_data.\n", FILE_APPEND);
}

foreach ($fields as $field) {
    // Skip 'merchant_id' if we've already added it from $_ENV
    if ($field === 'merchant_id') {
        continue;
    }
    if(isset($_POST[$field])){
        $merchant_data .= $field.'='.urlencode($_POST[$field]).'&';
    }
}

// Log the final merchant_data before encryption
file_put_contents($log_file, "[" . date("Y-m-d H:i:s") . "] Final merchant_data before encryption: " . $merchant_data . "\n\n", FILE_APPEND);

$encrypted_data=encrypt($merchant_data,$working_key); // Method for encrypting the data.
 
 
 
 
?>
<form method="post" name="redirect" action="<?= htmlspecialchars($ccavenue_transaction_url) ?>"> 
<?php
echo "<input type=hidden name=encRequest value=$encrypted_data>";
echo "<input type=hidden name=access_code value=$access_code>";
?>
</form>
</center>
<script nonce="<?= $nonce ?>">document.redirect.submit();</script>
</body>
</html>