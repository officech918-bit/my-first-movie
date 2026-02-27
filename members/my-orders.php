<?php declare(strict_types=1);

/**
 * @author   closemarketing
 * @license  https://www.closemarketing.com/
 * @version  1.0.0
 * @since    1.0.0
 */

require_once __DIR__ . '/inc/requires.php';

date_default_timezone_set('Asia/Kolkata');
$date = date("Y-m-d H:i:s");
$time = date("d-m-Y H:i");
$order_date = date("d M Y");

$is_error = false;
$from_pg = false;

if (!$user->check_session()) {
    header("Location: index.php");
    exit();
}

if (!$user->isActive()) {
    header("location: activate.php");
    exit();
}

$cc_working_key = $user->get_cc_working_key();
$company_name = $user->get_company_name();
$path = $user->get_path();



if(isset($_POST["encResp"])) {	
	
	$from_pg = true;
	include('../Crypto.php');

	error_reporting(0);
	$workingKey=$cc_working_key;		//Working Key should be provided here.
	$encResponse=$_POST["encResp"];			//This is the response sent by the CCAvenue Server
	$rcvdString=decrypt($encResponse,$workingKey);		//Crypto Decryption used as per the specified working key.
	$decryptValues=explode('&', $rcvdString);
	$dataSize=sizeof($decryptValues);

	$order_status="";
	$order_id = "";
	$tracking_id="";
	$bank_ref_no="";
	$order_status="";
	$failure_message="";
	$payment_mode="";
	$card_name="";
	$status_code="";
	$status_message="";
	$currency="";
	$amount="";
	$billing_name="";
	$billing_address="";
	$billing_city="";
	$billing_state="";
	$billing_zip="";
	$billing_country="";
	$billing_tel="";
	$billing_email="";
	$vault="";
	$product_amount='';
	$service_tax='';
		
	$echo_message = '';
	$message_type = 0;


	for($i = 0; $i < $dataSize; $i++) 
	{
		$information=explode('=',$decryptValues[$i]);
		if($i==0)	$order_id =$information[1];
		else if($i==1)	$tracking_id =$information[1];
		else if($i==2)	$bank_ref_no=$information[1];
		else if($i==3)	$order_status=$information[1];
		else if($i==4)	$failure_message=$information[1];
		else if($i==5)	$payment_mode=$information[1];
		else if($i==6)	$card_name=$information[1];
		else if($i==7)	$status_code=$information[1];
		else if($i==8)	$status_message=$information[1];
		else if($i==9)	$currency=$information[1];
		else if($i==10)	$amount=$information[1];
		else if($i==11)	$billing_name=$information[1];
		else if($i==12)	$billing_address=$information[1];
		else if($i==13)	$billing_city=$information[1];
		else if($i==14)	$billing_state=$information[1];
		else if($i==15)	$billing_zip=$information[1];
		else if($i==16)	$billing_country=$information[1];
		else if($i==17)	$billing_tel=$information[1];
		else if($i==18)	$billing_email=$information[1];
		else if($i==26)	$product_amount=$information[1];
		else if($i==27)	$service_tax=$information[1];
		else if($i==28)	$i_agree=$information[1];
		else if($i==31)	$vault=$information[1];
		
	}
	
    $user_id = (int)$_SESSION['uid'];
    $ip = $user->getRealIPAddr();

    // Create the order and insert into the database
    $stmt = $database->prepare("INSERT INTO orders(user_id, date, ip_address, order_id, product_amount,  service_tax, amount, currency, billing_name, billing_email, billing_tel, billing_address, billing_city, billing_state, billing_zip, billing_country, i_agree, tracking_id, bank_ref_no, order_status, failure_message, payment_mode, card_name, status_code, status_message, vault) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('isssssssssssssssssssssssss', $user_id, $date, $ip, $order_id, $product_amount, $service_tax, $amount, $currency, $billing_name, $billing_email, $billing_tel, $billing_address, $billing_city, $billing_state, $billing_zip, $billing_country, $i_agree, $tracking_id, $bank_ref_no, $order_status, $failure_message, $payment_mode, $card_name, $status_code, $status_message, $vault);
    $stmt->execute();
    $stmt->close();

    if (isset($_SESSION["subscriptions"])) {
        foreach ($_SESSION["subscriptions"] as $cart_itm) {
            $subscription_id = (int)$cart_itm["subscription_id"];
            $subscription_price_id = (int)$cart_itm["subscription_price_id"];

            // Get the pricing slab & prepare the expire date
            $stmt = $database->prepare("SELECT duration FROM pricing WHERE price_id = ?");
            $stmt->bind_param('i', $subscription_price_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $temp_arr = $result->fetch_assoc();
            $duration = $temp_arr['duration'];
            $expire_date = '';

            switch ($duration) {
                case 'Single Issue':
                    $expire_date = date('Y-m-d H:i:s', strtotime("+7 day"));
                    break;
                case '3 Months':
                    $expire_date = date('Y-m-d H:i:s', strtotime("+3 months"));
                    break;
                case '6 Months':
                    $expire_date = date('Y-m-d H:i:s', strtotime("+6 months"));
                    break;
                case '1 Year':
                    $expire_date = date('Y-m-d H:i:s', strtotime("+1 year"));
                    break;
                case '2 Year':
                    $expire_date = date('Y-m-d H:i:s', strtotime("+2 year"));
                    break;
            }
            $stmt->close();

            $stmt = $database->prepare("INSERT INTO orders_subscription(order_id, subscription_id, price_id, user_id, pricing_slab, subscription_from, expire_date) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param('siiisss', $order_id, $subscription_id, $subscription_price_id, $user_id, $duration, $date, $expire_date);
            $stmt->execute();
            $stmt->close();
        }
    }

	//prepare message to show
	if($order_status==="Success")
	{
		
		//mail the order detail to the client
		$to = $billing_email.', subscriber@projectalert.in';
		$subject = "Project Alert - Subscription Order# ".e($order_id);
		// To send the HTML mail we need to set the Content-type header.		
		$headers = "From: subscriber@projectalert.in\r\n";
		$headers .= "Reply-To: subscriber@projectalert.in\r\n";
		//$headers .= "BCC: subscriber@projectalert.in\r\n";
		$headers .= "MIME-Version: 1.0\r\n";
		$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
		
		//html mail data
		$message = '<html>
	<head>
	<title>Project Alert Order #'.e($order_id).'</title>
	</head>
	
	<body>
	<p></p>
	<div>
	  <table style="font-family: "Century Gothic" ! important; line-height: normal; font-size: 13px; width: 100%;" border="0" cellspacing="0" cellpadding="0" bgcolor="#f5f5f5">
		<tbody>
		  <tr>
			<td><table border="0" cellspacing="0" cellpadding="0">
				<tbody>
				</tbody>
			  </table>
			  <table style="width: 100%;" bgcolor="#f5f5f5">
				<tbody>
				  <tr>
					<td align="center"><table style="width: 750px;" border="0" cellspacing="0" cellpadding="0" align="center" bgcolor="#f5f5f5">
						<tbody>
						  <tr>
							<td width="10%" height="65" align="left"><div style="min-height: 45px;"><a href="http://projectalert.in/" target="_blank"><img src="'.e($path).'assets/frontend/layout/img/logos/logo.png" border="0" alt="" width="227" height="50" /></a></div></td>
							<td width="90%" align="right" valign="bottom"><span style="font-size: 40px; font-family:Century Gothic; color: #666666;">Order<br />
							  </span> <span style="font-size: 24px; color: #666666;font-family:Century Gothic;">Project Alert</span>
							  <div><span style="font-size: 11px; line-height: 16px; color: #999999;font-family:Century Gothic;"> '.e($billing_name).', this is the order you have placed.<br />
								</span></div></td>
						  </tr>
						</tbody>
					  </table>
					  <table style="width: 750px;" border="0" cellspacing="0" cellpadding="0" align="center">
						<tbody>
						  <tr>
							<td style="padding: 30px;" width="100%" align="left" bgcolor="#B83334"><span style="font-size: 40px; font-family:Century Gothic; color: #ffffff;">Order #: '.e($order_id).'<br />
							  </span> <span style="font-size: 24px; color: #ffffff;font-family:Century Gothic;">Dated: '.e($order_date).' </span></td>
						  </tr>
						  <tr>
							<td style="line-height: 16px;" align="left" valign="top" bgcolor="#ffffff"></td>
						  </tr>
						  <tr>
							<td valign="top" bgcolor="#ffffff"><table style="width: 100%;" border="0" cellspacing="0" cellpadding="0">
								<tbody>
								  <tr>
									<td align="left" valign="top" style="padding: 20px;"><span style="line-height: 20px; color: #000;font-size:15px; font-family:"Century Gothic"">Dear '.e($billing_name).',<br />
									  <br />
									  Thank you for placing subscription order with us. Below are the list of subscriptions you have ordered for. </span></td>
								  </tr>
								  <tr>
									<td style="padding: 0px 20px;font-size:15px; font-family:"Century Gothic"" align="left" valign="top"><table width="100%" border="0" align="center" cellpadding="0" cellspacing="0"  style="color:#333; line-height:20px;font-size:15px; font-family:Century Gothic" >
										<tbody>
										  <tr>
											<td width="22%" align="center" valign="center" bgcolor="#e4e4e4" style="padding:6px;border-left:1px solid #bcbcbc;border-top:1px solid #bcbcbc;border-bottom:1px solid #bcbcbc;">Item Ordered</td>
											<td width="10%" align="left" valign="center" bgcolor="#e4e4e4" style="padding:6px;border-left:1px solid #bcbcbc;border-top:1px solid #bcbcbc;border-bottom:1px solid #bcbcbc">Issue Duration</td>
											<td width="15%" align="center" valign="center" bgcolor="#e4e4e4" style="padding:6px;border-left:1px solid #bcbcbc;border-top:1px solid #bcbcbc;border-bottom:1px solid #bcbcbc;border-right:1px solid #bcbcbc;">Amount</td>
										  </tr>';
										  
									if (isset($_SESSION["subscriptions"])) 
										{ 	
											foreach ($_SESSION["subscriptions"] as $cart_itm) {
												$subscription_id = (int)$cart_itm["subscription_id"];
												$subscription_price_id = (int)$cart_itm["subscription_price_id"];
												$amount = 0;
												$sub_total = 0; 
                                                
                                                $stmt = $database->prepare("SELECT * FROM subscriptions WHERE subcription_id = ?");
                                                $stmt->bind_param('i', $subscription_id);
                                                $stmt->execute();
                                                $result = $stmt->get_result();
												if($result->num_rows > 0) {
													$data = $result->fetch_assoc();
													
                                                    $stmt_pricing = $database->prepare("SELECT * FROM pricing WHERE subscription_id = ?");
                                                    $stmt_pricing->bind_param('i', $subscription_id);
                                                    $stmt_pricing->execute();
                                                    $result2 = $stmt_pricing->get_result();
	
													while($rows = $result2->fetch_assoc()) {
														if($rows['price_id'] == $subscription_price_id) {  
															$amount = (int)$rows['amount'];
                                                            $sub_total += $amount;
															$message .= '<tr><td style="padding:6px;border-left:1px solid #bcbcbc;border-bottom:1px solid #bcbcbc" valign="middle" >'.e($data['title']).'</td><td style="padding:6px;border-left:1px solid #bcbcbc;border-bottom:1px solid #bcbcbc" valign="middle" >'.e($rows['duration']).'</td><td style="padding:6px;border-left:1px solid #bcbcbc;border-bottom:1px solid #bcbcbc;border-right:1px solid #bcbcbc;" align="center">Rs. '.e((string)$rows['amount']).'.00</td></tr>';
														}
													}
                                                    $stmt_pricing->close();
												}
                                                $stmt->close();
											}
										}
									 
										  
								 
								$sub_total = number_format( $sub_total, 2, '.', '' );          
								$percent = $sub_total/100;
								$service_tax = number_format( $percent * 14, 2, '.', '' );
								$total_amount = number_format( $service_tax + $sub_total, 2, '.', '' );
															
								 
										 
										  
										  
										  $message .= '<tr>
											<td style="padding:6px;border-left:1px solid #bcbcbc;border-bottom:1px solid #bcbcbc" valign="middle" >&nbsp;</td>
											<td style="padding:6px;border-left:1px solid #bcbcbc;border-bottom:1px solid #bcbcbc" valign="middle" >&nbsp;</td>
											<td style="padding:6px;border-left:1px solid #bcbcbc;border-bottom:1px solid #bcbcbc;border-right:1px solid #bcbcbc;" align="center">&nbsp;</td>
										  </tr>
										  <tr>
											<td style="padding:6px;border-left:1px solid #bcbcbc;border-bottom:1px solid #bcbcbc" valign="middle" >&nbsp;</td>
											<td style="padding:6px;border-left:1px solid #bcbcbc;border-bottom:1px solid #bcbcbc" valign="middle" >Sub Total</td>
											<td style="padding:6px;border-left:1px solid #bcbcbc;border-bottom:1px solid #bcbcbc;border-right:1px solid #bcbcbc;" align="center">Rs. '.$sub_total.'</td>
										  </tr>
										  <tr>
											<td style="padding:6px;border-left:1px solid #bcbcbc;border-bottom:1px solid #bcbcbc" valign="middle" >&nbsp;</td>
											<td style="padding:6px;border-left:1px solid #bcbcbc;border-bottom:1px solid #bcbcbc" valign="middle" >Service Tax (14%)</td>
											<td style="padding:6px;border-left:1px solid #bcbcbc;border-bottom:1px solid #bcbcbc;border-right:1px solid #bcbcbc;" align="center">Rs. '.$service_tax.'</td>
										  </tr>
										  <tr>
											<td style="padding:6px;border-left:1px solid #bcbcbc;border-bottom:1px solid #bcbcbc" valign="middle" >&nbsp;</td>
											<td style="padding:6px;border-left:1px solid #bcbcbc;border-bottom:1px solid #bcbcbc" valign="middle" >Total Payable</td>
											<td style="padding:6px;border-left:1px solid #bcbcbc;border-bottom:1px solid #bcbcbc;border-right:1px solid #bcbcbc;" align="center">Rs. '.$total_amount.'</td>
										  </tr>
									  </table></td>
								  </tr>
								  <tr>
									<td style="padding: 20px; font-size: 15px; color: #000;font-family: "Century Gothic";" align="left" valign="top"><p style="font-size:15px; font-family:"Century Gothic" ">Feel free to contact us, if you face any difficulty while making payment of the order.</p>
									  <table style="width: 100%;" border="0" cellspacing="0" cellpadding="0" bgcolor="#ffffff">
										<tbody>
										  <tr>
											<td align="left"></td>
										  </tr>
										  <tr>
											<td align="left" style="font-size:15px; font-family:"Century Gothic" ">&nbsp;</td>
										  </tr>
										  <tr>
											<td align="left" valign="top"></td>
										  </tr>
										  <tr>
											<td style="font-size: 15px; color: #003a5b;font-family: "Century Gothic";" height="40" align="left" valign="top"><strong>Need help?</strong></td>
										  </tr>
										  <tr>
											<td align="left"><table style="width: 100%;" border="0" cellspacing="0" cellpadding="0">
												<tbody>
												  <tr>
													<td width="18%" align="center"><table style="width: 95%;" cellspacing="0" cellpadding="0" align="center">
														<tbody>
														  <tr>
															<td height="35" align="center" style="border:solid 1px #444;color:#111;font-size:11px;">Call: 022-6712-1749</td>
														  </tr>
														</tbody>
													  </table></td>
													<td width="18%" align="center"><table style="width: 95%;" border="0" cellspacing="0" cellpadding="0" align="center">
														<tbody>
														  <tr>
															<td style="border: solid 1px #444;" height="35" align="center">Mail: <a style="color: #111; font-size: 15px; text-decoration: none;font-family: "Century Gothic"; " href="mailto:subscriber@projectalert.in" target="_blank">subscriber@projectalert.in</a></td>
														  </tr>
														</tbody>
													  </table></td>
													<td width="18%" align="center"><table style="width: 95%;" border="0" cellspacing="0" cellpadding="0" align="center">
														<tbody>
														  <tr>
															<td style="border: solid 1px #444;" height="35" align="center"><a style="color: #111; font-size: 15px; text-decoration: none;font-family: "Century Gothic"; " href="http://projectalert.in/contact-us.php" target="_blank">Contact Us</a></td>
														  </tr>
														</tbody>
													  </table></td>
												  </tr>
												</tbody>
											  </table></td>
										  </tr>
										</tbody>
									  </table></td>
								  </tr>
								  <tr>
									<td style="padding:0px; font-size: 15px; color: #003a5b;font-family: "Century Gothic";" align="left" valign="top"><table width="750" border="0" align="center" cellpadding="0" cellspacing="0">
										<tbody>
										  <tr>
											<td style="line-height: 28px; font-size: 24px; text-align: left; color: #fff; padding: 5px 15px;font-family: "Century Gothic"; " width="64%" align="center" bgcolor="#B83334"><strong>Thanks for being a loyal customer.</strong><br />
											  <span style="font-size: 18px;font-family: "Century Gothic"; ">know more about our services visit our website.</span><br /></td>
											<td style="line-height: 28px; font-size: 24px; text-align: center; color: #fff; padding: 5px 15px 10px;" width="34%" align="center" valign="top" bgcolor="#3E4095"><table style="border: 0px solid; border-collapse: collapse;background: none repeat scroll 0% 0% #0398d2; width: 55%;margin-top:10px;" border="0" cellspacing="0" cellpadding="0" align="center">
												<tbody>
												  <tr>
													<td style="font-size: 16px;" height="40" align="center"><a style="font-size: 17px; color: #fff; text-decoration: none;font-family: "Century Gothic"; " href="http://accordequips.com/photography-packages.php#skin3-top-slide_left" target="_blank">Visit Now</a></td>
												  </tr>
												</tbody>
											  </table></td>
										  </tr>
										</tbody>
									  </table></td>
								  </tr>
								  <tr>
									<td style="padding:0px; font-size: 15px; color: #003a5b;font-family: "Century Gothic";" align="left" valign="top"><table width="750" border="0" align="center" cellpadding="0" cellspacing="0" style="width: 750px;">
										<tbody>
										</tbody>
										<tbody>
										  <tr>
											<td style="padding: 10px; padding-left: 20px; line-height: 18px;background-color:#444;" width="43%" align="left" valign="bottom" bgcolor="#444"><span style="font-size: 11px; color: #999999;font-family: "Century Gothic"; "> Economic Research India Pvt. Ltd. <a style="text-decoration: none; color: #fff;font-family: "Century Gothic"; " href="http://projectalert.in/" target="_blank">http://projectalert.in</a><br />
											  Copyright ?. All rights reserved.</span></td>
											<td style="padding: 10px; font-size: 11px; color: #999999;background-color:#444;" width="57%" align="right" valign="bottom" bgcolor="#444">Sterling House, 5/7, Sorabji Santuk Lane, Marine Lines (E), Mumbai-400002.
											  Email: <a href="mailto:subscriber@projectalert.in">subscriber@projectalert.in</a> Tel: 022-6712-1749 </td>
										  </tr>
										</tbody>
									  </table></td>
								  </tr>
								</tbody>
							  </table></td>
						  </tr>
						</tbody>
					  </table></td>
				  </tr>
				</tbody>
			  </table>
			  <br /></td>
		  </tr>
		</tbody>
	  </table>
	</div>
	</body>
	</html>
	';
		$isSent = mail($to, $subject, $message, $headers);
	
	
		
		//send invoice & download link to client
		//mail the order detail to the client
		$to = $billing_email.', subscriber@projectalert.in';
		$subject = "Project Alert - Subscription Order# $order_id";
		// To send the HTML mail we need to set the Content-type header.		
		$headers = "From: subscriber@projectalert.in\r\n";
		$headers .= "Reply-To: subscriber@projectalert.in\r\n";
		//$headers .= "BCC: subscriber@projectalert.in\r\n";
		$headers .= "MIME-Version: 1.0\r\n";
		$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
	
		$message = '<html>
<head>
<title>Project Alert Successfull Payment</title>
</head>

<body>
<p></p>
<div>
  <table style="font-family: "Century Gothic" ! important; line-height: normal; font-size: 13px; width: 100%;" border="0" cellspacing="0" cellpadding="0" bgcolor="#f5f5f5">
    <tbody>
      <tr>
        <td><table border="0" cellspacing="0" cellpadding="0">
            <tbody>
            </tbody>
          </table>
          <table style="width: 100%;" bgcolor="#f5f5f5">
            <tbody>
              <tr>
                <td align="center"><table style="width: 750px;" border="0" cellspacing="0" cellpadding="0" align="center" bgcolor="#f5f5f5">
                    <tbody>
                      <tr>
                        <td width="10%" height="65" align="left"><div style="min-height: 45px;"><a href="http://projectalert.in/" target="_blank"><img src="http://projectalert.in/assets/frontend/layout/img/logos/logo.png" border="0" alt="" width="227" height="50" /></a></div></td>
                        <td width="90%" align="right" valign="bottom"><span style="font-size: 40px; font-family:Century Gothic; color: #666666;">Payment Invoice<br />
                          </span> <span style="font-size: 24px; color: #666666;font-family:Century Gothic;">Project Alert</span>
                          <div><span style="font-size: 11px; line-height: 16px; color: #999999;font-family:Century Gothic;"> '.$billing_name.', this is an Invoice.<br />
                            </span></div></td>
                      </tr>
                    </tbody>
                  </table>
                  <table style="width: 750px;" border="0" cellspacing="0" cellpadding="0" align="center">
                    <tbody>
                      <tr>
                        <td style="padding: 30px;" width="100%" align="left" bgcolor="#B83334"><span style="font-size: 40px; font-family:Century Gothic; color: #ffffff;">Order #: '.$order_id.'<br />
                          </span> <span style="font-size: 24px; color: #ffffff;font-family:Century Gothic;">Dated: '.$order_date.' </span></td>
                      </tr>
                      <tr>
                        <td style="line-height: 16px;" align="left" valign="top" bgcolor="#ffffff"></td>
                      </tr>
                      <tr>
                        <td valign="top" bgcolor="#ffffff"><table style="width: 100%;" border="0" cellspacing="0" cellpadding="0">
                            <tbody>
                              <tr>
                                <td align="left" valign="top" style="padding: 20px;"><span style="line-height: 20px; color: #000;font-size:15px; font-family:"Century Gothic"">Dear '.$billing_name.',<br />
                                  <br />
                                  Thank you for placing subscription order with us. This is a payment receipt for Order '.$order_id.' sent on '.$order_date.'. </span></td>
                              </tr>
                              <tr>
                                <td style="padding: 0px 20px;font-size:15px; font-family:"Century Gothic"" align="left" valign="top">
                                <br>
                                <table width="100%" border="0" align="center" cellpadding="0" cellspacing="0"  style="color:#333; line-height:20px;font-size:15px; font-family:Century Gothic" >
                                    <tbody>
                                      <tr>
                                        <td width="22%" align="center" valign="center" bgcolor="#e4e4e4" style="padding:6px;border-left:1px solid #bcbcbc;border-top:1px solid #bcbcbc;border-bottom:1px solid #bcbcbc;">Transaction #</td>
                                        <td width="10%" align="left" valign="center" bgcolor="#e4e4e4" style="padding:6px;border-left:1px solid #bcbcbc;border-top:1px solid #bcbcbc;border-bottom:1px solid #bcbcbc">Total Paid</td>
                                        <td width="15%" align="center" valign="center" bgcolor="#e4e4e4" style="padding:6px;border-left:1px solid #bcbcbc;border-top:1px solid #bcbcbc;border-bottom:1px solid #bcbcbc;border-right:1px solid #bcbcbc;">Status</td>
                                      </tr>
                                      <tr>
                                        <td style="padding:6px;border-left:1px solid #bcbcbc;border-bottom:1px solid #bcbcbc" valign="middle" >'.$tracking_id.'</td>
                                        <td style="padding:6px;border-left:1px solid #bcbcbc;border-bottom:1px solid #bcbcbc" valign="middle" >Rs. '.$total_amount.'</td>
                                        <td style="padding:6px;border-left:1px solid #bcbcbc;border-bottom:1px solid #bcbcbc;border-right:1px solid #bcbcbc;" align="center">'.$order_status.'</td>
                                      </tr>
                                  </table>
                                <br>
                                
                                <table width="100%" border="0" align="center" cellpadding="0" cellspacing="0"  style="color:#333; line-height:20px;font-size:15px; font-family:Century Gothic" >
                                    <tbody>
                                      <tr>
                                        <td align="center" valign="center" bgcolor="#e4e4e4" style="padding:6px;border-left:1px solid #bcbcbc;border-top:1px solid #bcbcbc;border-bottom:1px solid #bcbcbc; border-right:1px solid #bcbcbc">Your Order is ready to Download</td>
                                      </tr>
                                      <tr>
                                        <td valign="middle" align="center" style="padding:6px;border-left:1px solid #bcbcbc;border-bottom:1px solid #bcbcbc;border-right:1px solid #bcbcbc;" ><table style="border: 0px solid; border-collapse: collapse;background: none repeat scroll 0% 0% #0398d2; width: 30%;margin-top:10px;" border="0" cellspacing="0" cellpadding="0" align="center">
                                            <tbody>
                                              <tr>
                                                <td style="font-size: 16px;" height="40" align="center"><a style="font-size: 17px; color: #fff; text-decoration: none;font-family: "Century Gothic"; " href="'.$path.'members/dashboard.php" target="_blank">Download Now</a></td>
                                              </tr>
                                            </tbody>
                                          </table>
                                        </td>
                                      </tr>
                                  </table></td>
                              </tr>
                              <tr>
                                <td style="padding: 20px; font-size: 15px; color: #000;font-family: "Century Gothic";" align="left" valign="top"><p style="font-size:15px; font-family:"Century Gothic" ">You may review your order history at any time by logging in to your <a href="'.$path.'members/">Client Area</a>.<br />

<strong>Note:</strong> This email will serve as an official receipt for this payment..</p>
                                  <table style="width: 100%;" border="0" cellspacing="0" cellpadding="0" bgcolor="#ffffff">
                                    <tbody>
                                      <tr>
                                        <td align="left"></td>
                                      </tr>
                                      <tr>
                                        <td align="left" style="font-size:15px; font-family:"Century Gothic" ">&nbsp;</td>
                                      </tr>
                                      <tr>
                                        <td align="left" valign="top"></td>
                                      </tr>
                                      <tr>
                                        <td style="font-size: 15px; color: #003a5b;font-family: "Century Gothic";" height="40" align="left" valign="top"><strong>Need help?</strong></td>
                                      </tr>
                                      <tr>
                                        <td align="left"><table style="width: 100%;" border="0" cellspacing="0" cellpadding="0">
                                            <tbody>
                                              <tr>
                                                <td width="18%" align="center"><table style="width: 95%;" cellspacing="0" cellpadding="0" align="center">
                                                    <tbody>
                                                      <tr>
                                                        <td height="35" align="center" style="border:solid 1px #444;color:#111;font-size:11px;">Call: 022-6712-1749</td>
                                                      </tr>
                                                    </tbody>
                                                  </table></td>
                                                <td width="18%" align="center"><table style="width: 95%;" border="0" cellspacing="0" cellpadding="0" align="center">
                                                    <tbody>
                                                      <tr>
                                                        <td style="border: solid 1px #444;" height="35" align="center">Mail: <a style="color: #111; font-size: 15px; text-decoration: none;font-family: "Century Gothic"; " href="mailto:subscriber@projectalert.in" target="_blank">subscriber@projectalert.in</a></td>
                                                      </tr>
                                                    </tbody>
                                                  </table></td>
                                                <td width="18%" align="center"><table style="width: 95%;" border="0" cellspacing="0" cellpadding="0" align="center">
                                                    <tbody>
                                                      <tr>
                                                        <td style="border: solid 1px #444;" height="35" align="center"><a style="color: #111; font-size: 15px; text-decoration: none;font-family: "Century Gothic"; " href="http://projectalert.in/contact-us.php" target="_blank">Contact Us</a></td>
                                                      </tr>
                                                    </tbody>
                                                  </table></td>
                                              </tr>
                                            </tbody>
                                          </table></td>
                                      </tr>
                                    </tbody>
                                  </table></td>
                              </tr>
                              <tr>
                                <td style="padding:0px; font-size: 15px; color: #003a5b;font-family: "Century Gothic";" align="left" valign="top"><table width="750" border="0" align="center" cellpadding="0" cellspacing="0">
                                    <tbody>
                                      <tr>
                                        <td style="line-height: 28px; font-size: 24px; text-align: left; color: #fff; padding: 5px 15px;font-family: "Century Gothic"; " width="64%" align="center" bgcolor="#B83334"><strong>Thanks for being a loyal customer.</strong><br />
                                          <span style="font-size: 18px;font-family: "Century Gothic"; ">know more about our services visit our website.</span><br /></td>
                                        <td style="line-height: 28px; font-size: 24px; text-align: center; color: #fff; padding: 5px 15px 10px;" width="34%" align="center" valign="top" bgcolor="#3E4095"><table style="border: 0px solid; border-collapse: collapse;background: none repeat scroll 0% 0% #0398d2; width: 55%;margin-top:10px;" border="0" cellspacing="0" cellpadding="0" align="center">
                                            <tbody>
                                              <tr>
                                                <td style="font-size: 16px;" height="40" align="center"><a style="font-size: 17px; color: #fff; text-decoration: none;font-family: "Century Gothic"; " href="http://projectalert.in/" target="_blank">Visit Now</a></td>
                                              </tr>
                                            </tbody>
                                          </table></td>
                                      </tr>
                                    </tbody>
                                  </table></td>
                              </tr>
                              <tr>
                                <td style="padding:0px; font-size: 15px; color: #003a5b;font-family: "Century Gothic";" align="left" valign="top"><table width="750" border="0" align="center" cellpadding="0" cellspacing="0" style="width: 750px;">
                                    <tbody>
                                    </tbody>
                                    <tbody>
                                      <tr>
                                        <td style="padding: 10px; padding-left: 20px; line-height: 18px;background-color:#444;" width="43%" align="left" valign="bottom" bgcolor="#444"><span style="font-size: 11px; color: #999999;font-family: "Century Gothic"; "> Economic Research India Pvt. Ltd. <a style="text-decoration: none; color: #fff;font-family: "Century Gothic"; " href="http://projectalert.in/" target="_blank">http://projectalert.in</a><br />
                                          Copyright ?. All rights reserved.</span></td>
                                        <td style="padding: 10px; font-size: 11px; color: #999999;background-color:#444;" width="57%" align="right" valign="bottom" bgcolor="#444">Sterling House, 5/7, Sorabji Santuk Lane, Marine Lines (E), Mumbai-400002.
                                          Email: <a href="mailto:subscriber@projectalert.in">subscriber@projectalert.in</a> Tel: 022-6712-1749 </td>
                                      </tr>
                                    </tbody>
                                  </table></td>
                              </tr>
                            </tbody>
                          </table></td>
                      </tr>
                    </tbody>
                  </table></td>
              </tr>
            </tbody>
          </table>
          <br /></td>
      </tr>
    </tbody>
  </table>
</div>
</body>
</html>';
		
		$isSent = mail($to, $subject, $message, $headers);
		
		
		$echo_message = "<br>Thank you for shopping with us. Your credit card has been charged and your transaction is successful. You can now <a href='dashboard.php' class='btn btn-primary green' style='color:#ffffff;'> DOWNLOAD </a> your subscription";
		$message_type = 1;
		
		//mail the payment status to the client upon successful recieve
		
		
	}
	else if($order_status==="Aborted")
	{
		//send invoice & download link to client
		//mail the order detail to the client
		$to = $billing_email.', subscriber@projectalert.in';
		$subject = "Payment Aborted - Subscription Order# $order_id - Project Alert";
		// To send the HTML mail we need to set the Content-type header.		
		$headers = "From: subscriber@projectalert.in\r\n";
		$headers .= "Reply-To: subscriber@projectalert.in\r\n";
		//$headers .= "BCC: subscriber@projectalert.in\r\n";
		$headers .= "MIME-Version: 1.0\r\n";
		$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
	
		$message = '<html>
<head>
<title>Project Alert Successfull Payment</title>
</head>

<body>
<p></p>
<div>
  <table style="font-family: "Century Gothic" ! important; line-height: normal; font-size: 13px; width: 100%;" border="0" cellspacing="0" cellpadding="0" bgcolor="#f5f5f5">
    <tbody>
      <tr>
        <td><table border="0" cellspacing="0" cellpadding="0">
            <tbody>
            </tbody>
          </table>
          <table style="width: 100%;" bgcolor="#f5f5f5">
            <tbody>
              <tr>
                <td align="center"><table style="width: 750px;" border="0" cellspacing="0" cellpadding="0" align="center" bgcolor="#f5f5f5">
                    <tbody>
                      <tr>
                        <td width="10%" height="65" align="left"><div style="min-height: 45px;"><a href="http://projectalert.in/" target="_blank"><img src="http://projectalert.in/assets/frontend/layout/img/logos/logo.png" border="0" alt="" width="227" height="50" /></a></div></td>
                        <td width="90%" align="right" valign="bottom"><span style="font-size: 40px; font-family:Century Gothic; color: #666666;">Payment Aborted<br />
                          </span> <span style="font-size: 24px; color: #666666;font-family:Century Gothic;">Project Alert</span>
                          <div><span style="font-size: 11px; line-height: 16px; color: #999999;font-family:Century Gothic;"> '.$billing_name.', this is a payment status notification.<br />
                            </span></div></td>
                      </tr>
                    </tbody>
                  </table>
                  <table style="width: 750px;" border="0" cellspacing="0" cellpadding="0" align="center">
                    <tbody>
                      <tr>
                        <td style="padding: 30px;" width="100%" align="left" bgcolor="#B83334"><span style="font-size: 40px; font-family:Century Gothic; color: #ffffff;">Order #: '.$order_id.'<br />
                          </span> <span style="font-size: 24px; color: #ffffff;font-family:Century Gothic;">Dated: '.$order_date.' </span></td>
                      </tr>
                      <tr>
                        <td style="line-height: 16px;" align="left" valign="top" bgcolor="#ffffff"></td>
                      </tr>
                      <tr>
                        <td valign="top" bgcolor="#ffffff"><table style="width: 100%;" border="0" cellspacing="0" cellpadding="0">
                            <tbody>
                              <tr>
                                <td align="left" valign="top" style="padding: 20px;"><span style="line-height: 20px; color: #000;font-size:15px; font-family:"Century Gothic"">Dear '.$billing_name.',<br />
                                  <br />
                                  Thank you for placing subscription order with us. However the payment has been aborted. Request you to make another purchase. </span></td>
                              </tr>
                              <tr>
                                <td style="padding: 20px; font-size: 15px; color: #000;font-family: "Century Gothic";" align="left" valign="top"><p style="font-size:15px; font-family:"Century Gothic" ">You may review your order history at any time by logging in to your <a href="'.$path.'members/">Client Area</a>.<br />

<strong>Note:</strong> This email will serve as an official receipt for this payment..</p>
                                  <table style="width: 100%;" border="0" cellspacing="0" cellpadding="0" bgcolor="#ffffff">
                                    <tbody>
                                      <tr>
                                        <td align="left"></td>
                                      </tr>
                                      <tr>
                                        <td align="left" style="font-size:15px; font-family:"Century Gothic" ">&nbsp;</td>
                                      </tr>
                                      <tr>
                                        <td align="left" valign="top"></td>
                                      </tr>
                                      <tr>
                                        <td style="font-size: 15px; color: #003a5b;font-family: "Century Gothic";" height="40" align="left" valign="top"><strong>Need help?</strong></td>
                                      </tr>
                                      <tr>
                                        <td align="left"><table style="width: 100%;" border="0" cellspacing="0" cellpadding="0">
                                            <tbody>
                                              <tr>
                                                <td width="18%" align="center"><table style="width: 95%;" cellspacing="0" cellpadding="0" align="center">
                                                    <tbody>
                                                      <tr>
                                                        <td height="35" align="center" style="border:solid 1px #444;color:#111;font-size:11px;">Call: 022-6712-1749</td>
                                                      </tr>
                                                    </tbody>
                                                  </table></td>
                                                <td width="18%" align="center"><table style="width: 95%;" border="0" cellspacing="0" cellpadding="0" align="center">
                                                    <tbody>
                                                      <tr>
                                                        <td style="border: solid 1px #444;" height="35" align="center">Mail: <a style="color: #111; font-size: 15px; text-decoration: none;font-family: "Century Gothic"; " href="mailto:subscriber@projectalert.in" target="_blank">subscriber@projectalert.in</a></td>
                                                      </tr>
                                                    </tbody>
                                                  </table></td>
                                                <td width="18%" align="center"><table style="width: 95%;" border="0" cellspacing="0" cellpadding="0" align="center">
                                                    <tbody>
                                                      <tr>
                                                        <td style="border: solid 1px #444;" height="35" align="center"><a style="color: #111; font-size: 15px; text-decoration: none;font-family: "Century Gothic"; " href="http://projectalert.in/contact-us.php" target="_blank">Contact Us</a></td>
                                                      </tr>
                                                    </tbody>
                                                  </table></td>
                                              </tr>
                                            </tbody>
                                          </table></td>
                                      </tr>
                                    </tbody>
                                  </table></td>
                              </tr>
                              <tr>
                                <td style="padding:0px; font-size: 15px; color: #003a5b;font-family: "Century Gothic";" align="left" valign="top"><table width="750" border="0" align="center" cellpadding="0" cellspacing="0">
                                    <tbody>
                                      <tr>
                                        <td style="line-height: 28px; font-size: 24px; text-align: left; color: #fff; padding: 5px 15px;font-family: "Century Gothic"; " width="64%" align="center" bgcolor="#B83334"><strong>Thanks for being a loyal customer.</strong><br />
                                          <span style="font-size: 18px;font-family: "Century Gothic"; ">know more about our services visit our website.</span><br /></td>
                                        <td style="line-height: 28px; font-size: 24px; text-align: center; color: #fff; padding: 5px 15px 10px;" width="34%" align="center" valign="top" bgcolor="#3E4095"><table style="border: 0px solid; border-collapse: collapse;background: none repeat scroll 0% 0% #0398d2; width: 55%;margin-top:10px;" border="0" cellspacing="0" cellpadding="0" align="center">
                                            <tbody>
                                              <tr>
                                                <td style="font-size: 16px;" height="40" align="center"><a style="font-size: 17px; color: #fff; text-decoration: none;font-family: "Century Gothic"; " href="http://projectalert.in/" target="_blank">Visit Now</a></td>
                                              </tr>
                                            </tbody>
                                          </table></td>
                                      </tr>
                                    </tbody>
                                  </table></td>
                              </tr>
                              <tr>
                                <td style="padding:0px; font-size: 15px; color: #003a5b;font-family: "Century Gothic";" align="left" valign="top"><table width="750" border="0" align="center" cellpadding="0" cellspacing="0" style="width: 750px;">
                                    <tbody>
                                    </tbody>
                                    <tbody>
                                      <tr>
                                        <td style="padding: 10px; padding-left: 20px; line-height: 18px;background-color:#444;" width="43%" align="left" valign="bottom" bgcolor="#444"><span style="font-size: 11px; color: #999999;font-family: "Century Gothic"; "> Economic Research India Pvt. Ltd. <a style="text-decoration: none; color: #fff;font-family: "Century Gothic"; " href="http://projectalert.in/" target="_blank">http://projectalert.in</a><br />
                                          Copyright ?. All rights reserved.</span></td>
                                        <td style="padding: 10px; font-size: 11px; color: #999999;background-color:#444;" width="57%" align="right" valign="bottom" bgcolor="#444">Sterling House, 5/7, Sorabji Santuk Lane, Marine Lines (E), Mumbai-400002.
                                          Email: <a href="mailto:subscriber@projectalert.in">subscriber@projectalert.in</a> Tel: 022-6712-1749 </td>
                                      </tr>
                                    </tbody>
                                  </table></td>
                              </tr>
                            </tbody>
                          </table></td>
                      </tr>
                    </tbody>
                  </table></td>
              </tr>
            </tbody>
          </table>
          <br /></td>
      </tr>
    </tbody>
  </table>
</div>
</body>
</html>';
		
		$isSent = mail($to, $subject, $message, $headers);
		
		$echo_message = "<br>Thank you for shopping with us.We will keep you posted regarding the status of your order through e-mail";
		$message_type = 2;
	}
	else if($order_status==="Failure")
	{
		//send invoice & download link to client
		//mail the order detail to the client
		$to = $billing_email.', subscriber@projectalert.in';
		$subject = "Payment Failure - Subscription Order# $order_id - Project Alert";
		// To send the HTML mail we need to set the Content-type header.		
		$headers = "From: subscriber@projectalert.in\r\n";
		$headers .= "Reply-To: subscriber@projectalert.in\r\n";
		//$headers .= "BCC: subscriber@projectalert.in\r\n";
		$headers .= "MIME-Version: 1.0\r\n";
		$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
	
		$message = '<html>
<head>
<title>Project Alert Successfull Payment</title>
</head>

<body>
<p></p>
<div>
  <table style="font-family: "Century Gothic" ! important; line-height: normal; font-size: 13px; width: 100%;" border="0" cellspacing="0" cellpadding="0" bgcolor="#f5f5f5">
    <tbody>
      <tr>
        <td><table border="0" cellspacing="0" cellpadding="0">
            <tbody>
            </tbody>
          </table>
          <table style="width: 100%;" bgcolor="#f5f5f5">
            <tbody>
              <tr>
                <td align="center"><table style="width: 750px;" border="0" cellspacing="0" cellpadding="0" align="center" bgcolor="#f5f5f5">
                    <tbody>
                      <tr>
                        <td width="10%" height="65" align="left"><div style="min-height: 45px;"><a href="http://projectalert.in/" target="_blank"><img src="http://projectalert.in/assets/frontend/layout/img/logos/logo.png" border="0" alt="" width="227" height="50" /></a></div></td>
                        <td width="90%" align="right" valign="bottom"><span style="font-size: 40px; font-family:Century Gothic; color: #666666;">Payment Failed<br />
                          </span> <span style="font-size: 24px; color: #666666;font-family:Century Gothic;">Project Alert</span>
                          <div><span style="font-size: 11px; line-height: 16px; color: #999999;font-family:Century Gothic;"> '.$billing_name.', this is a payment status notification.<br />
                            </span></div></td>
                      </tr>
                    </tbody>
                  </table>
                  <table style="width: 750px;" border="0" cellspacing="0" cellpadding="0" align="center">
                    <tbody>
                      <tr>
                        <td style="padding: 30px;" width="100%" align="left" bgcolor="#B83334"><span style="font-size: 40px; font-family:Century Gothic; color: #ffffff;">Order #: '.$order_id.'<br />
                          </span> <span style="font-size: 24px; color: #ffffff;font-family:Century Gothic;">Dated: '.$order_date.' </span></td>
                      </tr>
                      <tr>
                        <td style="line-height: 16px;" align="left" valign="top" bgcolor="#ffffff"></td>
                      </tr>
                      <tr>
                        <td valign="top" bgcolor="#ffffff"><table style="width: 100%;" border="0" cellspacing="0" cellpadding="0">
                            <tbody>
                              <tr>
                                <td align="left" valign="top" style="padding: 20px;"><span style="line-height: 20px; color: #000;font-size:15px; font-family:"Century Gothic"">Dear '.$billing_name.',<br />
                                  <br />
                                  Thank you for placing subscription order with us. However the payment has been <strong>FAILED</strong>. Request you to make another purchase. </span></td>
                              </tr>
                              <tr>
                                <td style="padding: 20px; font-size: 15px; color: #000;font-family: "Century Gothic";" align="left" valign="top"><p style="font-size:15px; font-family:"Century Gothic" ">You may review your order history at any time by logging in to your <a href="'.$path.'members/">Client Area</a>.<br />

<strong>Note:</strong> This email will serve as an official receipt for this payment.</p>
                                  <table style="width: 100%;" border="0" cellspacing="0" cellpadding="0" bgcolor="#ffffff">
                                    <tbody>
                                      <tr>
                                        <td align="left"></td>
                                      </tr>
                                      <tr>
                                        <td align="left" style="font-size:15px; font-family:"Century Gothic" ">&nbsp;</td>
                                      </tr>
                                      <tr>
                                        <td align="left" valign="top"></td>
                                      </tr>
                                      <tr>
                                        <td style="font-size: 15px; color: #003a5b;font-family: "Century Gothic";" height="40" align="left" valign="top"><strong>Need help?</strong></td>
                                      </tr>
                                      <tr>
                                        <td align="left"><table style="width: 100%;" border="0" cellspacing="0" cellpadding="0">
                                            <tbody>
                                              <tr>
                                                <td width="18%" align="center"><table style="width: 95%;" cellspacing="0" cellpadding="0" align="center">
                                                    <tbody>
                                                      <tr>
                                                        <td height="35" align="center" style="border:solid 1px #444;color:#111;font-size:11px;">Call: 022-6712-1749</td>
                                                      </tr>
                                                    </tbody>
                                                  </table></td>
                                                <td width="18%" align="center"><table style="width: 95%;" border="0" cellspacing="0" cellpadding="0" align="center">
                                                    <tbody>
                                                      <tr>
                                                        <td style="border: solid 1px #444;" height="35" align="center">Mail: <a style="color: #111; font-size: 15px; text-decoration: none;font-family: "Century Gothic"; " href="mailto:subscriber@projectalert.in" target="_blank">subscriber@projectalert.in</a></td>
                                                      </tr>
                                                    </tbody>
                                                  </table></td>
                                                <td width="18%" align="center"><table style="width: 95%;" border="0" cellspacing="0" cellpadding="0" align="center">
                                                    <tbody>
                                                      <tr>
                                                        <td style="border: solid 1px #444;" height="35" align="center"><a style="color: #111; font-size: 15px; text-decoration: none;font-family: "Century Gothic"; " href="http://projectalert.in/contact-us.php" target="_blank">Contact Us</a></td>
                                                      </tr>
                                                    </tbody>
                                                  </table></td>
                                              </tr>
                                            </tbody>
                                          </table></td>
                                      </tr>
                                    </tbody>
                                  </table></td>
                              </tr>
                              <tr>
                                <td style="padding:0px; font-size: 15px; color: #003a5b;font-family: "Century Gothic";" align="left" valign="top"><table width="750" border="0" align="center" cellpadding="0" cellspacing="0">
                                    <tbody>
                                      <tr>
                                        <td style="line-height: 28px; font-size: 24px; text-align: left; color: #fff; padding: 5px 15px;font-family: "Century Gothic"; " width="64%" align="center" bgcolor="#B83334"><strong>Thanks for being a loyal customer.</strong><br />
                                          <span style="font-size: 18px;font-family: "Century Gothic"; ">know more about our services visit our website.</span><br /></td>
                                        <td style="line-height: 28px; font-size: 24px; text-align: center; color: #fff; padding: 5px 15px 10px;" width="34%" align="center" valign="top" bgcolor="#3E4095"><table style="border: 0px solid; border-collapse: collapse;background: none repeat scroll 0% 0% #0398d2; width: 55%;margin-top:10px;" border="0" cellspacing="0" cellpadding="0" align="center">
                                            <tbody>
                                              <tr>
                                                <td style="font-size: 16px;" height="40" align="center"><a style="font-size: 17px; color: #fff; text-decoration: none;font-family: "Century Gothic"; " href="http://projectalert.in/" target="_blank">Visit Now</a></td>
                                              </tr>
                                            </tbody>
                                          </table></td>
                                      </tr>
                                    </tbody>
                                  </table></td>
                              </tr>
                              <tr>
                                <td style="padding:0px; font-size: 15px; color: #003a5b;font-family: "Century Gothic";" align="left" valign="top"><table width="750" border="0" align="center" cellpadding="0" cellspacing="0" style="width: 750px;">
                                    <tbody>
                                    </tbody>
                                    <tbody>
                                      <tr>
                                        <td style="padding: 10px; padding-left: 20px; line-height: 18px;background-color:#444;" width="43%" align="left" valign="bottom" bgcolor="#444"><span style="font-size: 11px; color: #999999;font-family: "Century Gothic"; "> Economic Research India Pvt. Ltd. <a style="text-decoration: none; color: #fff;font-family: "Century Gothic"; " href="http://projectalert.in/" target="_blank">http://projectalert.in</a><br />
                                          Copyright ?. All rights reserved.</span></td>
                                        <td style="padding: 10px; font-size: 11px; color: #999999;background-color:#444;" width="57%" align="right" valign="bottom" bgcolor="#444">Sterling House, 5/7, Sorabji Santuk Lane, Marine Lines (E), Mumbai-400002.
                                          Email: <a href="mailto:subscriber@projectalert.in">subscriber@projectalert.in</a> Tel: 022-6712-1749 </td>
                                      </tr>
                                    </tbody>
                                  </table></td>
                              </tr>
                            </tbody>
                          </table></td>
                      </tr>
                    </tbody>
                  </table></td>
              </tr>
            </tbody>
          </table>
          <br /></td>
      </tr>
    </tbody>
  </table>
</div>
</body>
</html>';
		
		$isSent = mail($to, $subject, $message, $headers);
		
		$echo_message = "<br>Thank you for shopping with us.However,the transaction has been declined.";
		$message_type = 3;
	}
	else
	{
		$echo_message = "<br>Security Error. Illegal access detected";
		$message_type = 4;
	
	}
	
	
	

	
}
?>
<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8 no-js"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9 no-js"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en">
<!--<![endif]-->

<!-- Head BEGIN -->

<head>
<meta charset="utf-8">
<title>My Orders | Project Alert</title>
<link rel="shortcut icon" href="favicon.ico">

<!-- Fonts START -->
<link href="http://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700|PT+Sans+Narrow|Source+Sans+Pro:200,300,400,600,700,900&amp;subset=all" rel="stylesheet" type="text/css">
<!-- Fonts END -->

<!-- Global styles START -->
<link href="assets/global/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet">
<link href="assets/global/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet">
<!-- Global styles END -->

<!-- Page level plugin styles START -->
<link href="assets/global/plugins/fancybox/source/jquery.fancybox.css" rel="stylesheet">
<link href="assets/global/plugins/uniform/css/uniform.default.css" rel="stylesheet" type="text/css">
<link href="assets/global/plugins/carousel-owl-carousel/owl-carousel/owl.carousel.css" rel="stylesheet">
<!-- Page level plugin styles END -->

<!-- Theme styles START -->
<link href="assets/global/css/components.css" rel="stylesheet">
<link href="assets/frontend/layout/css/style.css" rel="stylesheet">
<link href="assets/frontend/pages/css/style-shop.css" rel="stylesheet" type="text/css">
<link href="assets/frontend/layout/css/style-responsive.css" rel="stylesheet">
<link href="assets/frontend/layout/css/themes/red.css" rel="stylesheet" id="style-color">
<link href="assets/frontend/layout/css/custom.css" rel="stylesheet">
<!-- Theme styles END -->

<!-- orders style -->
<style type="text/css">
#report {
	border-collapse:collapse;
}
#report h4 {
	margin:0px;
	padding:0px;
	font-size:17px;
}
#report a {
	color:#fff;
	float:right;
	margin-right:40px;
}
#report img {
	float:right;
}
#report ul {
	margin:0px;
	padding:0px;
	list-style:none;
}
#report ul li {
	width:100%;
	float:left;
	padding:6px 0;
	border-bottom:1px solid #BBBBBB;
}
#report ul li:last-child {
	border:none;
}
#report th {
	background:#DB2627;
	color:#fff;
	padding:7px 15px;
	text-align:left;
}
#report td {
	background:#DADADA;
	color:#000;
	padding:7px 15px;
	border-bottom:1px solid #c5c5c5;
	font-weight: 600;
}
#report td p {
	margin:0px;
	color:#FFF;
	padding:5px 10px;
	width:82px;
	text-align:center;
}
#report tr.odd td {
	background:#F0F0F0;
	cursor:pointer;
}
#report div.arrow {
	background:transparent url(arrows.png) no-repeat scroll 0px -20px;
	width:20px;
	height:20px;
	display:block;
	margin-top:3px;
}
#report div.up {
	background-position:0px 0px;
}
.bordered {
	box-shadow:none;
	width:90%;
}
</style>
<?php include('inc/pre-body.php'); ?>
</head>
<!-- Head END -->

