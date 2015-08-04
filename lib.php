<?php
/**
 * This file contains lib functions for the API Login authentication plugin.
 *
 * @package   auth_apilogin
 * @copyright 2015 Blake Kidney
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Library class for the API Login.
 */
class auth_apilogin_lib {
	
	/**
     * Indicates whether the moodle framework has been loaded or not.
     *
     * @var boolean
     */
    public $framework = true;
	/**
     * An array of allowed user identification fields.
	 * 
     * @var array
     */
	private $idfields = array('id', 'username', 'idnumber', 'email');
	/**
     * A list of valid methods that may be called by the service 
	 * along with required parameters.
     *
     * @var array
     */
    private $validMethods = array('logUser', 'getUser', 'getAllUsers', 'createUser', 'updateUser', 'deleteUser', 'suspendUser', 'unsuspendUser');
	/**
     * An array of available user fields in Moodle.
	 * 
     * @var array
     */
	private $userfields = array(
		'id' => 				array('type' => 'bigint(10)', 		'null' => 'No', 	'default' => ''),
		'auth' => 				array('type' => 'varchar(20)', 		'null' => 'No', 	'default' => 'manual'),
		'confirmed' => 			array('type' => 'tinyint(1)', 		'null' => 'No', 	'default' => '0'),
		'policyagreed' => 		array('type' => 'tinyint(1)', 		'null' => 'No', 	'default' => '0'),
		'deleted' => 			array('type' => 'tinyint(1)', 		'null' => 'No', 	'default' => '0'),
		'suspended' => 			array('type' => 'tinyint(1)', 		'null' => 'No', 	'default' => '0'),
		'mnethostid' => 		array('type' => 'bigint(10)', 		'null' => 'No', 	'default' => '0'),
		'username' => 			array('type' => 'varchar(100)', 	'null' => 'No', 	'default' => ''),
		'password' => 			array('type' => 'varchar(255)', 	'null' => 'No', 	'default' => ''),
		'idnumber' => 			array('type' => 'varchar(255)', 	'null' => 'No', 	'default' => ''),
		'firstname' => 			array('type' => 'varchar(100)', 	'null' => 'No', 	'default' => ''),
		'lastname' => 			array('type' => 'varchar(100)', 	'null' => 'No', 	'default' => ''),
		'email' => 				array('type' => 'varchar(100)', 	'null' => 'No', 	'default' => ''),
		'emailstop' => 			array('type' => 'tinyint(1)', 		'null' => 'No', 	'default' => '0'),
		'icq' => 				array('type' => 'varchar(15)', 		'null' => 'No', 	'default' => ''),
		'skype' => 				array('type' => 'varchar(50)', 		'null' => 'No', 	'default' => ''),
		'yahoo' => 				array('type' => 'varchar(50)', 		'null' => 'No', 	'default' => ''),
		'aim' => 				array('type' => 'varchar(50)', 		'null' => 'No', 	'default' => ''),
		'msn' => 				array('type' => 'varchar(50)', 		'null' => 'No', 	'default' => ''),
		'phone1' => 			array('type' => 'varchar(20)', 		'null' => 'No', 	'default' => ''),
		'phone2' => 			array('type' => 'varchar(20)', 		'null' => 'No', 	'default' => ''),
		'institution' => 		array('type' => 'varchar(255)', 	'null' => 'No', 	'default' => ''),
		'department' => 		array('type' => 'varchar(255)', 	'null' => 'No', 	'default' => ''),
		'address' => 			array('type' => 'varchar(255)', 	'null' => 'No', 	'default' => ''),
		'city' => 				array('type' => 'varchar(120)', 	'null' => 'No', 	'default' => ''),
		'country' => 			array('type' => 'varchar(2)', 		'null' => 'No', 	'default' => ''),
		'lang' => 				array('type' => 'varchar(30)', 		'null' => 'No', 	'default' => 'en'),
		'theme' => 				array('type' => 'varchar(50)', 		'null' => 'No', 	'default' => ''),
		'timezone' => 			array('type' => 'varchar(100)', 	'null' => 'No', 	'default' => '99'),
		'firstaccess' => 		array('type' => 'bigint(10)', 		'null' => 'No', 	'default' => '0'),
		'lastaccess' => 		array('type' => 'bigint(10)', 		'null' => 'No', 	'default' => '0'),
		'lastlogin' => 			array('type' => 'bigint(10)', 		'null' => 'No', 	'default' => '0'),
		'currentlogin' => 		array('type' => 'bigint(10)', 		'null' => 'No', 	'default' => '0'),
		'lastip' => 			array('type' => 'varchar(45)', 		'null' => 'No', 	'default' => ''),
		'secret' => 			array('type' => 'varchar(15)', 		'null' => 'No', 	'default' => ''),
		'picture' => 			array('type' => 'bigint(10)', 		'null' => 'No', 	'default' => '0'),
		'url' => 				array('type' => 'varchar(255)', 	'null' => 'No', 	'default' => ''),
		'description' => 		array('type' => 'longtext', 		'null' => 'Yes', 	'default' => NULL), 
		'descriptionformat' => 	array('type' => 'tinyint(2)', 		'null' => 'No', 	'default' => '1'),
		'mailformat' => 		array('type' => 'tinyint(1)', 		'null' => 'No', 	'default' => '1'),
		'maildigest' => 		array('type' => 'tinyint(1)', 		'null' => 'No', 	'default' => '0'),
		'maildisplay' => 		array('type' => 'tinyint(2)', 		'null' => 'No', 	'default' => '2'),
		'autosubscribe' => 		array('type' => 'tinyint(1)', 		'null' => 'No', 	'default' => '1'),
		'trackforums' => 		array('type' => 'tinyint(1)', 		'null' => 'No', 	'default' => '0'),
		'timecreated' => 		array('type' => 'bigint(10)', 		'null' => 'No', 	'default' => '0'),
		'timemodified' => 		array('type' => 'bigint(10)', 		'null' => 'No', 	'default' => '0'),
		'trustbitmask' => 		array('type' => 'bigint(10)', 		'null' => 'No', 	'default' => '0'),
		'imagealt' => 			array('type' => 'varchar(255)', 	'null' => 'Yes', 	'default' => NULL), 
		'lastnamephonetic' => 	array('type' => 'varchar(255)', 	'null' => 'Yes', 	'default' => NULL), 
		'firstnamephonetic' => 	array('type' => 'varchar(255)', 	'null' => 'Yes', 	'default' => NULL), 
		'middlename' => 		array('type' => 'varchar(255)', 	'null' => 'Yes', 	'default' => NULL), 
		'alternatename' => 		array('type' => 'varchar(255)', 	'null' => 'Yes', 	'default' => NULL), 
		'calendartype' => 		array('type' => 'varchar(30)', 		'null' => 'No', 	'default' => 'gregorian'),
	);
	/**
     * Checks to see if a token was passed as a parameter and
	 * if the token is valid.
     *
     * @return object The $user object from the database.
     */
    public function validateToken() {
		global $DB;
		
		//check for a security token defined as parameter in the url
		$token = isset($_GET['token']) ? $_GET['token'] : false;
		
		if(!$token) return false;
		
		//check for a valid user with the security token
		$sql = "SELECT u.*, a.redirect
				FROM {auth_apilogin} a
				INNER JOIN {user} u ON u.id = a.userid
				WHERE a.token = ?
					AND a.useragent = ?
					AND a.expires > NOW()";
		$user = $DB->get_record_sql($sql, array('token' => $token, 'useragent' => $_SERVER['HTTP_USER_AGENT']));			
		
		//clear out the login token (it might be expired, so we do it even if we don't find it)
		$DB->delete_records('auth_apilogin', array('token' => $token));
		
		//if we didn't find a user, then return false
		if(!$user) return false;
		
		//let's check the auth plugin for this user and change it
		if($user->auth != 'apilogin') {
			$userauth = new stdClass();
			$userauth->id = $user->id;
			$userauth->auth = $user->auth = 'apilogin';
			$DB->update_record('user', $userauth);		
		}
		
		return $user;
		
	}
	
	
	//////////////////////////////////////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////////////////////////////////
	// WEB SERVICE UTITLITY METHODS
	//////////////////////////////////////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////////////////////////////////

	
	/**
     * Validates a request by checking ip address, signature, and method.
	 * Sends the response to the client.
	 *	 
	 * @param array $post The post data from the $_POST variable.
     *
     * @return void
     */
    public function validateRequest($post) {
		
		//let's make sure we have the minimal required parameters
		if(empty($post['method']) || empty($post['time']) || empty($post['signature'])) {
			$this->respond(false, array('message' => 'Invalid request. Missing required parameters.'));
		}
		
		//pull the configuration files from the database for this plugin
		$results = $this->db_select("SELECT id, name, value FROM {config_plugins} WHERE plugin = 'auth_apilogin'");
		if(!$results) {
			$this->respond(false, array('message' => 'Error loading the configuration for the apilogin plugin from the Moodle database.'));			
		}
		$this->config = new stdClass();
		foreach($results as $row) {
			$this->config->{$row->name} = $row->value;
		}		
		if(!isset($this->config->allowipaddr)) $this->config->allowipaddr = array();
		if(!is_array($this->config->allowipaddr)) $this->config->allowipaddr = preg_split('/\s*[,;]\s*/', $this->config->allowipaddr);		
		
		//validate the IP address
		if(!in_array($_SERVER['REMOTE_ADDR'], $this->config->allowipaddr)) {
			$this->respond(false, array('message' => 'Access denied. Requests not allowed from IP address: '.$_SERVER['REMOTE_ADDR']));
		}		
		
		//pull the signature
		$signature = $post['signature'];
		unset($post['signature']);
		
		//sort the parameters to ensure the order matches
		ksort($post);
		
		//validate the signature
		if($signature !== $this->sign($post, $this->config->apikey)) {
			$this->respond(false, array('message' => 'Invalid signature.'));
		}		
		
		//validate the method
		if(!in_array($post['method'], $this->validMethods)) {
			$this->respond(false, array('message' => 'Invalid method.'));			
		}
		
		//check the required parameters for the given method
		
		
		//this really isn't necessary as the script would have exited above if anything failed
		return true;		
	}	
	/**
	 * Checks the required parameters are present in the posted variables for the chosen method. 
	 * 
	 * @param array $required  An array of the required parameters.
	 * @param array $post  An array of the posted variables.
	 * 
	 * @return void.  
	 */
	private function checkRequired($required, &$post) {
		$missing = array();
		foreach($required as $param) {
			if(empty($post[$param])) $missing[] = $param;
		}	
		if(!empty($missing)) {
			$this->respond(false, array('message' => 
				'The '.$post['method'].' method is missing parameters: '.implode(', ', $missing)
			));
		}
		//if the useridfield is set, we need to check it as well
		if(isset($post['useridfield']) && !in_array($post['useridfield'], $this->idfields)) {
			$this->respond(false, array('message' => 
				'The useridfield ['.$post['useridfield'].'] is not permitted as a lookup for the user in Moodle. '.
				'Please user one of the following: '.implode(', ', $this->idfields)
			));
		}
		
	}
	/**
	 * Sends the response to the client as a JSON encoded string.
	 * 
	 * @param boolean $success Indicates whether the operation was a success or not.
	 * @param array $data The data to encode and send.
     *
     * @return void
     */
    public function respond($success, $data) {
		header('Content-Type: application/json');
		$data['success'] = $success;
		if(!($json = json_encode($data, JSON_PRETTY_PRINT))) {
			exit('{ "success":false, "message":"The server encountered an error creating the response." }');
		}
		exit($json);
	}
	/**
	 * Builds the signature to sign the parameters. 
	 * 
	 * @param  array   $parameters  The parameters to add to the url.
	 * @param  string   $secret  The secret key added to the parameters.
	 * 
	 * @return  string  The signature for the parameters.  
	 */
	private function sign($parameters, $secret) {
		$str = '';
		foreach($parameters as $key => $value) {
			$str .= $key.$value;
		}
		return hash('sha256', $secret.$str);		
	}
	/**
	 * Generates a security token. 
	 * 
	 * @return  string  The token.  
	 */
	private function genToken() {
		$token = false;
		$size = 16;
		
		if(function_exists('mcrypt_create_iv') && version_compare(PHP_VERSION, '5.3.7') >= 0) {
			$token = bin2hex(mcrypt_create_iv($size, MCRYPT_DEV_URANDOM));
		}
		if(!$token && function_exists('openssl_random_pseudo_bytes')) {
			$token = bin2hex(openssl_random_pseudo_bytes($size));
		}
		//this is not as cryptographically secure, however, it will work for our needs
		if(!$token) {
			$token = md5(uniqid(rand(), true));
		}
		
		//if the token generation failed, report it
		if(!$token) {
			$this->respond(false, array('message' => 'An error occurred generating a token. Please install the mcrypt extension.'));
		} 
		
		return $token;		
	}
	
