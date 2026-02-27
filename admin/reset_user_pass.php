<?php
	date_default_timezone_set('Asia/Kolkata');
	$date = date('d-m-Y');
	$time = date("d-m-Y H:i", time());

	//get class files
	include('inc/requires.php');
	
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


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
        http_response_code(400);
        exit('Invalid CSRF token');
    }
    $user_id = trim((string)$_POST['id']);
    if ($user_id === '' || !ctype_digit($user_id)) {
        http_response_code(400);
        exit('Invalid user id');
    }
    $password = $user->rand_string(8);
    $salt = $user->generateSalt();
    $hash = $user->generateHash($password, $salt);
    $database->query("UPDATE users SET salt=?, password=? WHERE user_id=?", [$salt, $hash, (int)$user_id]);
    echo $password;
}
?>
