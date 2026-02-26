<?php
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


if(isset($_POST['id'], $_POST['csrf_token'])) {
	// Validate CSRF token
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        // Handle CSRF error
        die('Invalid CSRF token.');
    }

	$temp = trim($_POST['id']);
	
	//delete 
	$stmt = $database->db->prepare("DELETE FROM pricing WHERE price_id = ?");
	$stmt->bind_param('i', $temp);
	$stmt->execute();
	$stmt->close();

}
?>