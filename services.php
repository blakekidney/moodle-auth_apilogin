<?php
/**
 * This provides web services for managing users for the login api.
 *
 * @package   auth_apilogin
 * @copyright 2015 Blake Kidney
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/*
Moodle already has webservices built in which we could use. 
However, for our purposes, we will keep it simple.

https://docs.moodle.org/29/en/Using_web_services
https://docs.moodle.org/dev/Web_services
https://docs.moodle.org/dev/Web_services_API
https://docs.moodle.org/dev/Web_service_API_functions
*/

//------------------------------------------------------------------------
// MOODLE FRAMEWORK
//------------------------------------------------------------------------
	
//tell Moodle to use an optimized version
define('ABORT_AFTER_CONFIG', 1);

//include Moodle's configuration
require('../../config.php');
//load only the libraries we will need to run just the database
require_once($CFG->libdir.'/setuplib.php');
require_once($CFG->libdir.'/classes/text.php');
require_once($CFG->libdir.'/classes/string_manager.php');
require_once($CFG->libdir.'/classes/string_manager_install.php');
require_once($CFG->libdir.'/classes/string_manager_standard.php');
require_once($CFG->libdir.'/weblib.php');
require_once($CFG->libdir.'/dmllib.php');
require_once($CFG->libdir.'/moodlelib.php');
require_once($CFG->libdir.'/sessionlib.php');
require_once($CFG->dirroot.'/cache/lib.php');

//------------------------------------------------------------------------
// SETUP
//------------------------------------------------------------------------
//setup the database and connect
setup_DB();
//pull the configuration for the site
initialise_cfg();

//------------------------------------------------------------------------
// ERROR SETTINGS - FOR DEVELOPMENT
//------------------------------------------------------------------------
//*
$CFG->displaydebug = true;
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 0);
//*/


//------------------------------------------------------------------------
// LOAD API CLASS
//------------------------------------------------------------------------

require_once($CFG->dirroot.'/auth/apilogin/lib.php');
$api = new auth_apilogin_lib();

//------------------------------------------------------------------------
// VALIDATE REQUEST
//------------------------------------------------------------------------
$api->validateRequest($_POST);

//------------------------------------------------------------------------
// PROCESS REQUEST
//------------------------------------------------------------------------
call_user_func(array($api, $_POST['method']), $_POST);




