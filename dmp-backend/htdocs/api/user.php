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
require '../../GIT/gitHub.php';



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



function ldap_login($user){
	
	if(GIT == 'gitlab'){
		
		$login = $user['username'];
		$password = $user['password'];
		$ldap = array(
				'server' => "ldaps://ldap.vital-it.ch:636/",
				'dn' => "ou=People,dc=vital-it,dc=isb-sib,dc=ch"
			);

		$ds=ldap_connect($ldap['server']);
		

		if(!$ds){
			throw new Exception("Unable to contact authentication server",501);
		}
		$sr=ldap_search($ds, $ldap['dn'], "uid=$login");
		// Verify that we get exactly one entry
		if (ldap_count_entries($ds, $sr) != 1){
			throw new Exception("Invalid login",501);
		}
		$user_ldap_id = ldap_first_entry($ds, $sr);
		$user_dn=ldap_get_dn($ds, $user_ldap_id);
		$r = @ldap_bind($ds, $user_dn, $password);
		

		if(!$r){
			throw new Exception("Invalid password",501);
		}
		$user = ldap_get_entries($ds,$sr)[0];
		if(!$user) throw new Exception("Unknown user",401);
		$current_time = time() / 3600 / 24;
		if(isset($user['shadowExpire']) && $user['shadowExpire'][0] <= $current_time) throw new Exception("User account in NOT active", 501);


		$firstnameLastname = explode(" ", $user['cn'][0]);
		$firstname = $firstnameLastname[0];
		$lastname = $firstnameLastname[1];
		$institution = "SIB";
		$userDMP = DB::queryFirstRow("SELECT * from users
			where login = %s and firstname = %s and lastname = %s and email = %s",$login,$firstname,$lastname,$user['mail'][0]);
		if(empty($userDMP)){
			$insertUser =array(
			  'login' => $login,
			  'firstname' => $firstname ,
			  'lastname' => $lastname,
			  'password' => $password,
			  'institution' => "",
			  'email' => $user['mail'][0],
			);

			$userDMP = register($insertUser,TRUE);
		
			return $userDMP;

		}

	}
	else{
		$userDMP = loginGitHub($user);

	}




	return process($userDMP);
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

?>
