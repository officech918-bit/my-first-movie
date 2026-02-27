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
	$sql = "SELECT downloads.*, subscriptions.title, web_users.email, DATE_FORMAT(downloads.donwload_date,'%d %b %Y %h:%i %p') AS donwload_date
		FROM downloads 
		JOIN web_users 
		ON downloads.user_id = web_users.uid 
		JOIN subscriptions
		ON downloads.subcription_id = subscriptions.subcription_id";
	
	$where_clauses = array();
	$params = array();
	$types = '';

	if ($from != '' && $to != '') {
		$where_clauses[] = "downloads.donwload_date >= ? AND downloads.donwload_date < ?";
		$params[] = &$from;
		$params[] = &$to;
		$types .= 'ss';
	}

	if ($search_term != '') {
		$where_clauses[] = "(downloads.order_id LIKE ? OR subscriptions.title LIKE ? OR web_users.email LIKE ?)";
		$search_param = "%{$search_term}%";
		$params[] = &$search_param;
		$params[] = &$search_param;
		$params[] = &$search_param;
		$types .= 'sss';
	}

	if (!empty($where_clauses)) {
		$sql .= " WHERE " . implode(' AND ', $where_clauses);
	}

	$sql .= " ORDER BY downloads.donwload_date ASC";

	$stmt = $database->db->prepare($sql);

	if (!empty($params)) {
		call_user_func_array(array($stmt, 'bind_param'), array_merge(array($types), $params));
	}

	$stmt->execute();
	$res = $stmt->get_result();

	// fetch table header from database
	$header = '';
	if ($res->num_rows > 0) {
		$fields = $res->fetch_fields();
		foreach ($fields as $field) {
			$header .= $field->name . "\t";
		}
	}

	// fetch data each row, store on tabular row data
	$data = '';
	while ($row = $res->fetch_row()) {
		$line = '';
		foreach ($row as $value) {
			if (!isset($value) || $value == "") {
				$value = "\t";
			} else {
				$value = str_replace('"', '""', $value);
				$value = '"' . $value . '"' . "\t";
			}
			$line .= $value;
		}
		$data .= trim($line) . "\n";
	}
	$data = str_replace("\r", "", $data);
			 
			 $name=date('d-m-y').'-downloads.xls';
			header("Content-type:application/vnd.ms-excel;name='excel'");
			 header("Content-Disposition: attachment; filename=$name");
			 header("Pragma: no-cache");
			 header("Expires: 0");
			
			 // Output data
			 echo $header."\n\n".$data;
			 exit();
}
		
			
?>