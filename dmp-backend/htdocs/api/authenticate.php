<?php
require '../vendor/autoload.php';
require '../../tools/include.php';
require 'user.php';
function switchaai_login(){
	$user = array();
	if (isset($_SERVER['Shib-Identity-Provider'])){

	  // User has Shibboleth session
	  if (isset($_SERVER['uniqueID'])){
		  $user['lastname'] = (mb_detect_encoding($_SERVER['surname']) !== 'UTF-8') ? utf8_encode($_SERVER['surname']) : $_SERVER['surname'];
		  $user['firstname'] = (mb_detect_encoding($_SERVER['givenName']) !== 'UTF-8') ? utf8_encode($_SERVER['givenName']) : $_SERVER['givenName'];
		  $user['email'] = $_SERVER['mail'];
		  $user['login'] = $_SERVER['uniqueID'];
		  $user['institution'] = $_SERVER['homeOrganization'];
		  $user['is_active'] = 'Y';


		  $db_user = DB::queryFirstRow("SELECT user_id,firstname, lastname,login, email, institution, is_active,is_admin,is_editor,code from users where login = %s",$user['login']);
		  if($db_user){
			  if(
			  	$user['lastname'] != $db_user['lastname']
				|| $user['firstname'] != $db_user['firstname']
				|| $user['email'] != $db_user['email']
				|| $user['login'] != $db_user['login']
				|| $user['institution'] != $db_user['institution']
				|| $user['is_active'] != $db_user['is_active']
			  ){
				  DB::update("users",array(
					  "lastname" => $user['lastname'],
					  "firstname" => $user['firstname'],
					  "email" => $user['email'],
					  "login" => $user['login'],
					  "institution" => $user['institution'],
					  "is_active" => 'Y'
				  ),'user_id = %i',$db_user['user_id']);
			  }
			  $user = process($db_user);
		  }
		  else{
			  $user = register($user);
		  }


	   } else {
		   throw new Exception("ERROR: invalid uniqueID from SwitchAAI", 501);

	   }
	} else {
		throw new Exception("ERROR: no Shibboleth session", 501);

	}
	return $user;
}

$user = switchaai_login();
header("Location: https://dmp.vital-it.ch/#/login/user_id/".$user['user_id']."/login/".$user['login']."/code/".$user['code']."/permissions/".implode(";",$user['permissions'])."/institution/".$user['institution']);

?>