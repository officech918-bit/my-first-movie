<?php 
class visitor {
	
	protected ?MySQLDB $db = null;
	public $sitename;
	public $sub_location;
	public $admin_location;
	public $path; 
	
	public $from_email;
	public $to_email;
	public $company_name;

	
	public $cc_merchant_id;
	public $cc_working_key;
	public $cc_access_code;
	
	function __construct(?MySQLDB $database = null) {
		if ($database) {
			$this->db = $database;
		} else {
			$this->db = new MySQLDB();
		}
		
		//set default variable for the site
		$this->sitename = $this->get_value('sitename');
		$this->sub_location = $this->get_value('sub_location');
		$this->admin_location = $this->get_value('admin_location');	
		
		$this->from_email = $this->get_value('from_email');	
		$this->to_email = $this->get_value('to_email');
		$this->company_name = $this->get_value('company_name');	
		
		$this->cc_merchant_id = $this->get_value('cc_merchant_id');
		$this->cc_working_key = $this->get_value('cc_working_key');
		$this->cc_access_code = $this->get_value('cc_access_code');	
		 
		if($this->sub_location != "") $this->path = $this->sitename.'/'.$this->sub_location.'/';
		else $this->path = $this->sitename;
	}
	
	function __destruct() {
		
	}
	
	function get_sitename() {
		// Always use dynamic host for proper URL generation
		$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
		if ($host) {
			$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
			
			// For localhost development, include the project folder
			$lowerHost = strtolower($host);
			if (
				$lowerHost === 'localhost' ||
				$lowerHost === '127.0.0.1' ||
				preg_match('/^192\.168\./', $lowerHost) ||
				preg_match('/^10\./', $lowerHost)
			) {
				// Infer the first path segment (e.g. "/myfirstmovie3") so URLs include the project folder
				$script = isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '';
				$basePath = '';
				if ($script) {
					$parts = explode('/', trim($script, '/'));
					if (!empty($parts)) {
						$basePath = '/' . $parts[0];
					}
				}
				return rtrim($scheme.'://'.$host.$basePath, '/');
			}
			
			// For production domains, use the current host with proper scheme
			return $scheme.'://'.$host;
		}
		
		// Fallback to database value if no host detected
		return $this->sitename;
	}
	function get_sub_location() {
		return $this->sub_location;
	}
	function get_admin_location() {
		return $this->admin_location;
	}
	function get_path() {
		$sitename = $this->get_sitename();
		$path = $sitename;
		if($this->sub_location != "") {
			$path .= '/'.$this->sub_location;
		}
		// Ensure there is always one trailing slash
		return rtrim($path, '/') . '/';
	}
	
	function get_from_email() {
		return $this->from_email;
	}
	
	function get_to_email() {
		return $this->to_email;
	}
	function get_company_name() {
		return $this->company_name;
	}
	
	function get_cc_merchant_id() {
		return $this->cc_merchant_id;
	}
	function get_cc_working_key() {
		return $this->cc_working_key;
	}
	function get_cc_access_code() {
		return $this->cc_access_code;
	}
	
	//function to generate randing string
	function rand_string($length) {
    	$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    	return substr(str_shuffle($chars),0,$length);

	}

	
	//function to generate activation code
	function generateActivationCode($salt){
		$pepper = "y8&K35h@PK1f";
		$hash = md5($salt . $pepper);
		return $hash;
	}
	
	function authenticatePassword(){
	}

	//function to check password
	function checkPassword($email, $password){
		global $database;
		$query = "SELECT * FROM web_users WHERE email = ?";
		if ($stmt = $database->db->prepare($query)) {
			$stmt->execute([$email]);
			$user_data = $stmt->fetch(PDO::FETCH_ASSOC);
			
			if($user_data && password_verify($password, $user_data['hash_code'])) {
				return $user_data; // Return user data on success
			}
		}
		return false; // Return false on failure
	}