	/**
	 * Hashes a password to store in the database. 
	 * 
	 * @param  string   $password  The password to hash.
	 * 
	 * @return  string  The hashed password.  
	 */
	private function hashpass($password) {
		global $CFG;
		
		//check to see if we have moodle's internal function loaded
		if(function_exists('hash_internal_user_password')) {
			return hash_internal_user_password($password);
		}
		
		if(function_exists('password_hash')) {
			return password_hash($password, PASSWORD_DEFAULT);
		}
		
		if(!function_exists('hash_internal_user_password')) {
			require_once($CFG->libdir.'/password_compat/lib/password.php');
		}
		
		return password_hash($password, PASSWORD_DEFAULT);
	}
	/**
	 * Turns an array into a string with an "and".
	 * 
	 * @param  array  $ray  The array of string to concat.
	 * @param  string  $singular  (optional) Appended to the end of the string if singular. (i.e. Portland campus)
	 * @param  string  $plural  (optional) Appended to the end of the string if plural.  (i.e. Portland and San Jose campuses)
	 * 
	 * @return  string  The final text.
	 */
	private function andlist($ray, $singular='', $plural='') {
		if(empty($ray)) return '';		
		if(count($ray) == 1) return current($ray).($singular ? ' '.$singular : '');
		if(count($ray) == 2) return implode(' and ', $ray).($plural ? ' '.$plural : '');
		$list = implode(', ', $ray);
		return substr_replace($list, ' and', strrpos($list, ',')+1, 0).($plural ? ' '.$plural : '');		
	}
	//////////////////////////////////////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////////////////////////////////
	// WEB SERVICE DATABASE METHODS
	//////////////////////////////////////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////////////////////////////////
	/**
     * Get a number of records as an array of objects. 
	 * 
	 * @param string $sql The SQL select query to execute. The first column of this SELECT statement
     *   must be a unique value (usually the 'id' field), as it will be used as the key of the
     *   returned array.
     * @param array $params array of sql parameters
     *
     * @return array The results of the select as an array of objects.
     */
    public function db_select($sql, $params=NULL) {		
		global $DB;
		try {
			return $DB->get_records_sql($sql, $params);
		} catch(Exception $e) { 
			$this->respond(false, array('message' => 'An error occurred accessing the database.'));
			return false;
		}
	}
	/**
     * Get a single record as an array of objects. 
	 * 
	 * @param string $sql The SQL select query to execute. The first column of this SELECT statement
     *   must be a unique value (usually the 'id' field), as it will be used as the key of the
     *   returned array.
     * @param array $params array of sql parameters
     *
     * @return array The results of the select as an array of objects.
     */
    public function db_select_one($sql, $params=NULL) {		
		global $DB;
		try {
			return $DB->get_record_sql($sql, $params);
		} catch(Exception $e) { 
			$this->respond(false, array('message' => 'An error occurred accessing the database.'));
			return false;
		}
	}
	/**
     * Get a number of records as an array of objects. 
	 * 
	 * @param string $table  The database table to be inserted into.
     * @param array $data An an array with contents equal to fieldname=>fieldvalue.
     *
     * @return int The new id of the record.
     */
    public function db_insert($table, $data) {		
		global $DB;
		$obj = is_array($data) ? (object) $data : $data;
		try {
			return $DB->insert_record($table, $obj);
		} catch(Exception $e) { 
			$this->respond(false, array('message' => 'An error occurred accessing the database.'));
			return false;
		}
	}
	/**
     * Get a number of records as an array of objects. 
	 * 
	 * @param string $table The database table to be updated.     
     * @param array $data An an array with contents equal to fieldname=>fieldvalue.
     *
     * @return boolean Indicates whether the operation was succesful or not.
     */
    public function db_update($table, $data) {		
		global $DB;
		$obj = is_array($data) ? (object) $data : $data;
		try {
			return $DB->update_record($table, $obj);
		} catch(Exception $e) { 
			$this->respond(false, array('message' => 'An error occurred accessing the database.'));
			return false;
		}
	}
	/**
     * Delete one or more records from a table which match a particular WHERE clause.
	 * 
	 * @param string $table The database table to be checked against.
     * @param string $select A fragment of SQL to be used in a where clause in the SQL call (used to define the selection criteria).
     * @param array $params An array of sql parameters.
	 * 
     * @return boolean Indicates whether the operation was succesful or not.
     */
    public function db_delete($table, $select, $params=NULL) {		
		global $DB;
		try {
			return $DB->delete_records_select($table, $select, $params);
		} catch(Exception $e) { 
			$this->respond(false, array('message' => 'An error occurred accessing the database.'));
			return false;
		}
	}
	/**
	 * Converts a value into sql datetime string.
	 * 
	 * If the value is not given, returns current time.
	 * 
	 * @param  mixed  $value  (optional) The date to format. If NULL, then use today. 
	 *    The date may be a string of the date in another format or UNIX timestamp.
	 * 
	 * @return  string  SQL formatted DATETIME string.
	 */
	public function sqldatetime($value=NULL) {
		if($value === NULL) return date("Y-m-d H:i:s");
		if($value === 0) return '0000-00-00 00:00:00';
		if($value === '') return false;
		if(is_numeric($value)) return date("Y-m-d H:i:s", $value);
		if(strlen($value) < 3) return false;
		try { $dt = new DateTime($value); } catch(Exception $e) { return false; }
		return $dt->format("Y-m-d H:i:s");
	}
	
