<?php
	require_once('../inc/requires.php');
	
	if(isset($_POST['submit'])) {
		
		$name = $_POST['name'];
		$email = $_POST['email'];		
		$contact = $_POST['contact'];		
		$message = $_POST['message'];

		$is_error = false;
		
		if(($name === '') && ($email == '') && ($contact == '')) {
			$is_error = TRUE;
			$error .= "Please Enter your Name, Email, Contact & Message | ";

		} 
		
	
		$userIP = $_SERVER["REMOTE_ADDR"];
		$recaptchaResponse = $_POST['g-recaptcha-response'];
		$secretKey = "6Lf72QgTAAAAAJ2RSYyjj3KljDV-kN1pxo7TvOBg";
		$request = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret={$secretKey}&response={$recaptchaResponse}&remoteip={$userIP}");
	
		if(!strstr($request, "true")){
			$error .= "Captcha Verification Failed!";
			$is_error = true;
		}
	
	
		
		//process data
		if(!$is_error) {
			//send confirmation mail to admin
			$to = 'support@dolphinrfid.in';
			//$to = 'support@aaravinfotech.com';
			$from = "info@dolphinrfid.in";
			$subject = 'Enquiry from website - dolphinrfid.in';
			// To send the HTML mail we need to set the Content-type header.		
			$headers = "From: info@dolphinrfid.in\r\n";
			$headers .= "Reply-To: info@dolphinrfid.in\r\n";
			$headers .= "MIME-Version: 1.0\r\n";
			$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
			//options to send to cc+bcc
			//$headers .= "Cc: [email]maa@p-i-s.cXom[/email]";
			//$headers .= "Bcc: [email]email@maaking.cXom[/email]";
	
			//begin of HTML message
			$mail_data = '<html><body>'; 
			$mail_data .= '<p><strong>Name: </strong>'.$name.'</p>';
			$mail_data .= '<p><strong>Email: </strong>'.$email.'</p>';
			$mail_data .= '<p><strong>Contact: </strong>'.$contact.'</p>';			
			$mail_data .= '<p><strong>IP: </strong>'.$userIP.'</p>';
			$mail_data .= '<p><strong>Message: </strong>'.$message.'</p>';
	
		
			$mail_data .= '</body></html>';
			//end of message 
		
			// now lets send the email.
			$isSent = mail($to, $subject, $mail_data, $headers);
		
		
	
			if($isSent) $_SESSION['message_string'] = "Thank you for placing requirement with us, we will get back to you at earliest.";
			else $_SESSION['message_string'] = "We are unable to process your request due to some technical error, please email your requirement to subscriber@dolphinrfid.in";
			
				
		}
			

	
	
		if($is_error) {
			$_SESSION['is_error'] = $is_error;
			$_SESSION['error_str'] = $error;
		}
		//mysql_close();
		//header("location: message.php"); 
		header('Location: ' . $_SERVER['HTTP_REFERER']);
		exit();
		
		
	}
	
	
	
	
?>