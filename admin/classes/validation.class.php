<?php
/**
 * @deprecated This class is deprecated and will be removed in a future version. Use Validator instead.
 */
class valid
{
	/**
	 * Checks the length of a value
	 *
	 * @param string  $value The value to check
	 * @param integer $maxLength The maximum allowable length of the value
	 * @param integer $minLength [Optional] The minimum allowable length
	 * @return boolean TRUE if the requirements are met, FALSE if not
	 */
	public static function checkLength($value, $maxLength, $minLength = 0)
	{
		if (!(strlen($value) > $maxLength) && !(strlen($value) < $minLength)) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Compares two values for equality
	 *
	 * @param string  $value1 First value to compare
	 * @param string  $value2 Second value to compare
	 * @param boolean $caseSensitive [Optional] TRUE if compare is case sensitive
	 * @return boolean TRUE if the values are equal and FALSE if not
	 */
	public static function compare($value1, $value2, $caseSensitive = false)
	{
		if ($caseSensitive) {
			return ($value1 ==  $value2 ? true : false);
		} else {
			if (strtoupper($value1) ==  strtoupper($value2)) {
				return true;
			} else {
				return false;
			}
		}
	}

	/**
	 * Converts any value of any datatype into boolean (true or false)
	 *
	 * @param any $value Value to analyze for TRUE or FALSE
	 * @param any $includeTrueValue (Optional) return TRUE if the value equals this
	 * @param any $includeFalseValue (Optional) return FALSE if the value equals this
	 * @return boolean Returns TRUE or FALSE
	 */
	public static function getBooleanValue($value, $includeTrueValue = null, $includeFalseValue = null) {

		if (!(is_null($includeTrueValue)) && $value == $includeTrueValue) {
			return true;
		} elseif (!(is_null($includeFalseValue)) && $value == $includeFalseValue) {
			return false;
		} else {
			if (gettype($value) == "boolean") {
				if ($value == true) {
					return true;
				} else {
					return false;
				}
			} elseif (is_numeric($value)) {
				if ($value > 0) {
					return true;
				} else {
					return false;
				}
			} else {
				$cleaned = strtoupper(trim($value));

				if ($cleaned == "ON") {
					return true;
				} elseif ($cleaned == "SELECTED" || $cleaned == "CHECKED") {
					return true;
				} elseif ($cleaned == "YES" || $cleaned == "Y") {
					return true;
				} elseif ($cleaned == "TRUE" || $cleaned == "T") {
					return true;
				} else {
					return false;
				}
			}
		}
	}

	/**
	 * Get the value for a cookie by the cookie name
	 *
	 * @param string  $name The name of the cookie
	 * @param string  $default (Optional) A default if the value is empty
	 * @return string The cookie value
	 */
	public static function getCookieValue($name, $default = '')
	{
		if (isset($_COOKIE[$name]))
		{
			return $_COOKIE[$name];
		} else {
			return $default;
		}
	}

	/**
	 * Returns the name of the current page
	 *
	 * @return string The page name
	 */
	public static function getCurrentPageName($lowercase = false) {
		$return = substr($_SERVER["SCRIPT_NAME"],
				 strrpos($_SERVER["SCRIPT_NAME"], "/") + 1);
		return ($lowercase ? strtolower($return) : $return);
	}

	/**
	 * Returns the name of the current URL
	 *
	 * @return string The URL path
	 */
	public static function getCurrentPageURL($lowercase = false) {
		$pageURL = 'http';
		if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
		$pageURL .= "://";
		if ($_SERVER["SERVER_PORT"] != "80") {
			$pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
		} else {
			$pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
		}
		return ($lowercase ? strtolower($pageURL) : $pageURL);
	}

	/**
	 * Returns the value if one exists, otherwise returns a default value
	 * (This also works on NULL values)
	 *
	 * @param string  $name The value to check
	 * @param string  $default A default if the value is empty
	 * @return string Returns the original value unless this value is
	 *                empty - in which the default is returned
	 */
	public static function getDefaultOnEmpty($value, $default) {
		if (self::hasValue($value)) {
			return $value;
		} else {
			return $default;
		}
	}

	/**
	 * Get a POST or GET value by a form element name
	 *
	 * @param string  $name The name of the POST or GET data
	 * @param string  $default (Optional) A default if the value is empty
	 * @return string The value of the form element
	 */
	public static function getFormValue($name, $default = '')
	{
		if (isset($_POST[$name]))
		{
			return $_POST[$name];
		} else {
			if (isset($_GET[$name]))
			{
				return $_GET[$name];
			} else {
				return $default;
			}
		}
	}

	/**
	 * Get the value for a request
	 *
	 * @param string  $name The name of the request item
	 * @param string  $default (Optional) A default if the value is empty
	 * @return string The request value
	 */
	public static function getRequestValue($name, $default = '')
	{
		if (isset($_REQUEST[$name]))
		{
			return $_REQUEST[$name];
		} else {
			return $default;
		}
	}

	/**
	 * Get the value for a session by the session name
	 *
	 * @param string  $name The name of the session
	 * @param string  $default (Optional) A default if the value is empty
	 * @return string The session value
	 */
	public static function getSessionValue($name, $default = '')
	{
		if (isset($_SESSION[$name]))
		{
			return $_SESSION[$name];
		} else {
			return $default;
		}
	}

	/**
	 * Get a POST, GET, Session, or Cookie value by name
	 * (in that order - if one doesn't exist, the next is tried)
	 *
	 * @param string  $name The name of the POST, GET, Session, or Cookie
	 * @param string  $default (Optional) A default if the value is empty
	 * @return string The value from that element
	 */
	public static function getValue($name, $default = '')
	{
		if (isset($_POST[$name]))
		{
			return $_POST[$name];
		} else {
			if (isset($_GET[$name]))
			{
				return $_GET[$name];
			} else {
				if (isset($_SESSION[$name]))
				{
					return $_SESSION[$name];
				} else {
					if (isset($_COOKIE[$name]))
					{
						return $_COOKIE[$name];
					} else {
						return $default;
					}
				}
			}
		}
	}

	/**
	 * Checks to see if a variable contains a value
	 *
	 * @param string  $value The value to check
	 * @return boolean TRUE if a value exists, FALSE if empty
	 */
	public static function hasValue($value)
	{
		if (strlen($value) < 1 || is_null($value) || empty($value)) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * Determines if a string is alpha only
	 *
	 * @param string $value The value to check for alpha (letters) only
	 * @param string $allow Any additional allowable characters
	 * @return boolean
	 */
	public static function isAlpha($value, $allow = '')
	{
		if (preg_match('/^[a-zA-Z' . $allow . ']+$/', $value))
		{
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Determines if a string is alpha-numeric
	 *
	 * @param string $value The value to check
	 * @return boolean TRUE if there are letters and numbers, FALSE if other
	 */
	public static function isAlphaNumeric($value)
	{
		if (preg_match("/^[A-Za-z0-9 ]+$/", $value))
		{
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Determines if a string contains a valid date
	 *
	 * @param string $value The value to inspect
	 * @return boolean TRUE if the value is a date, FALSE if not
	 */
	public static function isDate($value)
	{
		$date = date('Y', strtotime($value));
		if ($date == "1969" || $date == '')
		{
			return false;
		} else {
			return true;
		}
	}
	public static function isValidDate($mydate)
	{
		list($yy,$mm,$dd)=explode("-",$mydate);
		if (is_numeric($yy) && is_numeric($mm) && is_numeric($dd))
		{
			return checkdate($mm,$dd,$yy);
		}
		return false;
	}
	
	public static function check_in_date_range($start_date, $end_date, $date_from_user) {
	  // Convert to timestamp
	  $start_ts = strtotime($start_date);
	  $end_ts = strtotime($end_date);
	  $user_ts = strtotime($date_from_user);

     // Check that user date is between start & end
     return (($user_ts >= $start_ts) && ($user_ts <= $end_ts));	
		
	}

	/**
	 * Checks for a valid email address
	 *
	 * @param string  $email The value to validate as an email address
	 * @return boolean TRUE if it is a valid email address, FALSE if not
	 */
	public static function isEmail($email)
	{
		$pattern = "/^([a-zA-Z0-9])+([\.a-zA-Z0-9_-])*@([a-zA-Z0-9_-])+(\.[a-zA-Z0-9_-]+)+/";

		if (preg_match($pattern, $email))
		{
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Checks to see if a variable contains no value (not even a zero)
	 *
	 * @param string  $value The value to check
	 * @return boolean TRUE if a value exists, FALSE if empty
	 */
	public static function isEmpty($value)
	{
		if (strlen($value) < 1 || is_null($value)) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Checks for a valid internet URL
	 *
	 * @param string $value The value to check
	 * @return boolean TRUE if the value is a valid URL, FALSE if not
	 */
	public static function isInternetURL($value)
	{
		if (preg_match("/^http(s)?:\/\/([\w-]+\.)+[\w-]+(\/[\w- .\/?%&=]*)?$/i", $value))
		{
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Checks for a valid IP Address
	 *
	 * @param string $value The value to check
	 * @return boolean TRUE if the value is an IP address, FALSE if not
	 */
	public static function isIPAddress($value)
	{
		$pattern = "/^(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/i";
		if (preg_match($pattern, $value))
		{
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Checks to see if a variable is a number
	 *
	 * @param integer $number The value to check
	 * @return boolean TRUE if the value is a number, FALSE if not
	 */
	public static function isNumber($number)
	{
		if (preg_match("/^\-?\+?[0-9e1-9]+$/", $number))
		{
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Checks for a two character state abbreviation
	 *
	 * @param string $value The value to inspect
	 * @return boolean TRUE if the value is a 2 letter state abbreviation
	 *                 FALSE if the value is anything else
	 */
	public static function isStateAbbreviation($value)
	{
		if (preg_match("/^[A-Z][A-Z]$/i", $value))
		{
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Check to see if a string length is too long
	 *
	 * @param string $value The string value to check
	 * @param integer $maximumLength The maximum allowed length
	 * @return boolean TRUE if the length is too long
	 *                 FALSE if the length is acceptable
	 */
	public static function isTooLong($value, $maximumLength) {
		if (self::checkLength($value, $maximumLength)) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * Check to see if a string length is too short
	 *
	 * @param string $value The string value to check
	 * @param integer $maximumLength The minimum allowed length
	 * @return boolean TRUE if the length is too short
	 *                 FALSE if the length is acceptable
	 */
	public static function isTooShort($value, $minimumLength) {
		  if (strlen($value) < $minimumLength) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * Checks to see if a variable is an unsigned number
	 *
	 * @param integer $number The value to inspect
	 * @return boolean TRUE if the value is a number without a sign
	 *                 and FALSE if a sign exists
	 */
	public static function isUnsignedNumber($number)
	{
		if (preg_match("/^\+?[0-9]+$/", $number))
		{
			return true;
		} else {
			return false;
		}
	}
	
	
	public static function validateFile($ext, $size, $maxSize) {
	if (($ext != "doc") && ($ext != "docx") && ($ext != "jpeg") && ($ext != "tif") && ($ext != "gif") && ($ext != "pcx") && ($ext != "jpg") && ($ext != "cdr") && ($ext != "cpt") && ($ext != "ai") && ($ext != "eps") && ($ext != "pdf") && ($ext != "wmf") && ($ext != "bmp") && ($ext != "dst") && ($ext != "emb") && ($ext != "isi") && ($ext != "mls") && ($ext != "ngs") && ($ext != "fmc") && ($ext != "exp") && ($ext != "toyota") && ($ext != "pfaff") && ($ext != "zsk") && ($ext != "cnd") && ($ext != "fdr") && ($ext != "dst") && ($ext != "100") && ($ext != "t09") && ($ext != "ksm") && ($ext != "bar") && ($ext != "0oo") && ($ext != "dsz") && ($ext != "ref") && ($ext != "melco") && ($ext != "ofm")) {
 		return false;
 	} elseif ($size > $maxSize) {
		return false;
	}	
	return true;	
}
	
}
?>