<!-- Body BEGIN -->
<body class="ecommerce">
<!-- BEGIN TOP BAR -->
<?php include('inc/header.php'); ?>
<!-- Header END -->

<div class="main">
  <div class="container">
    <ul class="breadcrumb">
      <li><a href="../index.php">Project Alert</a></li>
      <li class="active">My Orders</li>
    </ul>
    <!-- BEGIN SIDEBAR & CONTENT -->
    <div class="row margin-bottom-40"> 
      <!-- BEGIN SIDEBAR -->
      <div class="sidebar col-md-3 col-sm-3">
        <?php include('inc/left-menu.php'); ?>
      </div>
      <!-- END SIDEBAR --> 
      
      <!-- BEGIN CONTENT -->
      <div class="col-md-9 col-sm-7">
        <div class="col-md-12 col-sm-12">
          <h1>My Orders</h1>
          <!-- BEGIN CHECKOUT PAGE -->
          <div class="checkout-page" > 
            
            <!-- BEGIN CONFIRM -->
            <div id="confirm-content">
              <div class="panel-body row">
                <?php if($from_pg) {
						$error_style = '';
						if($message_type == 1) $error_style = 'note-success';
						else if($message_type == 2) $error_style = 'note-info';
						else if($message_type == 3) $error_style = 'note-danger';
						else if($message_type == 4) $error_style = 'note-warning';
						echo '<div class="note '.$error_style.'">
								<h4 class="block">'.$order_status.'</h4>
								<p>'.$echo_message.'</p>
							</div>';
						
					}?>
                <div class="col-md-12 clearfix">
                  <div class="table-wrapper-responsive">
                    <table id="report" class="bordered">
                      <tr>
                        <th width="15%">Order ID</th>
                        <th width="10%">Date</th>
                        <th width="15%">Price</th>
                        <th width="14%">Status</th>
                        <th width="6%"></th>
                      </tr>
                      
                      <!--<table>
                      <tr style="color:#FFF;">
                        <th width="30%" class="checkout-model">Order ID</th>
                        <th width="30%" class="checkout-quantity">Date</th>
                        <th width="30%" class="checkout-price">Amount</th>
                        <th width="10%" class="checkout-total">Status</th>
                      </tr> -->
                      <?php 
					    $user_id = $_SESSION['uid'];
					  	$result3 = mysql_query("SELECT *, DATE_FORMAT(date,'%d/%m/%Y') AS date FROM orders WHERE user_id='$user_id' ORDER BY date ASC");
						$num_orders = mysql_num_rows($result3);
						
						if($num_orders > 0) {
							while($orders = mysql_fetch_assoc($result3)) {
								$status_label = 'label-info';
								if($orders['order_status'] == 'Success') $status_label = 'label-success';
								if($orders['order_status'] == 'Aborted') $status_label = 'label-info';
								if($orders['order_status'] == 'Failure') $status_label = 'label-danger';
								
								//prepare the subbscription details on success order
								if($orders['order_status']==="Success") {
								$order_id = $orders['order_id'];
								$result = mysql_query("SELECT orders_subscription.*, subscriptions.title, DATE_FORMAT(orders_subscription.subscription_from,'%d/%m/%Y') AS subscription_from, DATE_FORMAT(orders_subscription.expire_date,'%d/%m/%Y') AS expire_date  
							FROM orders_subscription 
							JOIN subscriptions
							ON orders_subscription.subscription_id = subscriptions.subcription_id
							WHERE orders_subscription.order_id='$order_id'");
								$num_rows = mysql_num_rows($result);
								$subscription_details = '<table class="">
									  <thead>
									  <tr>
										<th>Subscription</td>
										<th>Pricing Slab</td>
										<th>From Date</td>
										<th>Expire Date</td>
									  </tr>
									  </thead>
									  <tbody>';
								if($num_rows > 0) while ($sub_data = mysql_fetch_assoc($result)) {
									$subscription_details .=  '<tr>
										<td>'.$sub_data['title'].'</td>
										<td>'.$sub_data['pricing_slab'].'</td>
										<td>'.$sub_data['subscription_from'].'</td>
										<td>'.$sub_data['expire_date'].'</td>
									  </tr>';
								}
								$subscription_details .=  '<tbody>
									</table>';
								}
								echo '<tr>
										<td class="checkout-quantity">'.$orders['order_id'].'</td>
										<td class="checkout-quantity">'.$orders['date'].'</td>
										<td class="checkout-price"><strong><span>Rs. </span>'.number_format( $orders['amount'], 2, '.', '' ).'</strong></td>
										<td class="checkout-total"><p class="'.$status_label.'" style="color:#FFF; padding:5px 10px;">'.$orders['order_status'].'</p></td>
										<td>'.($orders['order_status']==="Success" ? '<div class="arrow"></div>' : '').'</td>
									  </tr>';
									  
								if($orders['order_status']==="Success") echo '<tr><td colspan="5">'.$subscription_details.'</td></tr>';
								else echo '<tr><td colspan="5"> Order has been '.$orders['order_status'].'</td></tr>';
								
								 
						
								  
							}
						
						}
						else echo "<tr><td colspan='5' class='checkout-model' style='color:#333; line-height: 30px;'>You did't have paced any order! &nbsp;&nbsp;&nbsp; <a class='btn btn-primary' href='".$path."esubscriptions/'  style='color:#FFF; float:none;'>Shop Now</a></td></tr>";
					   
					  ?>
                    </table>
                  </div>
                  <div class="clearfix"></div>
                </div>
                
                <!--<div class="col-md-12 clearfix">
                      <div class="table-wrapper-responsive">
                      <table id="report" class="bordered">
        <tr>
            <th width="40%">Order No.</th>
            <th width="40%">Price</th>
            <th width="14%">Status</th>
            <th width="6%"></th>
        </tr>
        
        <tr>
            <td>0012345</td>
            <td>Rs. 500</td>
            <td><p class="label-success">Success</p></td>
            <td><div class="arrow"></div></td>
        </tr>
        <tr>
            <td colspan="5">
            	<ul>
                	<li>
                		<a href="#">Download</a> <h4>Title comingsoon</h4> 
                	</li>
                    <li>
                		<a href="#">Download</a> <h4>Title comingsoon</h4> 
                	</li>
                </ul> 
            </td>
        </tr>
        
        
        <tr>
            <td>0012345</td>
            <td>Rs. 500</td>
            <td><p class="label-success">Success</p></td>
            <td><div class="arrow"></div></td>
        </tr>
        <tr>
            <td colspan="5">
            	<ul>
                	<li>
                		<a href="#">Download</a> <h4>Title comingsoon</h4> 
                	</li>
                    <li>
                		<a href="#">Download</a> <h4>Title comingsoon</h4> 
                	</li>
                    <li>
                		<a href="#">Download</a> <h4>Title comingsoon</h4> 
                	</li>
                </ul> 
            </td>
        </tr>
        
        <tr>
            <td>0012345</td>
            <td>Rs. 500</td>
            <td><p class="label-success">Success</p></td>
            <td><div class="arrow"></div></td>
        </tr>
        <tr>
            <td colspan="5">
                <a href="#">Download</a>
                <h4>Title comingsoon</h4>  
            </td>
        </tr>
        
    </table>
                      </div>
                      <div class="clearfix"></div>
                    </div> --> 
              </div>
            </div>
            <!-- END CONFIRM --> 
          </div>
          <!-- END CHECKOUT PAGE --> 
        </div>
      </div>
      <!-- END CONTENT --> 
    </div>
    <!-- END SIDEBAR & CONTENT --> 
  </div>
</div>

<!-- BEGIN PRE-FOOTER -->
<?php include('inc/footer.php'); ?>
<!-- END PRE-FOOTER --> 

<!-- Load javascripts at bottom, this will reduce page load time --> 
<!-- BEGIN CORE PLUGINS(REQUIRED FOR ALL PAGES) --> 
<!--[if lt IE 9]>
    <script src="assets/global/plugins/respond.min.js"></script>  
    <![endif]--> 
<script src="assets/global/plugins/jquery.min.js" type="text/javascript"></script> 
<script src="assets/global/plugins/jquery-migrate.min.js" type="text/javascript"></script> 
<script src="assets/global/plugins/bootstrap/js/bootstrap.min.js" type="text/javascript"></script> 
<script src="assets/frontend/layout/scripts/back-to-top.js" type="text/javascript"></script> 
<script src="assets/global/plugins/jquery-slimscroll/jquery.slimscroll.min.js" type="text/javascript"></script> 
<!-- END CORE PLUGINS --> 

<!-- BEGIN PAGE LEVEL JAVASCRIPTS (REQUIRED ONLY FOR CURRENT PAGE) --> 
<script src="assets/global/plugins/fancybox/source/jquery.fancybox.pack.js" type="text/javascript"></script><!-- pop up --> 
<script src="assets/global/plugins/carousel-owl-carousel/owl-carousel/owl.carousel.min.js" type="text/javascript"></script><!-- slider for products --> 
<script src="assets/global/plugins/uniform/jquery.uniform.min.js" type="text/javascript"></script> 
<script src="assets/frontend/layout/scripts/layout.js" type="text/javascript"></script> 
<script type="text/javascript">
        jQuery(document).ready(function() {
            Layout.init();    
            Layout.initOWL();
			Layout.initUniform();
            Layout.initFixHeaderWithPreHeader(); /* Switch On Header Fixing (only if you have pre-header) */
        });
    </script> 
<!-- END PAGE LEVEL JAVASCRIPTS --> 

<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js" type="text/javascript"></script> 
<script type="text/javascript">  
        $(document).ready(function(){
            $("#report tr:odd").addClass("odd");
            $("#report tr:not(.odd)").hide();
            $("#report tr:first-child").show();
            
            $("#report tr.odd").click(function(){
                $(this).next("tr").toggle();
                $(this).find(".arrow").toggleClass("up");
            });
            //$("#report").jExpand();
        });
    </script>
<?php 
//destroy cart details, all session data
if(isset($_SESSION["subscriptions"])) unset($_SESSION["subscriptions"]);
if(isset($_SESSION['error'])) unset($_SESSION['error']);
if(isset($_SESSION['is_error'])) unset($_SESSION['is_error']);
if(isset($_SESSION['user_data'])) unset($_SESSION['user_data']);
if(isset($_SESSION['order_id'])) unset($_SESSION['order_id']);
?>
</body>
<!-- END BODY -->
</html>