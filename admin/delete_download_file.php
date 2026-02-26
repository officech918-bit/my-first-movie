<?php
	session_start();
	require_once 'inc/requires.php';
	
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
		$_SESSION['error_message'] = 'CSRF token validation failed.';
		header('Location: all-download-files.php');
    	exit;
	}

	$id = (int)$_POST['id'];

	if ($id > 0) {
		// First, get the file details from the database to unlink it from the server
		$stmt = $database->db->prepare("SELECT file_name FROM download_files WHERE id = ?");
		$stmt->bind_param("i", $id);
		$stmt->execute();
		$result = $stmt->get_result();
		$file = $result->fetch_assoc();
		$stmt->close();

		if ($file) {
			// Construct the full file path and delete the file
			$file_to_delete = $direct_path . 'downloads/' . $file['file_name'];
			if (file_exists($file_to_delete) && !empty($file['file_name'])) {
				unlink($file_to_delete);
			}

			// Delete the record from the download_files table
			$stmt = $database->db->prepare("DELETE FROM download_files WHERE id = ?");
			$stmt->bind_param("i", $id);
			$stmt->execute();
			$stmt->close();

			// Delete related records from the download_files_regions table
			$stmt = $database->db->prepare("DELETE FROM download_files_regions WHERE pdf_id = ?");
			$stmt->bind_param("i", $id);
			$stmt->execute();
			$stmt->close();

			$_SESSION['success_message'] = 'File deleted successfully.';
		} else {
			$_SESSION['error_message'] = 'File not found.';
		}
	} else {
		$_SESSION['error_message'] = 'Invalid ID provided.';
	}

	header('Location: all-download-files.php');
    exit;
}
?>