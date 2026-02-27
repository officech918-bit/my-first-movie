<?php
	//get class files
	require_once('inc/requires.php');
	
	//create objects
	$database = new MySQLDB();
	$user = new visitor();
	$is_edit = false;
	
	//check if the user is not logged in
	if(!$user->check_session())
	{	
		header("location: index.php"); 
		exit();
	} else if ($_SESSION['user_type'] == 'webmaster'){
		$user = new webmaster();
			
		$wm_first_name = $user->get_wm_first_name();
		$wm_last_name = $user->get_wm_last_name();
		
	} else if ($_SESSION['user_type'] == 'admin'){
		$user = new admin();
	} else {
		$user = new user();
	}
	
	$path = "";
	$direct_path = "";
	if($sub_location != ""){
		$path = $sitename.'/'.$sub_location.'/';
		$direct_path = $_SERVER['DOCUMENT_ROOT'].'/'.$sub_location.'/';		
	}
	else {
		$path = $sitename.'/';
		$direct_path = $_SERVER['DOCUMENT_ROOT'].'/';
	}


if(isset($_POST['id'])) {
	$temp = $_POST['id'];
	$temp = trim($temp);
	//$pieces = explode("-", $temp);
	//$order_id = $pieces[0]; // piece1
	//$table = $pieces[1]; // piece2
	//$order_id = trim($order_id);
	//$table = trim($table);
	
	$query = "DELETE FROM web_users WHERE uid='$temp'";
	//$query = "DELETE * FROM admin WHERE user_id='$temp'";
	$result = mysql_query($query);

}
?>