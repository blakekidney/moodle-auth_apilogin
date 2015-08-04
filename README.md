
# Moodle Authentication Api Login

This authentication plugin aids in the development of SSO (Single Sign-on) by providing an api for logging users into Moodle and managing user accounts. Access to the api is accomplished by accepting requests only from specific IP addresses while also signing parameters with a shared secret key.

A sample client written in PHP has been provided with the plugin. 

## GENERATING A REQUEST

The endpoint to the api is the url to the Moodle site along with the following path: /auth/apilogin/services.php

All requests require the following three parameters: method, time, and signature. 
method = The function to run on the service. (Example: getUser)
time = The unix timestamp of the when the request is being generated.
signature = A hashed string of the parameters combined with the secret key.

All responses are returned as JSON that contains a 'success' boolean parameter indicating whether the operation was successful or not and either a 'message' about the error if there was one or 'data' depending on if the operation called for data to be sent back.

## CREATING THE SIGNATURE:
The signature is created by ordering all the parameters by their key, combining then into a single string, prepending the secret key, and lastly hashing them using SHA256. 

##### EXAMPLE IN PHP:

```php
$params = array(
	'method' => 'getUser',
	'time' => time(),
	'userid' => '1',
	'userfield' => 'id'
);
ksort($params);
$str = '';
foreach($params as $key => $value) {
	$str .= $key.$value;
}
$params['signature'] = hash('sha256', $secret.$str);
```

## METHODS:

### logUser
Logs a user into Moodle by generating an authentication token for that user. The token is sent back and should be included when redirecting the user to Moodle's login page.

##### Parameters
* **userid** - The identifier for the user. 
* **userfield** - The field used to identify the user in the user table. This can be 'id', 'username', 'email', or 'idnumber'.
* **useragent** - The http user agent from the user who will be redirected to the Moodle site for login.
* **redirect** - (optional) The full path of the page within Moodle to redirect the user after login.

##### Returns
* **token** - The token necessary to log the user into the system.
* **data** - The user id of the user.

---
### getUser
Obtains a user record from the Moodle database user table.

##### Parameters
* **userid** - The identifier for the user. 
* **userfield** - The field used to identify the user in the user table. This can be 'id', 'username', 'email', or 'idnumber'.
* **fields** - (optional) An array of fields to pull from the user table concerning a user. The password or secret column are not included. If not included, returns all columns.

##### Returns
* **data** - An object of the user data.

---
### getAllUsers
Obtains all the user records from the Moodle database user table excluding deleted users.

##### Parameters
* **fields** - (optional) An array of fields to pull from the user table concerning a user. The password or secret column are not included. If not included, returns all columns.

##### Returns
* **data** - An array of objects of the users' data.

---
### createUser
Creates a new user within Moodle. The 'username', 'firstname', 'lastname', and 'email' are required fields. 

##### Parameters
* **userdata** - An array of user data for the user. 

##### Returns
* **data** - The id of the user.

---
### updateUser
Updates a user record within Moodle. 

##### Parameters
* **userid** - The identifier for the user. 
* **userfield** - The field used to identify the user in the user table. This can be 'id', 'username', 'email', or 'idnumber'.
* **userdata** - An array of user data for the user. 

##### Returns
* **data** - The id of the user.

---
### deleteUser
Deletes a user within Moodle. This function only flags the user's record as being deleted. It does not impact any of the user's enrollments or other information. 

##### Parameters
* **userid** - The identifier for the user. 
* **userfield** - The field used to identify the user in the user table. This can be 'id', 'username', 'email', or 'idnumber'.

##### Returns
* **data** - The id of the user.

---
### suspendUser
Suspends a user within Moodle.

##### Parameters
* **userid** - The identifier for the user. 
* **userfield** - The field used to identify the user in the user table. This can be 'id', 'username', 'email', or 'idnumber'.

##### Returns
* **data** - The id of the user.

---
### unsuspendUser
Removes a suspension from a user within Moodle.

##### Parameters
* **userid** - The identifier for the user. 
* **userfield** - The field used to identify the user in the user table. This can be 'id', 'username', 'email', or 'idnumber'.

##### Returns
* **data** - The id of the user.