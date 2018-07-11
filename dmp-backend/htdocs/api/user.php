<?php
/************************ LICENCE ***************************
*     This file is part of <DMP Canvas Generator web application>
*     Copyright (C) <2016> SIB Swiss Institute of Bioinformatics
*
*     This program is free software: you can redistribute it and/or modify
*     it under the terms of the GNU Affero General Public License as
*     published by the Free Software Foundation, either version 3 of the
*     License, or (at your option) any later version.
*
*     This program is distributed in the hope that it will be useful,
*     but WITHOUT ANY WARRANTY; without even the implied warranty of
*     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*     GNU Affero General Public License for more details.
*
*     You should have received a copy of the GNU Affero General Public License
*    along with this program.  If not, see <http://www.gnu.org/licenses/>
*
*****************************************************************/


/**
 * process
 *
 * gets all details about a user (list of groups and projects)
 *
 * @param array $user
 * @param string $is_admin
 * @return array
 * @author Robin
 */

function process($user,$is_admin = FALSE,$is_leader=FALSE,$is_editor = FALSE){
	if(!$user) throw new Exception('Unknown user2',501);
	else {
		$user['permissions'] = array();
		if(!isset($user['is_editor'])) $user['is_editor'] = 'N';
		if(isset($user['is_admin']) && $user['is_admin'] == 'Y'){
			$user['permissions'][] = 'admin';
			if($user['is_editor'] != 'Y'){
				DB::update('users', array(
				  'is_editor' => 'Y'
				  ), "user_id=%i ", $user['user_id']);
				  $user['is_editor'] = 'Y';
			}

		}
		if($user['is_editor'] == 'Y') $user['permissions'][] = 'editor';

		// if(!$is_admin) unset($user['is_admin']);
		$user['code'] = base64_encode($user['login'].":".$user['code']);
		if($user['is_active'] == 'N'){
			$leaders = $GLOBALS['DB']->select(
				"groups",
				array("[><]users" => array("leader_id" => "user_id")),
				array("users.user_id (user_id)", "users.email (email)", "users.firstname (firstname)", "users.lastname (lastname)", "users.login (login)"),
				array("AND" => array("groups.group_id" => $user['groups'][0]['group_id'], "users.is_active" => 'Y'))
			);
		}
		else{
			$user['permissions'][] = 'active';
			if(!$is_admin && !$is_leader) unset($user['activation_code']);
		}
	}
	return $user;
}

/**
 * listUsers
 *
 * list all registered and valid users with their projects and groups membership.
 *
 * @param string $loggedLogin
 * @param string $loggedCode
 * @return array
 * @author Robin
 */

function listUsers($loggedLogin, $loggedCode){
	$is_admin = check_admin(null,$loggedLogin,$loggedCode);
	$is_editor = check_editor($loggedLogin,$loggedCode);

	$institution = getUserInstitution($loggedLogin,$loggedCode);
	if($is_admin){
		$users = DB::query("SELECT * from users  ORDER BY lastname");
	}
	else{
		$users = DB::query("SELECT * from users where institution = %s ORDER BY lastname",$institution);

	}
	foreach($users as $idx => $user){

		if($is_admin || $is_editor == 'Y'){
			$users[$idx]['canEdit'] = 'Y';
		}
		else $users[$idx]['canEdit'] = 'N';
	}

	return $users;
}


/**
 * login
 *
 * Checks login. Update reset password if activation_code is used as password.
 *
 * @param array $request
 * @return array
 * @author Robin
 */

function loginGitHub($request){
	

	$users = $GLOBALS['DB']->select(
	"users",
	array("user_id", "firstname", "lastname", "login", "email", "is_active","is_editor", "is_admin", "code", "is_password_reset", "password"),
	array("login" => $request['username']));


	foreach($users as $user){
		
		if(password_verify($request['password'],$user['password'])){
			if($user['is_active'] == 'D') throw new Exception("ERROR: account has been rejected", 501);
			if($user['is_active'] == 'N') throw new Exception("ERROR: the account request has NOT yet been reviewed.", 501);
			if($user['is_password_reset'] == 'Y'){
				$GLOBALS['DB']->update('users',array('is_password_reset' => 'N'),array('user_id' => $user['user_id']));
				$user['is_password_reset'] = 'N';
			}
			break;
		}
		elseif($user['activation_code'] == $request['password'] && $user['is_password_reset'] == 'Y'){
			break;
		}
		else unset($user);
	}
	if(!isset($user) || !$user) throw new Exception("ERROR: invalid username / password", 501);
	unset($user['password']);
	return process($user);
	
}


/**
 * getUser
 *
 * gets the user. $user_id could be either int (user_id) or authdata (base64_encode(login:code));
 *
 * @param string $user_id
 * @param string $loggedLogin
 * @param string $loggedCode
 * @return array
 * @author Robin
 */

