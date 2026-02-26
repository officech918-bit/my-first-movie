<?php
	require_once('inc/requires.php');
	
	session_start();

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
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die('Invalid CSRF token');
    }

	$temp = $_POST['id'];
	$temp = trim($temp);
	
	//delete the folder first
    $stmt = $database->db->prepare("SELECT slug FROM subscriptions WHERE subcription_id=?");
    $stmt->bind_param("s", $temp);
    $stmt->execute();
    $result = $stmt->get_result();
	$array = $result->fetch_assoc();
	$slug = $array['slug'];
	
	if($slug != '') {
		$delete_dir = $direct_path.'/esubscriptions/'.$slug;
		 
		
		//delete slug
		if (is_dir($delete_dir)) {
			delete_directory($delete_dir);
		}
	
	}
	
    $stmt = $database->db->prepare("DELETE FROM pricing WHERE subscription_id=?");
    $stmt->bind_param("s", $temp);
    $stmt->execute();

    $stmt = $database->db->prepare("DELETE FROM subscriptions WHERE subcription_id=?");
    $stmt->bind_param("s", $temp);
    $stmt->execute();
}
?>