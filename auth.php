<?php
/**
 * Login though an API.
 *
 * @package    auth_apilogin
 * @copyright  2015 Blake Kidney
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/*
https://docs.moodle.org/dev/Authentication_plugins
https://docs.moodle.org/dev/Authentication_API
*/
 
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/authlib.php');
require_once($CFG->dirroot.'/auth/apilogin/lib.php');

/**
 * Plugin for no authentication.
 */
class auth_plugin_apilogin extends auth_plugin_base {
	
	
    /**
     * Constructor.
     */
    public function auth_plugin_apilogin() {
        $this->authtype = 'apilogin';
        $this->roleauth = 'auth_apilogin';
        $this->errorlogtag = '[AUTH APILOGIN] ';
        $this->config = get_config('auth/apilogin');
		$this->set_config_defaults($this->config);
    }		 
		 
	
	/**
     * Hook for overriding behaviour of prior to redirecting to 
	 * the login page, eg redirecting to an external login url for 
	 * SAML or OpenID authentication. If you implement this you 
	 * should also implement loginpage_hook as the user may go 
	 * directly to the login page.
     */
    public function pre_loginpage_hook() {
		global $DB;
		
		$lib = new auth_apilogin_lib();
		$user = $lib->validateToken();

		//if valid, then complete the user login so they can enter directly into the site
		if($user) {
			complete_user_login($user);	
			
			if(!empty($user->redirect)) {
				redirect($user->redirect);
			}
			
		//if not valid, then redirect if the user to the login page set in the plugin config
		//of if not set, then allow moodle to perform the redirect
		} else {	
			if($this->config->loginredirect) {
				redirect($this->config->loginredirect);
			}		
		}
		
	}
	/**
     * Authentication hook - is called every time user hit the login page
     * The code is run only if the param code is mentioned.
     */
    public function loginpage_hook() {
        global $USER, $SESSION, $CFG, $DB;
		
		$USER->auth = 'apilogin';
		
		//if the user is already logged into the system, then don't do anything
		if(!$USER->id) {		
		
			$lib = new auth_apilogin_lib();
			$user = $lib->validateToken();

			//if valid, then complete the user login so they can enter directly into the site
			if($user) {
				complete_user_login($user);	
				
				//redirect to the requested page
				if(!empty($user->redirect)) {	
					redirect($user->redirect);
				
				//redirect to the wantsurl if it exists
				} elseif(!empty($SESSION->wantsurl)) {
					redirect($SESSION->wantsurl);
				
				//redirect to the dashboard
				} else {
					redirect($CFG->wwwroot.'/my/');
				}
				
			//if not valid, then redirect if the user to the login page set in the plugin config
			//of if not set, then allow moodle to perform the redirect
			} else {	
				if($this->config->loginredirect) {
					redirect($this->config->loginredirect);
				}		
			}
		}
		
	}
	
    /**
     * Returns true if the username and password work or don't exist and false
     * if the user exists and the password is wrong.
     *
     * @param string $username The username
     * @param string $password The password
     * @return bool Authentication success or failure.
     */
    public function user_login($username, $password) {
        global $CFG, $DB, $USER;
				
		//allow normal login if the api is not set for redirection		
		if(!$user = $DB->get_record('user', array('username' => $username, 'mnethostid' => $CFG->mnet_localhost_id))) {
			return false;
        }
        if(!validate_internal_user_password($user, $password)) {
			return false;
        }
        if($password === 'changeme') {
            // force the change - this is deprecated and it makes sense only for manual auth,
            // because most other plugins can not change password easily or
            // passwords are always specified by users
            set_user_preference('auth_forcepasswordchange', true, $user->id);
        }
        return true;
    }

    /**
     * Updates the user's password.
     *
     * called when the user password is updated.
     *
     * @param  object  $user        User table object
     * @param  string  $newpassword Plaintext password
     * @return boolean result
     *
     */
    public function user_update_password($user, $newpassword) {
        $user = get_complete_user_data('id', $user->id);
        // This will also update the stored hash to the latest algorithm
        // if the existing hash is using an out-of-date algorithm (or the
        // legacy md5 algorithm).
        return update_internal_user_password($user, $newpassword);
    }
	
