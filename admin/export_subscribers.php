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

if(isset($_POST['export_data'])) {
	$search_term = $_POST['search_data'];
	$from = $_POST['from'];
	$to = $_POST['to'];
			
	if($from != '' && $to != '') {
		$pieces = explode("/", $from);
		$from = $pieces[2].'-'.$pieces[1].'-'.$pieces[0].' 00:00:00';
		
		$pieces = explode("/", $to);
		$to = $pieces[2].'-'.$pieces[1].'-'.$pieces[0].' 00:00:00';
		$to = date('Y-m-d H:i:s', strtotime($to . ' + 1 day'));
	}

 ob_start();
			 if($search_term != '' && ($from != '' && $to != '')) {
				$sql = "SELECT *, DATE_FORMAT(create_date,'%d/%m/%Y') AS create_date FROM web_users WHERE create_date >= '$from' AND create_date < '$to' AND (first_name LIKE '%$search_term%' OR last_name LIKE '%$search_term%') ORDER BY create_date ASC";
			
				} else if($search_term == '' && ($from != '' && $to != '')) {
					$sql = "SELECT *, DATE_FORMAT(create_date,'%d/%m/%Y') AS create_date FROM web_users WHERE (create_date >= '$from' AND create_date < '$to') ORDER BY create_date ASC";
					
				} else if($search_term != '' && ($from == '' && $to == '')) {
					$sql = "SELECT *, DATE_FORMAT(create_date,'%d/%m/%Y') AS create_date FROM web_users WHERE (first_name LIKE '%$search_term%' OR last_name LIKE '%$search_term%') ORDER BY create_date ASC";
				}
				else {
					$sql = "SELECT *, DATE_FORMAT(create_date,'%d/%m/%Y') AS create_date FROM web_users ORDER BY create_date ASC";
				}
		
			 //$sql = "SELECT * from web_users";

			 $res = mysql_query( $sql) or die();
			 $count = mysql_num_fields($res);
			
			 // fetch table header from database
			 $header = '';
			 for ($i = 0; $i < $count; $i++){
				$header .= mysql_field_name($res, $i)."\t";
				}
			
			 // fetch data each row, store on tabular row data
			 while($row = mysql_fetch_row($res)){
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
			 
			 $name=date('d-m-y').'-webusers.xls';
			header("Content-type:application/vnd.ms-excel;name='excel'");
			 header("Content-Disposition: attachment; filename=$name");
			 header("Pragma: no-cache");
			 header("Expires: 0");
			
			 // Output data
			 echo $header."\n\n".$data;
			 exit();
}
		
			
?>