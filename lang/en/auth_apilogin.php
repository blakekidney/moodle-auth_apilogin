<?php
/**
 * Strings for component 'auth_apilogin', language 'en'.
 *
 * @package   auth_apilogin
 * @copyright 2015 Blake Kidney
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['auth_apilogindescription'] = 'Provides an API for logging users into Moodle for single sign-on.';
$string['pluginname'] = 'API Login';
$string['auth_apilogin_label_allowipaddr'] = 'Allowed IP Addresses';
$string['auth_apilogin_label_apikey'] = 'API Key';
$string['auth_apilogin_label_duration'] = 'Duration';
$string['auth_apilogin_label_loginredirect'] = 'Login Redirection';
$string['auth_apilogin_label_passwordurl'] = 'Url to Change Password';
$string['auth_apilogin_label_profileurl'] = 'Url to Edit Profile';
$string['auth_apilogin_info_ipaddrallow'] = 'A comma separated list of ip addresses that are permitted to access the api.';
$string['auth_apilogin_info_apikey'] = 'The secret key shared between the client and server that is used to produce a signature for api access.';
$string['auth_apilogin_info_duration'] = 'The duration of how many minutes before an access token expires after being issue by Moodle.';
$string['auth_apilogin_info_loginredirect'] = 'Where to redirect users when they land on the login page. If blank, the standard Moodle login will be used.';
$string['auth_apilogin_info_passwordurl'] = 'Url where users can change their password. If blank, the standard Moodle url is used. This only works when the user\'s authentication plugin is set to API Login.';
$string['auth_apilogin_info_profileurl'] = 'Url where users can edit their profile. If blank, the standard Moodle url is used. This only works when the user\'s authentication plugin is set to API Login.';