	//get user ip address
	function getRealIPAddr()
	{
		if (!empty($_SERVER['HTTP_CLIENT_IP']))   //check ip from share internet
		{
			$ip=$_SERVER['HTTP_CLIENT_IP'];
		}
		elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))   //to check ip is pass from proxy
		{
			$ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
		}
		else
		{
			$ip=$_SERVER['REMOTE_ADDR'];
		}
		return $ip;
	}

	//convert number to month name
	function num2month($num) {
			switch ($num) {
			case "01":
				return "January";
				break;
			case "02":
				return "February";
				break;
			case "03":
				return "March";
				break;
			case "04":
				return "April";
				break;
			case "05":
				return "May";
				break;
			case "06":
				return "June";
				break;
			case "07":
				return "July";
				break;
			case "08":
				return "August";
				break;
			case "09":
				return "September";
				break;
			case "10":
				return "October";
				break;
			case "11":
				return "November";
				break;
			case "12":
				return "December";
				break;	
			}
		}

	//function to generate unique id with user prefix
	function generateUniquePrefixID($prefix){
		return uniqid($prefix."_", true);
	
	}

	//function to generate unique id
	function generateUniqueID(){
		return uniqid(true);
	}
	//This function reads the extension of the file. It is used to determine if the file  is an image by checking the extension.
	function getExtension($str) {
			 $i = strrpos($str,".");
			 if (!$i) { return ""; }
			 $l = strlen($str) - $i;
			 $ext = substr($str,$i+1,$l);
			 return $ext;
	 }

	//compare dates
	public function compareDates($date, $expDate) {
		if ($date == $expDate) {  
		return 0;  
		} else if ($date < $expDate) {  
		return 1;   
		} else if ($date > $expDate) {  
		return -1;  }
	}

	//function to calculate difference between two dates or unix timestamp
  	// Time format is UNIX timestamp or
  	// PHP strtotime compatible strings
	public function dateDiff($time1, $time2, $precision = 6) {
		
    // If not numeric then convert texts to unix timestamps
    if (!is_int($time1)) {
      $time1 = strtotime($time1);
    }
    if (!is_int($time2)) {
      $time2 = strtotime($time2);
    }
 
    // If time1 is bigger than time2
    // Then swap time1 and time2
    if ($time1 > $time2) {
      $ttime = $time1;
      $time1 = $time2;
      $time2 = $ttime;
    }
 
    // Set up intervals and diffs arrays
    $intervals = array('year','month','day','hour','minute','second');
    $diffs = array();
 
    // Loop thru all intervals
    foreach ($intervals as $interval) {
      // Set default diff to 0
      $diffs[$interval] = 0;
      // Create temp time from time1 and interval
      $ttime = strtotime("+1 " . $interval, $time1);
      // Loop until temp time is smaller than time2
      while ($time2 >= $ttime) {
	$time1 = $ttime;
	$diffs[$interval]++;
	// Create new temp time from time1 and interval
	$ttime = strtotime("+1 " . $interval, $time1);
      }
    }
 
    $count = 0;
    $times = array();
    // Loop thru all diffs
    foreach ($diffs as $interval => $value) {
      // Break if we have needed precission
      if ($count >= $precision) {
	break;
      }
      // Add value and interval 
      // if value is bigger than 0
      if ($value > 0) {
	// Add s if value is not 1
	if ($value != 1) {
	  $interval .= "s";
	}
	// Add value and interval to times array
	$times[] = $value . " " . $interval;
	$count++;
      }
    }
 
    // Return string with times
    return implode(", ", $times);
	}

	//get the current page name
	public function currentPageName() {
 		return substr($_SERVER["SCRIPT_NAME"],strrpos($_SERVER["SCRIPT_NAME"],"/")+1);
	}
	
	//get date & time provding timezone
	public function getTimeZoneDateTime($GMT){
		$timestamp=time();
    	$timezones = array(
        '-12'=>'Pacific/Kwajalein',
        '-11'=>'Pacific/Samoa',
        '-10'=>'Pacific/Honolulu',
        '-9'=>'America/Juneau',
        '-8'=>'America/Los_Angeles',
        '-7'=>'America/Denver',
        '-6'=>'America/Mexico_City',
        '-5'=>'America/New_York',
        '-4'=>'America/Caracas',
        '-3.5'=>'America/St_Johns',
        '-3'=>'America/Argentina/Buenos_Aires',
        '-2'=>'Atlantic/Azores',// no cities here so just picking an hour ahead
        '-1'=>'Atlantic/Cape_Verde',
        '0'=>'Europe/London',
        '1'=>'Europe/Paris',
        '2'=>'Europe/Helsinki',
        '3'=>'Europe/Moscow',
        '3.5'=>'Asia/Tehran',
        '4'=>'Asia/Baku',
        '4.5'=>'Asia/Kabul',
        '5'=>'Asia/Karachi',
        '5.5'=>'Asia/Calcutta',
        '6'=>'Asia/Colombo',
        '7'=>'Asia/Bangkok',
        '8'=>'Asia/Singapore',
        '9'=>'Asia/Tokyo',
        '9.5'=>'Australia/Darwin',
        '10'=>'Pacific/Guam',
        '11'=>'Asia/Magadan',
        '12'=>'Asia/Kamchatka'
    );
    	date_default_timezone_set($timezones[$GMT]); //ensures that whenever i return this date, i will get central time
    	if ($GMT == -2) $timestamp -= 3600; //since i set that an hour ahead, im subtracting the extra hour now
    	return date('Y-m-d H:i:s', $timestamp);
	} 
	
	//get timezone using GMT
	public function getTimeZonebyGMT($GMT){
    	$timezones = array(
        '-12'=>'Pacific/Kwajalein',
        '-11'=>'Pacific/Samoa',
        '-10'=>'Pacific/Honolulu',
        '-9'=>'America/Juneau',
        '-8'=>'America/Los_Angeles',
        '-7'=>'America/Denver',
        '-6'=>'America/Mexico_City',
        '-5'=>'America/New_York',
        '-4'=>'America/Caracas',
        '-3.5'=>'America/St_Johns',
        '-3'=>'America/Argentina/Buenos_Aires',
        '-2'=>'Atlantic/Azores',// no cities here so just picking an hour ahead
        '-1'=>'Atlantic/Cape_Verde',
        '0'=>'Europe/London',
        '1'=>'Europe/Paris',
        '2'=>'Europe/Helsinki',
        '3'=>'Europe/Moscow',
        '3.5'=>'Asia/Tehran',
        '4'=>'Asia/Baku',
        '4.5'=>'Asia/Kabul',
        '5'=>'Asia/Karachi',
        '5.5'=>'Asia/Calcutta',
        '6'=>'Asia/Colombo',
        '7'=>'Asia/Bangkok',
        '8'=>'Asia/Singapore',
        '9'=>'Asia/Tokyo',
        '9.5'=>'Australia/Darwin',
        '10'=>'Pacific/Guam',
        '11'=>'Asia/Magadan',
        '12'=>'Asia/Kamchatka'
    );
    	return $timezones[trim($GMT)]; 
	}
	public function getDateByTimezone($tz){
		$timestamp=time();
		date_default_timezone_set($tz); 
    	return date('Y-m-d', $timestamp);
	}
	public function getDateTimeByTimezone($tz){
		$timestamp=time();
		date_default_timezone_set($tz); 
    	return date('Y-m-d H:i:s', $timestamp);
	}
	
	function checkaccesscontrol($access, $page){
		if(isset($_SESSION['access_control_array'])){
			$access_control = $_SESSION['access_control_array'];
			if($access_control[$page][$access] == 1){
				return "allowed";
			} else {
				header("location: access-denied.php"); 
				exit();
			}
		} else {
			// If access control is not in session, deny access
			header("location: access-denied.php"); 
			exit();
		}
	}
	
	//function to check the user session is active
	public function check_session() {
		if (isset($_SESSION['expire']) && time() > $_SESSION['expire']) {
			unset($_SESSION['uid'], $_SESSION['email'], $_SESSION['user_type'], $_SESSION['expire'], $_SESSION['start']);
			return false;
		}
		
		if (isset($_SESSION['uid'], $_SESSION['email'])) {
			// Refresh the session expiration on activity
			$_SESSION['expire'] = time() + (30 * 60);
			return true;
		}
		
		return false;
	}
	
	public function create_session($uid, $email, $user_type) {
		$_SESSION['uid'] = $uid;
		$_SESSION['email'] = $email;
		$_SESSION['user_type'] = $user_type;
		
		$_SESSION['start'] = time(); // taking now logged in time
		$_SESSION['expire'] = time() + (30 * 60); // ending a session in 30     minutes from the starting time
		
		//upddate database for last login date & time, Ip address
		global $database;
		$date = date("Y-m-d H:i:s", time());
		$ip = $this->getRealIPAddr();
		
		$query = "UPDATE web_users SET ip=?, last_login=? WHERE uid=? AND email=?";
		if ($stmt = $database->db->prepare($query)) {
			$stmt->execute([$ip, $date, $uid, $email]);
		}
	}
	
	public function update_session_email($email)
	{
		$_SESSION['email'] = $email;
		
		$_SESSION['start'] = time(); // taking now logged in time
		$_SESSION['expire'] = $_SESSION['start'] + (30 * 60) ; // ending a session in 30     minutes from the starting time
	}
	
			
	//process logout
	public function destroy_session(){
		if(isset($_SESSION['uid'])){unset($_SESSION['uid']);}
		if(isset($_SESSION['email'])){unset($_SESSION['email']);}
		session_destroy();
	}
	
	function isActive() {
		if (!isset($_SESSION['uid']) || !isset($_SESSION['email'])) {
			return false;
		}
		$userUniqueID = $_SESSION['uid'];
		$sessionEmail = $_SESSION['email'];
		try {
			$stmt = $this->db->db->prepare("SELECT activation_status FROM web_users WHERE uid = ? AND email = ?");
			if ($stmt) {
				$stmt->execute([$userUniqueID, $sessionEmail]);
				$result = $stmt->fetch(PDO::FETCH_ASSOC);
				return $result && (int)$result['activation_status'] === 1;
			}
		} catch (PDOException $e) {
			// Log error or handle as needed
		}
		return false;
	}
	
	function isApproved() {
		if (!isset($_SESSION['uid']) || !isset($_SESSION['email'])) {
			return false;
		}
		$userUniqueID = $_SESSION['uid'];
		$sessionEmail = $_SESSION['email'];
		try {
			$stmt = $this->db->db->prepare("SELECT admin_approved FROM web_users WHERE uid = ? AND email = ?");
			if ($stmt) {
				$stmt->execute([$userUniqueID, $sessionEmail]);
				$result = $stmt->fetch(PDO::FETCH_ASSOC);
				return $result && (int)$result['admin_approved'] === 1;
			}
		} catch (PDOException $e) {
			// Log error or handle as needed
		}
		return false;
	}
	
	function get_value($variable) {
		try {
			$stmt = $this->db->db->prepare("SELECT value FROM configs WHERE variable = ?");
			if ($stmt) {
				$stmt->execute([$variable]);
				$result = $stmt->fetch(PDO::FETCH_ASSOC);
				return $result ? $result['value'] : null;
			}
		} catch (PDOException $e) {
			// Log error or handle as needed
			return null;
		}
		return null;
	}
	function toSlug($string,$space="-") {
		if (function_exists('iconv')) {
			$string = @iconv('UTF-8', 'ASCII//TRANSLIT', $string);
		}
		$string = preg_replace("/[^a-zA-Z0-9 -]/", "", $string);
		$string = strtolower($string);
		$string = str_replace(" ", $space, $string);
		return $string;
	}
	
	function subscription_is_active($user_id, $subscription_id) {
		date_default_timezone_set('Asia/Kolkata');
		$date = date("Y-m-d H:i:s");
		$is_active = false;
	
		try {
			// Check for single issue subscription
			$stmt1 = $this->db->db->prepare("SELECT o.order_status FROM orders_subscription os JOIN orders o ON os.order_id = o.order_id WHERE os.subscription_id = ? AND os.user_id = ? AND os.pricing_slab = 'Single Issue' AND os.expire_date >= ?");
			if ($stmt1) {
				$stmt1->execute([$subscription_id, $user_id, $date]);
				$results1 = $stmt1->fetchAll(PDO::FETCH_ASSOC);
				foreach ($results1 as $data) {
					if ($data['order_status'] === 'Success') {
						return true; // Early return
					}
				}
			}
	
			// Get issue date for the subscription
			$stmt2 = $this->db->db->prepare("SELECT issue_date FROM subscriptions WHERE subcription_id = ?");
			$temp_issue_date = null;
			if ($stmt2) {
				$stmt2->execute([$subscription_id]);
				$r2 = $stmt2->fetch(PDO::FETCH_ASSOC);
				if ($r2) {
					$temp_issue_date = $r2['issue_date'];
				}
			}
	
			// Check for other types of subscriptions
			$stmt3 = $this->db->db->prepare("SELECT os.subscription_id, os.expire_date, os.subscription_from, o.order_status FROM orders_subscription os JOIN orders o ON os.order_id = o.order_id WHERE os.user_id = ? AND os.pricing_slab != 'Single Issue' AND os.expire_date >= ?");
			if ($stmt3) {
				$stmt3->execute([$user_id, $date]);
				$results3 = $stmt3->fetchAll(PDO::FETCH_ASSOC);
				foreach ($results3 as $data) {
					if ($data['order_status'] === 'Success') {
						if ($data['subscription_id'] == $subscription_id) {
							return true; // Early return
						}
						if ($temp_issue_date) {
							$expire_date_timestamp = strtotime($data['expire_date']);
							$subscription_from_timestamp = strtotime($data['subscription_from']);
							$temp_issue_date_timestamp = strtotime($temp_issue_date);
							if ($temp_issue_date_timestamp >= $subscription_from_timestamp && $temp_issue_date_timestamp <= $expire_date_timestamp) {
								return true; // Early return
							}
						}
					}
				}
			}
		} catch (PDOException $e) {
			// Log error or handle as needed
		}
	
		return $is_active;
	}
	
}

