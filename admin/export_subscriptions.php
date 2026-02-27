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
				$sql = "SELECT s.subcription_id, s.title, s.price, s.megazine_description, s.slug, s.image_raw, s.image_300, s.image_100, s.alt_text, s.pdf, s.status, s.page_title, s.page_description, s.page_keywords, s.search_terms, s.issue_date, s.ref_issue_title, s.ref_issue_id, s.create_date, s.last_update, s.created_by, s.last_updated_by, s.last_update_ip, u.first_name, u.last_name, DATE_FORMAT(s.issue_date,'%d %b %Y %h:%i %p') AS issue_date_formatted, DATE_FORMAT(s.last_update,'%d %b %Y %h:%i %p') AS last_update_formatted, d.downloads 
		FROM subscriptions s
		JOIN users u
		ON s.last_updated_by = u.user_id 
		LEFT JOIN downloads d
		ON s.subcription_id = d.subcription_id
		WHERE s.create_date >= ? AND s.create_date < ? AND (s.title LIKE ? OR s.megazine_description LIKE ?) 
		ORDER BY s.create_date ASC";
                $search_param = "%$search_term%";
                $stmt = $database->db->prepare($sql);
                $stmt->bind_param("ssss", $from, $to, $search_param, $search_param);
			
				} else if($search_term == '' && ($from != '' && $to != '')) {
					$sql = "SELECT s.subcription_id, s.title, s.price, s.megazine_description, s.slug, s.image_raw, s.image_300, s.image_100, s.alt_text, s.pdf, s.status, s.page_title, s.page_description, s.page_keywords, s.search_terms, s.issue_date, s.ref_issue_title, s.ref_issue_id, s.create_date, s.last_update, s.created_by, s.last_updated_by, s.last_update_ip, u.first_name, u.last_name, DATE_FORMAT(s.issue_date,'%d %b %Y %h:%i %p') AS issue_date_formatted, DATE_FORMAT(s.last_update,'%d %b %Y %h:%i %p') AS last_update_formatted, d.downloads 
		FROM subscriptions s
		JOIN users u
		ON s.last_updated_by = u.user_id 
		LEFT JOIN downloads d
		ON s.subcription_id = d.subcription_id 
		WHERE (s.create_date >= ? AND s.create_date < ?) 
		ORDER BY s.create_date ASC";
                    $stmt = $database->db->prepare($sql);
                    $stmt->bind_param("ss", $from, $to);
					
				} else if($search_term != '' && ($from == '' && $to == '')) {
					$sql = "SELECT s.subcription_id, s.title, s.price, s.megazine_description, s.slug, s.image_raw, s.image_300, s.image_100, s.alt_text, s.pdf, s.status, s.page_title, s.page_description, s.page_keywords, s.search_terms, s.issue_date, s.ref_issue_title, s.ref_issue_id, s.create_date, s.last_update, s.created_by, s.last_updated_by, s.last_update_ip, u.first_name, u.last_name, DATE_FORMAT(s.issue_date,'%d %b %Y %h:%i %p') AS issue_date_formatted, DATE_FORMAT(s.last_update,'%d %b %Y %h:%i %p') AS last_update_formatted, d.downloads 
		FROM subscriptions s
		JOIN users u
		ON s.last_updated_by = u.user_id 
		LEFT JOIN downloads d
		ON s.subcription_id = d.subcription_id
		WHERE (s.title LIKE ? OR s.megazine_description LIKE ?) 
		ORDER BY s.create_date ASC";
                    $search_param = "%$search_term%";
                    $stmt = $database->db->prepare($sql);
                    $stmt->bind_param("ss", $search_param, $search_param);
				}
				else {
					$sql = "SELECT s.subcription_id, s.title, s.price, s.megazine_description, s.slug, s.image_raw, s.image_300, s.image_100, s.alt_text, s.pdf, s.status, s.page_title, s.page_description, s.page_keywords, s.search_terms, s.issue_date, s.ref_issue_title, s.ref_issue_id, s.create_date, s.last_update, s.created_by, s.last_updated_by, s.last_update_ip, u.first_name, u.last_name, DATE_FORMAT(s.issue_date,'%d %b %Y %h:%i %p') AS issue_date_formatted, DATE_FORMAT(s.last_update,'%d %b %Y %h:%i %p') AS last_update_formatted, d.downloads
		FROM subscriptions s
		JOIN users u
		ON s.last_updated_by = u.user_id 
		LEFT JOIN downloads d
		ON s.subcription_id = d.subcription_id
		ORDER BY s.create_date ASC";
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
			 
			 $name=date('d-m-y').'-subscriptions.xls';
			header("Content-type:application/vnd.ms-excel;name='excel'");
			 header("Content-Disposition: attachment; filename=$name");
			 header("Pragma: no-cache");
			 header("Expires: 0");
			
			 // Output data
			 echo $header."\n\n".$data;
			 exit();
}
		
			
?>