	//////////////////////////////////////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////////////////////////////////
	// WEB SERVICE USER METHODS
	//////////////////////////////////////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////////////////////////////////
	/**
     * Logs the user into Moodle by obtaining a token and then redirecting them.
	 * 
	 * @param array post An array of the posted parameters.
	 *
     * @return void
     */
	public function logUser($post) {
		//check for the required parameters and exit if any are missing
		$this->checkRequired(array('userid', 'useridfield', 'useragent'), $post);
		
		//obtain the user record for this user
		$user = $this->db_select_one("SELECT id FROM {user} WHERE ".$post['useridfield']." = ?", array($post['userid']));
		if(!$user) $this->respond(false, array('message' => 'User not found with '.$post['useridfield'].' equal to '.$post['userid'].'.' ));
		
		//delete any previous tokens that exist for this user
		$this->db_delete('auth_apilogin', 'userid = ?', array($user->id));
		
		//generate a token for the user and save it
		$pdata = array(
			'userid' => $user->id,
			'token' => $this->genToken(),
			'useragent' => $post['useragent'],
			'expires' => $this->sqldatetime('+5 minutes')
		);
		if(!empty($post['redirect'])) {
			$pdata['redirect'] = $post['redirect'];
		}
		$this->db_insert('auth_apilogin', $pdata);
		
		//send back the result
		$this->respond(true, array('data' => $user->id, 'token' => $pdata['token']));
		
	}	
	/**
     * Obtains the user record for the user. 
	 * 
	 * @param array post An array of the posted parameters.
	 *
     * @return void
     */
	public function getUser($post) {
		//check for the required parameters
		$this->checkRequired(array('userid', 'useridfield'), $post);
			
		$what = '';
		if(empty($post['fields']) || !is_array($post['fields'])) {
			foreach($this->userfields as $field => $info) {
				if($field == 'password' || $field == 'secret') continue;
				$what .= $field.',';
			}
		} else {
			foreach($post['fields'] as $field) {
				if($field == 'password' || $field == 'secret') continue;
				if(isset($this->userfields[$field])) $what .= $field.',';
			}
		}
		$what = rtrim($what, ',');
		
		//obtain the user record for this user
		$user = $this->db_select_one("SELECT ".$what." FROM {user} WHERE ".$post['useridfield']." = ?", array($post['userid']));
		if(!$user) $this->respond(false, array('message' => 'User not found with '.$post['useridfield'].' equal to '.$post['userid'].'.' ));		
		
		//send back the result
		$this->respond(true, array('data' => $user));
	}		
	/**
     * Obtains a list of all the users within the user table.
	 *
	 * @param array post An array of the posted parameters.
	 * 
     * @return void
     */
	public function getAllUsers($post) {
		
		$what = '';
		if(empty($post['fields']) || !is_array($post['fields'])) {
			foreach($this->userfields as $field => $info) {
				if($field == 'password' || $field == 'secret') continue;
				$what .= $field.',';
			}
		} else {
			foreach($post['fields'] as $field) {
				if($field == 'password' || $field == 'secret') continue;
				if(isset($this->userfields[$field])) $what .= $field.',';
			}
		}
		$what = rtrim($what, ',');
		
		//obtain the user record for this user
		$users = $this->db_select("SELECT ".$what." FROM {user} WHERE deleted = 0");
		
		//send back the result
		$this->respond(true, array('data' => $users));
		
	}
	
	
	/**
     * Creates a new user. 
	 * 
	 * @param array post An array of the posted parameters.
	 *
     * @return void
     */
	public function createUser($post) {
		
		//check for the required parameters
		$this->checkRequired(array('userdata'), $post);
		
		//check for the required data for the user
		$missing = array();
		$needfields = array('username', 'firstname', 'lastname', 'email');
		foreach($needfields as $field) {
			if(!isset($post['userdata'][$field])) $missing[] = $field;
		}
		if(!empty($missing)) {
			$this->respond(false, array('message' => 'Required fields were missing for creating the user: '.implode(', ', $missing)));
		}
		
		//only copy fields that belong and filter out those that don't apply
		$pdata = array();
		foreach($post['userdata'] as $key => $param) {
			if($key == 'id' || $key == 'password' || $key == 'secret') continue;
			if(isset($this->userfields[$key])) $pdata[$key] = $param;
		}
		
		//check if another user exists with the provided username, email, or idnumber
		$tmpdata = array($pdata['username'], $pdata['email']);
		$sql = "SELECT id, username, email, idnumber FROM {user} ".
			   "WHERE username = ? OR email = ?";
		if(!empty($pdata['idnumber'])) {
			$sql .= " OR idnumber = ?";
			$tmpdata[] = $pdata['idnumber'];
		}
		$users = $this->db_select($sql, $tmpdata);
		if($users) {
			//determine which matches we have found
			$m = array();
			foreach($users as $user) {
				if($pdata['username'] == $user->username) $m[] = 'username';
				if($pdata['email'] == $user->email) $m[] = 'email';
				if(!empty($pdata['idnumber']) && $pdata['idnumber'] = $user->idnumber) $m[] = 'idnumber';
			}
			$this->respond(false, 
				array('message' => (count($users) > 1 ? 'Other users were' : 'Another user was').
								   ' found with the same '.$this->andlist($m).'.')
			);		
		}
		
		//if we don't have a password, then indicate this
		if(!isset($post['userdata']['password'])) {
			$pdata['password'] = 'NOT SET '.time();
		} else {
		//if we do have a password, then we need to hash the password
			$pdata['password'] = $this->hashpass($post['password']);	
			if(!$pdata['password']) $pdata['password'] = 'NOT SET '.time();
		}
		
		//create the user
		$id = $this->db_insert('user', $pdata);
		
		//send back the result
		$this->respond(true, array('data' => $id));
	
	}
	/**
     * Updates a user's information.
	 * 
	 * @param array post An array of the posted parameters.
	 *
     * @return void
     */
	public function updateUser($post) {
		//check for the required parameters
		$this->checkRequired(array('userid', 'userdata', 'useridfield'), $post);
		
		//first, let's see if we can verify the user exists
		$user = $this->db_select_one("SELECT id FROM {user} WHERE ".$post['useridfield']." = ?", array($post['userid']));
		if(!$user) $this->respond(false, array('message' => 'User not found with '.$post['useridfield'].' equal to '.$post['userid'].'.' ));
		
		//only copy fields that belong and filter out those that don't apply
		$pdata = array('id' => $user->id);
		foreach($post['userdata'] as $key => $param) {
			if($key == 'id') continue;
			if(isset($this->userfields[$key])) $pdata[$key] = $param;
		}
		
		if(empty($pdata)) {
			$this->respond(false, array('message' => 'Missing any valid fields to update.' ));
		}
		
		$this->db_update('user', $pdata);
		
		//send back the result
		$this->respond(true, array('data' => $user->id));
	
	}
	/**
     * Deletes a user from Moodle.
	 * 
	 * @param array post An array of the posted parameters.
	 *
     * @return void
     */
	public function deleteUser($post) {
		global $CFG;
		//check for the required parameters
		$this->checkRequired(array('userid', 'useridfield'), $post);
		
		//first, let's see if we can verify the user exists
		$user = $this->db_select_one("SELECT id, username, email, idnumber ".
									 "FROM {user} WHERE ".$post['useridfield']." = ?", 
									 array($post['userid']));
		if(!$user) $this->respond(false, array('message' => 'User not found with '.$post['useridfield'].' equal to '.$post['userid'].'.' ));
		
		//guest account cannot be deleted
		if($user->username === 'guest' || (!empty($CFG->siteguest) && $CFG->siteguest == $user->id)) {
			$this->respond(false, array('message' => 'The guest account cannot be deleted.' ));
		}
		
		//don't allow the deletion of local site administrators
		if(preg_match('/,'.$user->id.',/', ','.$CFG->siteadmins.',')) {
			$this->respond(false, array('message' => 'Local administrators cannot be deleted.' ));
		}
		
		//unlike Moodle's user deletion, we are going to preserve all the user data and just flag the account as deleted
		//@see - /lib/moodlelib.php - function delete_user()
		$time = time();
		$pdata = new stdClass();
		$pdata->id = $user->id;
		$pdata->deleted = 1;
		$pdata->username = $user->username.'/'.$time;
		$pdata->email = $user->email.'/'.$time;
		$pdata->idnumber = $user->idnumber.'/'.$time;
		$pdata->password = 'USER DELETED ON '.$this->sqldatetime();
		$pdata->timemodified = $time;
		
		$this->db_update('user', $pdata);
		
		//send back the result
		$this->respond(true, array('data' => $user->id));
		
	}
	/**
     * Suspends a user in Moodle.
	 * 
	 * @param array post An array of the posted parameters.
	 *
     * @return void
     */
	public function suspendUser($post) {
		//check for the required parameters
		$this->checkRequired(array('userid', 'useridfield'), $post);
		
		//first, let's see if we can verify the user exists
		$user = $this->db_select_one("SELECT id FROM {user} WHERE ".$post['useridfield']." = ?", array($post['userid']));
		if(!$user) $this->respond(false, array('message' => 'User not found with '.$post['useridfield'].' equal to '.$post['userid'].'.' ));
		
		//update the user
		$this->db_update('user', array('id' => $user->id, 'suspended' => '1'));
		
		//send back the result
		$this->respond(true, array('data' => $user->id));
	}
	/**
     * Removes a suspension from a user in Moodle.
	 * 
	 * @param array post An array of the posted parameters.
	 *
     * @return void
     */
	public function unsuspendUser($post) {
		//check for the required parameters
		$this->checkRequired(array('userid', 'useridfield'), $post);
		
		//first, let's see if we can verify the user exists
		$user = $this->db_select_one("SELECT id FROM {user} WHERE ".$post['useridfield']." = ?", array($post['userid']));
		if(!$user) $this->respond(false, array('message' => 'User not found with '.$post['useridfield'].' equal to '.$post['userid'].'.' ));
		
		//update the user
		$this->db_update('user', array('id' => $user->id, 'suspended' => '0'));
		
		//send back the result
		$this->respond(true, array('data' => $user->id));
	}	

}