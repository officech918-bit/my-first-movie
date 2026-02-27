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
				$sql = "SELECT *, DATE_FORMAT(date,'%d/%m/%Y %H:%m') AS date_formatted FROM orders WHERE date >= ? AND date < ? AND (order_id LIKE ? OR amount LIKE ? OR billing_name LIKE ? OR billing_email LIKE ? OR billing_tel LIKE ? OR billing_address LIKE ? OR billing_city LIKE ? OR billing_state LIKE ? OR billing_zip LIKE ? OR billing_country LIKE ? OR tracking_id LIKE ? OR bank_ref_no LIKE ? OR order_status LIKE ? OR payment_mode LIKE ? OR status_message LIKE ?) ORDER BY date ASC";
                $search_param = "%$search_term%";
                $stmt = $database->db->prepare($sql);
                $stmt->bind_param("sssssssssssssssss", $from, $to, $search_param, $search_param, $search_param, $search_param, $search_param, $search_param, $search_param, $search_param, $search_param, $search_param, $search_param, $search_param, $search_param, $search_param, $search_param);
			
				} else if($search_term == '' && ($from != '' && $to != '')) {
					$sql = "SELECT *, DATE_FORMAT(date,'%d/%m/%Y %H:%m') AS date_formatted FROM orders WHERE date >= ? AND date < ? ORDER BY date ASC";
                    $stmt = $database->db->prepare($sql);
                    $stmt->bind_param("ss", $from, $to);
					
				} else if($search_term != '' && ($from == '' && $to == '')) {
					$sql = "SELECT *, DATE_FORMAT(date,'%d/%m/%Y %H:%m') AS date_formatted FROM orders WHERE (order_id LIKE ? OR amount LIKE ? OR billing_name LIKE ? OR billing_email LIKE ? OR billing_tel LIKE ? OR billing_address LIKE ? OR billing_city LIKE ? OR billing_state LIKE ? OR billing_zip LIKE ? OR billing_country LIKE ? OR tracking_id LIKE ? OR bank_ref_no LIKE ? OR order_status LIKE ? OR payment_mode LIKE ? OR status_message LIKE ?) ORDER BY date ASC";
                    $search_param = "%$search_term%";
                    $stmt = $database->db->prepare($sql);
                    $stmt->bind_param("sssssssssssssss", $search_param, $search_param, $search_param, $search_param, $search_param, $search_param, $search_param, $search_param, $search_param, $search_param, $search_param, $search_param, $search_param, $search_param, $search_param);
				}
				else {
					$sql = "SELECT *, DATE_FORMAT(date,'%d/%m/%Y %H:%m') AS date_formatted FROM orders ORDER BY date ASC";
                    $stmt = $database->db->prepare($sql);
				}
		
			 //$sql = "SELECT * from web_users";

             $stmt->execute();
             $res = $stmt->get_result();
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
			 
			 $name=date('d-m-y').'-orders.xls';
			header("Content-type:application/vnd.ms-excel;name='excel'");
			 header("Content-Disposition: attachment; filename=$name");
			 header("Pragma: no-cache");
			 header("Expires: 0");
			
			 // Output data
			 echo $header."\n\n".$data;
			 exit();
}
		
			
?>