class web_user extends visitor {
	private $activation_code;
	private $activation_status;
	private $activation_time;
	private $activation_expire_time;
	private $avatar;
	private $avatar_thumb;
	private $avatar_path;
	private $contact;
	private $city;
	private $state;
	private $dob_date;
	private $dob_month;
	private $dob_year;
	private $email;
	private $first_name;
	private $last_name;
	private $gender;
	private $hash_code;
	private $salt;
	private $status;
	private $uid;
	private $billing_address;
	private $newsletter;
	private $company;
	private $region;
	private $about_me;
	
	public $from_email;
	public $to_email;
	public $company_name;
	
	public $cc_merchant_id;
	public $cc_working_key;
	public $cc_access_code;
	
	
	function __construct() {
        parent::__construct();
        if (isset($_SESSION['uid']) && isset($_SESSION['email'])) {
			$userUniqueID = $_SESSION['uid'];
			$sessionEmail = $_SESSION['email'];
			
			$stmt = $this->db->db->prepare("SELECT * FROM web_users WHERE uid = ? AND email = ?");
			if ($stmt) {
				$stmt->execute([$userUniqueID, $sessionEmail]);
				$array = $stmt->fetch(PDO::FETCH_ASSOC);
			} else {
				$array = null;
			}
			
			if ($array) {
				$this->activation_code = $array['activation_code'];
				$this->activation_link = $array['activation_link'];
				$this->activation_status = $array['activation_status'];
				$this->activation_time = $array['activation_time'];
				$this->avatar = $array['avatar'];
				$this->avatar_thumb = $array['avatar_thumb'];
				$this->avatar_path = $array['avatar_path'];
				$this->contact = $array['contact'];
				$this->email = $array['email'];
				$this->first_name = $array['first_name'];
				$this->last_name = $array['last_name'];
				$this->gender = $array['gender'];
				$this->hash_code = $array['hash_code'];
				$this->salt = $array['salt'];
				$this->status = $array['status'];
				$this->address = $array['address'];
				$this->city = $array['city'];
				$this->state = $array['state'];
				$this->zip = $array['zip'];
				$this->country = $array['country'];
				$this->company = $array['company'];
				$this->newsletter = $array['newsletter'];
				$this->region = $array['region'];
				$this->about_me = $array['about_me'];
			}
		}
		
		$this->sitename = parent::get_value('sitename');
		$this->sub_location = parent::get_value('sub_location');
		$this->admin_location = parent::get_value('admin_location');
		
		$this->from_email = parent::get_value('from_email');	
		$this->to_email = parent::get_value('to_email');
		$this->company_name = parent::get_value('company_name');
		
		$this->cc_merchant_id = parent::get_value('cc_merchant_id');
		$this->cc_working_key = parent::get_value('cc_working_key');
		$this->cc_access_code = parent::get_value('cc_access_code');
		
	}
	
	function get($variable) {
		return $this->$variable;
	}

	public function generateHash($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }
}
?>