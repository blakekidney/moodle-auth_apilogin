<?php
/**
 * This class complements the Moodle Api Login authentication plugin. 
 * This provides the client for accessing the api.
 *
 * The ApiLogin authentication plugin will need to be installed 
 * on the Moodle site first. Within the settings, an apikey will 
 * need to be set. Lastly, the url to the site and the api key 
 * need to be entered in the parameters below.
 * 
 * EXAMPLE:
 *    require_once 'MoodleApiLoginClient.php';
 *    $api = new MoodleApiLoginClient();
 *    $api->defaultUserField = 'id';
 *    $api->logUser('3');
 *    //if the login failed, then we need to handle the error
 *    echo 'Could not connect to the server at this time. Please try again later.'; 
 *
 * @package   MoodleApiLoginClient
 * @copyright 2015 Blake Kidney
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class MoodleApiLoginClient {
	
	////////////////////////////////////////////////////////////////////////////
	// USER DEFINED OPTIONS
	////////////////////////////////////////////////////////////////////////////	
	/**
     * The url to the moodle site.
	 *
     * @var string
     */
	protected $siteurl = 'https://www.moodle-site.org';
	/**
     * The key used by the system to access the api.
	 *
     * @var string
     */
	protected $apikey = 'enter the api key here';
	/**
     * The field in the database by which to identify the user.
	 * This may be: id, username, idnumber, or email
	 *
     * @var string
     */
	public $defaultUserField = 'idnumber';
	/**
     * Tells curl whether or not to verify the peer's certificate.
	 * 
	 * Setting this to false makes your api vulnerable to man in the middle attacks
	 * and it is not reccommended in a production environment. However, if doing so will
	 * resolve "SSL certificate problem: self signed certificate in certificate chain" 
	 * error sent by curl.
	 * see: http://snippets.webaware.com.au/howto/stop-turning-off-curlopt_ssl_verifypeer-and-fix-your-php-config/
	 *
     * @var boolean
     */
	public $curl_verifypeer = false;
	
	
	////////////////////////////////////////////////////////////////////////////
	// PUBLIC METHODS
	////////////////////////////////////////////////////////////////////////////
	/**
     * Constructor.
     */
    public function MoodleApiLoginClient() {
        
		//build the url to the api
		$this->apiurl = rtrim($this->siteurl, '/').'/auth/apilogin/services.php';
		
    }
			
	/**
     * Returns the last error.
	 * 
     * @return  string  The last error message.
     */
	public function error() {
		return empty($this->response->message) ? '' : $this->response->message;
	}
	
	/**
     * Returns the last server response.
	 * 
     * @return  object  The last server response.
     */
	public function getResponse() {
		return $this->response;
	}
	
	/**
     * Logs the user into Moodle by obtaining a token and then redirects them.
	 * 
	 * This method assumes it is being run by a request made from the client 
	 * that will be logging into the Moodle site as it pulls and send the user agent
	 * before redirecting the user.
	 * 
	 * @param  string  $userid  	The identifier for the user in the database.
	 * @param  string  $moodlepath	(optional) The path on the Moodle site to send 
	 *								the user upon login. The default is the dashboard.
	 * @param  string  $useridfield The field in the database to identify the user. 
	 * 								If blank the default is used.
	 *
     * @return  void
     */
	public function logUser($userid, $moodlepath=false, $useridfield='') {
		
		//contact the moodle site and obtain security token 
		$params = array(
			'userid' => $userid,
			'useridfield' => ($useridfield) ? $useridfield : $this->defaultUserField,
			'useragent' => $_SERVER['HTTP_USER_AGENT'] 
		);
		if($moodlepath) {
			$params['redirect'] = $this->buildurl(array($this->siteurl, $moodlepath));
		}
		
		$response = $this->request('logUser', $params);
		
		//check the response to see if there was an error
		if(!$response->success) return false;
		
		//redirect the user to the Moodle login page with the token in the parameters
		$this->redirect( $this->buildurl(array($this->siteurl, '/login/index.php'), array('token' => $response->token)) );
		
	}	
	/**
     * Obtains the user record for the user. 
	 * 
	 * @param  string  $userid  	The identifier for the user in the database.
	 * @param  array   $fields  	The fields to obtain concerning the user.
	 * @param  string  $useridfield The field in the database to identify the user. 
	 * 								If blank the default is used.
	 *
     * @return  array|boolean  An array of the user's information or false if there was an error.
     */
	public function getUser($userid, $fields=NULL, $useridfield='') {
		
		$params = array(
			'userid' => $userid,
			'useridfield' => ($useridfield) ? $useridfield : $this->defaultUserField,
			'fields' => $fields
		);
		$response = $this->request('getUser', $params);
		
		//check the response to see if there was an error
		if(!$response->success) return false;
		//return the data from the response
		return $response->data;
		
	}		
	/**
     * Obtains a list of all the users within the user table.
	 *
	 * @param  array  $fields  The fields to obtain concerning the user.
	 * 
     * @return  array  An array of all the users with their information.
     */
	public function getAllUsers($fields=NULL) {
		
		$params = array(
			'fields' => $fields
		);
		$response = $this->request('getAllUsers', $params);
		
		//check the response to see if there was an error
		if(!$response->success) return false;
		//return the data from the response
		return $response->data;
		
	}
	/**
     * Creates a new user. 
	 * 
	 * @param  array   $userdata  	The user data for the new user as an array.
	 *
     * @return  int|boolean  The id of the new user or false if there was an error.
     */
	public function createUser($userdata) {
		
		$params = array('userdata' => $userdata);
		$response = $this->request('createUser', $params);
		
		//check the response to see if there was an error
		if(!$response->success) return false;
		//return the data from the response
		return intval($response->data);
	
	}
	/**
     * Updates a user's information.
	 * 
	 * @param  string  $userid  	The identifier for the user in the database.
	 * @param  string  $userdata 	An array of key/value pairs of the info to update.
	 * @param  string  $useridfield The field in the database to identify the user. 
	 * 								If blank the default is used.
	 *
     * @return  boolean  Indicates whether it was successful or not.
     */
	public function updateUser($userid, $userdata, $useridfield='') {
		
		$params = array(
			'userid' => $userid,
			'userdata' => $userdata,
			'useridfield' => ($useridfield) ? $useridfield : $this->defaultUserField,
		);
		$response = $this->request('updateUser', $params);
		
		return ($response->success);
	
	}
	/**
     * Deletes a user from Moodle.
	 * 
	 * @param  string  $userid  	The identifier for the user in the database.
	 * @param  string  $useridfield The field in the database to identify the user. 
	 * 								If blank the default is used.
	 *
     * @return  boolean  Indicates whether it was successful or not.
     */
	public function deleteUser($userid, $useridfield='') {
	
		$params = array(
			'userid' => $userid,
			'useridfield' => ($useridfield) ? $useridfield : $this->defaultUserField,
		);
		$response = $this->request('deleteUser', $params);
		
		return ($response->success);
		
	}
	/**
     * Suspends a user in Moodle.
	 * 
	 * @param  string  $userid  	The identifier for the user in the database.
	 * @param  string  $useridfield The field in the database to identify the user. 
	 * 								If blank the default is used.
	 *
     * @return  boolean  Indicates whether it was successful or not.
     */
	public function suspendUser($userid, $useridfield='') {
	
		$params = array(
			'userid' => $userid,
			'useridfield' => ($useridfield) ? $useridfield : $this->defaultUserField,
		);
		$response = $this->request('suspendUser', $params);
		
		return ($response->success);
	}
	/**
     * Removes a suspension from a user in Moodle.
	 * 
	 * @param  string  $userid  	The identifier for the user in the database.
	 * @param  string  $useridfield The field in the database to identify the user. 
	 * 								If blank the default is used.
	 *
     * @return  boolean  Indicates whether it was successful or not.
     */
	public function unsuspendUser($userid, $useridfield='') {
	
		$params = array(
			'userid' => $userid,
			'useridfield' => ($useridfield) ? $useridfield : $this->defaultUserField,
		);
		$response = $this->request('unsuspendUser', $params);
		
		return ($response->success);
	}
	
	////////////////////////////////////////////////////////////////////////////
	// PRIVATE SYSTEM METHODS
	////////////////////////////////////////////////////////////////////////////
	
	/**
     * The response from the server from the last operation.
	 * 
     * @var object
     */
	private $response = null;
	/**
     * The url to the Moodle ApiLogin service. Built in the constructor.
	 * 
     * @var string
     */
	private $apiurl = '';
	/**
     * An array of allowed user identification fields.
	 * 
     * @var array
     */
	private $idfields = array('id', 'username', 'idnumber', 'email');
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
	 * Redirects to a new url.
	 * 
	 * @param  string  $url  The url to redirect.
	 * 
	 * @return  void
	 */
	public function redirect($url) {
		ob_start();
		header("Location: $url", true, 303);
		ob_flush();
		exit();
	}
	/**
	 * Builds a url.
	 * 
	 * @param  string|array  $paths  		The url pieces to connect.
	 * @param  array  		 $parameters  	(optional) The parameters to add to the url.
	 * 
	 * @return  string  The final url.
	 */
	public function buildurl($paths, $parameters=false) {
		
		if(is_array($paths)) {
			$url = '';
			foreach($paths as $path) {
				if($url && $path[0] != '/') $url .= '/';
				$url = rtrim($url, '/').$path;				
			}
		} else {
			$url = $paths;
		}
		
		if(!empty($parameters)) {
			$url .= (strpos($url, '?') === false) ? '?' : '&';			
			$url .= http_build_query($parameters);			
		}
		
		return $url;
		
	}
	/**
	 * Sends a request to the API.
	 * 
	 * @param  string|array  $method  		The function to perform through the api.
	 * @param  array  		 $parameters  	(optional) The parameters to add to the url.
	 * 
	 * @return  object  The JSON response from the API parsed as a PHP stdClass.  
	 */
	public function request($method, $parameters=false) {
		
		//add to the parameters the method, time, and signature
		$parameters['method'] = $method;
		$parameters['time'] = time();
		
		//sort the parameters
		ksort($parameters);
		//add the signature
		$parameters['signature'] = $this->sign($parameters, $this->apikey);
		
		//make the request via curl
		$curl = curl_init();
		
		curl_setopt($curl, CURLOPT_URL, 			$this->apiurl);					//set the url
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 	false);  				//do not follow redirects
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 	30);					//time out 
		curl_setopt($curl, CURLOPT_USERAGENT, 		'Moodle ApiLogin Client/1.0');
		curl_setopt($curl, CURLOPT_ENCODING, 		'gzip,deflate'); 		//Accept-Encoding:
		curl_setopt($curl, CURLOPT_FRESH_CONNECT, 	true); 					//force new connection, not cached
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 	true);					//return out as a string
		curl_setopt($curl, CURLOPT_HEADER, 			false);					//do not include header in output
		curl_setopt($curl, CURLOPT_POST, 			true);					//method = post
		curl_setopt($curl, CURLOPT_POSTFIELDS, 		$parameters);			//data to post
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 	$this->curl_verifypeer);	
		
		//obtain the response
		$output = curl_exec($curl);
				
		//check for a curl error
		if($output === false) {
			$this->response = new stdClass();
			$this->response->success = false;
			$this->response->message = 'Could not connect to the server due to a curl error. CURL: '.curl_error($curl);
			return $this->response;
		}
				
		//close the curl connection
		curl_close($curl);
		
		//we are expecting json as a response
		if(($json = json_decode($output)) === NULL) {
			//we could not decode the response
			$this->response = new stdClass();
			$this->response->success = false;
			$this->response->message = 'Could not decode the server\'s response. Expecting a JSON string. RESPONSE: '.$output;
			return $this->response;			
		}
		
		//otherwise, return the json as the response
		$this->response = $json;
		return $this->response;
		
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
}