    /**
     * Indicates if password hashes should be stored in local moodle database. 
	 * This function automatically returns the opposite boolean of what 
	 * is_internal() returns. Returning true means MD5 password hashes will 
	 * be stored in the user table. Returning false means flag 'not_cached' 
	 * will be stored there instead. 
	 * 
     * @return boolean
     */
    public function prevent_local_passwords() {
        return false;
    }
	
    /**
     * Returns true if this authentication plugin is 'internal'.
	 * Internal plugins use password hashes from Moodle user table for authentication.
     *
     * @return bool
     */
    public function is_internal() {
        return false;
    }

    /**
     * Returns true if this authentication plugin can change the user's password.
	 * 
	 * Leave this true if you are going to use change_password_url();
     *
     * @return bool
     */
    public function can_change_password() {
        return (!$this->config->passwordurl);
    }

    /**
     * Returns the URL for changing the user's pw, or empty if the default can
     * be used.
     *
     * @return moodle_url
     */
    public function change_password_url() {
        return $this->config->passwordurl ? new moodle_url($this->config->passwordurl) : null;
    }

    /**
     * Returns true if plugin allows resetting of internal password.
     *
     * @return bool
     */
    public function can_reset_password() {
        return (!$this->config->passwordurl);
    }

    /**
     * Returns true if this authentication plugin can edit the users' profile. 
	 * 
	 * Only works when the auth paramenter in the user table is set to this plugin.
	 * 
	 * Leave this true if you are going to use edit_profile_url();
     *
     * @return bool
     */
    public function can_edit_profile() {
		return (!$this->config->profileurl);
    }

    /**
     * Returns the URL for editing users' profile, or empty if the defaults URL can be used.
	 * 
	 * Only works when the auth paramenter in the user table is set to this plugin.
     *
     * @return moodle_url
     */
    public function edit_profile_url() {        
		return $this->config->profileurl ? new moodle_url($this->config->profileurl) : NULL;
    }

    /**
     * Returns true if plugin can be manually set.
     *
	 * This function was introduced in the base class and returns 
	 * false by default. If overriden by an authentication plugin 
	 * to return true, the authentication plugin will be able to be 
	 * manually set for users. For example, when bulk uploading users 
	 * you will be able to select it as the authentication method they use.
	 * 
     * @return bool
     */
    public function can_be_manually_set() {
        return true;
    }
		
	 /**
     * Config settings.
     */
	 public function config_settings() {
		return array('allowipaddr', 'apikey', 'loginredirect', 'passwordurl', 'profileurl');
	 }
	
    /**
     * Prints a form for configuring this authentication plugin.
     *
     * This function is called from admin/auth.php, and outputs a full page with
     * a form for configuring this plugin.
     *
     * @param array $page An object containing all the data for this page.
     */
    public function config_form($config, $err, $user_fields) {
        //set defaults if undefined
		$this->set_config_defaults($config);
		//load the form
		include 'config-form.php';
    }

    /**
     * Processes and stores configuration data for this authentication plugin.
     */
    public function process_config($config) {
		//set defaults if undefined
		$this->set_config_defaults($config);
		
		// save settings
		foreach($this->config_settings() as $setting) {
			set_config($setting, $config->{$setting}, 'auth_apilogin');
		}		
        return true;
    }
	/*
	 * Sets the configuration defaults if they are not defined.
	 */
	public function set_config_defaults(&$config) {
		foreach($this->config_settings() as $setting) {
			if(!isset($config->{$setting})) $config->{$setting} = '';
		}		
		//only generate a random key if one is not defined
		if(!$config->apikey) {
			$config->apikey = trim(base64_encode(openssl_random_pseudo_bytes(mt_rand(10, 16))), '=');
		}
    }
}


