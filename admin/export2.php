<?php
require_once('inc/requires.php');
	
	//create objects
	$database = new MySQLDB();
	$user = new visitor();
	$is_edit = false;
	$menu = 'inc/left-menu-user.php';
	
	//check if the user is not logged in
	if(!$user->check_session())
	{	
		header("location: index.php"); 
		exit();
	} else if ($_SESSION['user_type'] == 'webmaster'){
		$user = new webmaster();
		$menu = 'inc/left-menu-webmaster.php';		
		$wm_first_name = $user->get_wm_first_name();
		$wm_last_name = $user->get_wm_last_name();
		
	} else if ($_SESSION['user_type'] == 'admin'){
		$user = new admin();
		$menu = 'inc/left-menu-admin.php';
	} else {
		$user = new user();
	}

	
			 ob_start();
			 $sql = "SELECT * from web_users";

             $res = $database->db->query($sql) or die();
             $fields = $res->fetch_fields();
			
			 // fetch table header from database
			 $header = '';
			 foreach ($fields as $field) {
                $header .= $field->name . "\t";
             }
			
			 // fetch data each row, store on tabular row data
			 while($row = $res->fetch_row()){
			   $line = '';
			   foreach($row as $value){
			   if(!isset($value) || $value == ""){
				 $value = "\t";
			   }else{
				 $value = str_replace('"', '""', $value);
				 $value = '"' . $value . '"' . "\t";
			   }
			   $line .= $value;
			   }
			   $data .= trim($line)."\n";
			   $data = str_replace("\r", "", $data);
			  }
			 
			 $name=date('d-m-y').'-parents.xls';
			header("Content-type:application/vnd.ms-excel;name='excel'");
			 header("Content-Disposition: attachment; filename=$name");
			 header("Pragma: no-cache");
			 header("Expires: 0");
			
			 // Output data
			 echo $header."\n\n".$data;
			 exit();
?>