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
function check_password_syntax($password){
	if(strlen($password) < 8) throw new Exception("ERROR: the length of the password should be at least 8 characters", 501);
	if(!preg_match("/[0-9]/",$password)) throw new Exception("ERROR: the password must contain at least one numerical character", 501);
	if(!preg_match("/[a-z]/",$password)) throw new Exception("ERROR: the password must contain at least one lowercase character", 501);
	if(!preg_match("/[A-Z]/",$password)) throw new Exception("ERROR: the password must contain at least one uppercase character", 501);
	return true;
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


function updateUserGitHub($user,$adminLogin,$adminCode,$is_admin){
	
	
	$admin_id = $GLOBALS['DB']->get('users','user_id',array("AND" => array("login" => $adminLogin,'code' => $adminCode,'is_active' => 'Y')));
	$admin_leader_groups = ($admin_id) ? $GLOBALS['DB']->select('groups','group_id',array("leader_id" => $admin_id)) : array();
	$dbUser = $GLOBALS['DB']->get("users",array("user_id","login","firstname","lastname","password","institution","email","is_admin","is_active","code","activation_code","is_password_reset","newsletter"),array("user_id" => $user['user_id']));



	$dbUser['groups'] = $GLOBALS['DB']->select('user_groups','group_id',array("user_id" => $user['user_id']));
	$is_leader = count(array_intersect($admin_leader_groups,$dbUser['groups']));
	if(!$dbUser) throw new Exception("Unknown user",501);
	if(!isset($user['password'])) $user['password'] = $dbUser['password'];
	if(!isset($user['password2'])) $user['password2'] = $dbUser['password'];
	if($user['password'] != $user['password2']) throw new Exception("Password was not confirmed correctly", 501);

	unset($user['password2']);

	// activate account
	if($dbUser['is_active'] == 'N' && $user['is_active'] == 'Y'){ // we set active

		if(!check_leader($dbUser['groups'][0],$admin_id) && !check_admin($admin_id)) throw new Exception("Permission denied. Leader is not valid", 501);

		$GLOBALS['DB']->update('users',array("is_active" => 'Y'),array("user_id" => $user['user_id']));

		return true;
	}
	// reject account
	elseif($user['is_active'] == 'R'){
		if(!check_leader($dbUser['groups'][0],$admin_id) && !check_admin($admin_id)) throw new Exception("Permission denied. Leader is not valid", 501);
		$leader = $GLOBALS['DB']->get('users',array('firstname','lastname','email'),array('user_id' => $admin_id));
		$GLOBALS['DB']->update('users',array("is_active" => 'R'),array("user_id" => $user['user_id']));

		// send email //
		return true;
	}

	else{
		$is_user = ($adminLogin == $dbUser['login']);
		if(!$is_admin && !$is_user && !$is_leader) throw new Exception("Permission denied", 501);
		$fields = array('firstname','lastname','password','email', 'institution','newsletter');
		if($is_admin){
			$fields[] = 'is_admin';
			$fields[] = 'is_active';
		}
		$updates = array();
		foreach($fields as $field){
			if((!isset($user[$field]) || empty($user[$field])) && $field != 'institution') throw new Exception("$field is not valid", 501);
			if($is_admin){
				if(!in_array($user['is_admin'],array('Y','N'))) throw new Exception("Is Admin is not valid", 501);
				if(!in_array($user['is_editor'],array('Y','N'))) throw new Exception("Is Editor is not valid", 501);
				if(!in_array($user['is_active'], array('Y','N'))) throw new Exception("Is Active is not valid", 501);
				if(!in_array($user['newsletter'], array('Y','N'))) throw new Exception("Newsletter is not valid", 501);
			}
			if($field == 'password' && $dbUser['password'] != $user['password']){
				check_password_syntax($user['password']);
				$user['password'] = password_hash($user['password'],PASSWORD_DEFAULT);
				$updates['is_password_reset'] = 'N';
			}
			$updates[$field] = $user[$field];
		}
		$GLOBALS['DB']->update('users',$updates,array('user_id' => $user['user_id']));
		$user['password2'] = $user['password'];
		return $user;
	
 

	
	}
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
	return $user;
	
}



?>