function getUser($user_id, $loggedLogin, $loggedCode){
	$is_admin = $GLOBALS['DB']->get("users","user_id",array("AND" => array("is_active" => "Y", "is_admin" => "Y", "login" => $loggedLogin, "code" => $loggedCode)));

	if(is_numeric($user_id)){
		$user = $GLOBALS['DB']->get("users",array("user_id", "firstname", "lastname", "login", "email", "institution", "is_active", "is_admin","is_editor", "code", "activation_code", "is_password_reset", 'newsletter'),array("user_id" => $user_id));
	}
	else{
		list($login,$code) = explode(":",base64_decode($user_id));
		$user = $GLOBALS['DB']->get("users",array("user_id", "firstname", "lastname", "login", "email", "institution", "is_active", "is_admin","is_editor", "code", "is_password_reset", 'newsletter'),array("AND" => array("login" => $login,"code" => $code)));
	}
	return process($user,$is_admin);
}

/**
 * register
 *
 * register a new account. The account is not active. An email is sent to the leader of the group and validate the account.
 *
 * @param stdClass $user
 * @return stdClass
 * @author Robin
 */

function register($user,$isVitalIt = FALSE){
	if(GIT == "github"){
		check_password_syntax($user['password']);
		$user['institution'] ='default.ch';
		unset( $user['password2']);
		$user['password'] = password_hash($user['password'],PASSWORD_DEFAULT);
	}
	else{
		$user['password'] = NULL;
	}
	
	$user_id = $GLOBALS['DB']->get("users","user_id",array("email" => $user['email']));
	if($user_id){
		throw new Exception("Email $user[email] is already registered", 501);
	}
	else{
		$required = array('login','firstname','lastname','email','institution');
		foreach($required as $require){
			if(!isset($user[$require]) || empty($user[$require])) throw new Exception("$require is not valid", 501);
			if(mb_detect_encoding($user[$require], 'UTF-8', true) === FALSE) $user[$require] = utf8_encode($user[$require]);
		}



		
		$user['code'] = rand_str(25);
		$user['activation_code'] = rand_str(25);
		$user['is_active'] = 'Y';
		$user['is_admin'] = 'N';
		$user['newsletter'] = 'N';

		if($isVitalIt){
			$user['is_active'] = 'Y';
		}



		DB::insert('users',(array)$user);
		$user['user_id'] = DB::insertId();


	}

	return process((array)$user);
}


/**
 * updateUser
 *
 * check permission to edit the user. Update the user details.
 * also used to validate or reject an account, from the links provided in the email to the group leader.
 *
 * @param stdClass $user
 * @param string $adminLogin
 * @param string $adminCode
 * @return array
 * @author Robin
 */


function updateUser($user,$adminLogin,$adminCode){


	$is_admin = check_admin(null,$adminLogin,$adminCode);
	$is_editor = check_editor($adminLogin,$adminCode);

	// if(GIT == "gitlab"){
		if($is_admin || $is_editor ){
			DB::update('users', array(
				'is_editor' => $user['is_editor'],
				'is_admin' => $user['is_admin'],
				'newsletter' => $user['newsletter']
			), "user_id=%i", $user['user_id']);
			$user = process((array)$user,$is_admin);
			return $user;
		}
		else{throw new Exception("Permission denied", 501); }

	// }


	// else{
// 		$user = updateUserGitHub($user,$adminLogin,$adminCode);
// 		$user = process((array)$user,$is_admin);
//
// 		return $user;
// 	}



}
function resetPassword($email){
	$user = $GLOBALS['DB']->get('users',array('user_id','firstname','login','activation_code','email'),array('email' => $email));
	if(!$user) throw new Exception("No account with this email address.",501);
	// reset password //
	$GLOBALS['DB']->update('users',array('is_password_reset' => 'Y'),array("user_id" => $user['user_id']));
	$title = "[".SITE_TITLE."] password reset";
	$body = "Dear ".$user['firstname'].", \r\n\r\n";
	$body .= "A temporary password has been created to access the ".SITE_TITLE." platform.\r\n\r\n";
	$body .= "login: ".$user['login']."\r\n";
	$body .= "password: ".$user['activation_code']."\r\n\r\n";
	$body .= "You can login at http://".$_SERVER['SERVER_NAME']."\r\n\r\n";
	$headers =    "MIME-Version: 1.0\r\n" .
	               "Content-type: text/plain; charset=utf-8; format=flowed\r\n" .
	               "Content-Transfer-Encoding: 8bit\r\n" .
				   "From: ".CONTACT_EMAIL ."\r\n" .
	               "X-Mailer: PHP" . phpversion();
	mail($user['email'],$title,$body,$headers);
	return true;
}

function check_password_syntax($password){
	if(strlen($password) < 8) throw new Exception("ERROR: the length of the password should be at least 8 characters", 501);
	if(!preg_match("/[0-9]/",$password)) throw new Exception("ERROR: the password must contain at least one numerical character", 501);
	if(!preg_match("/[a-z]/",$password)) throw new Exception("ERROR: the password must contain at least one lowercase character", 501);
	if(!preg_match("/[A-Z]/",$password)) throw new Exception("ERROR: the password must contain at least one uppercase character", 501);
	return true;
}
?>
