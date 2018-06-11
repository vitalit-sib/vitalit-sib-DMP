<?php
require '../vendor/autoload.php';
require '../../tools/include.php';
define('ClientID','APP-F3Q96TBNK2S3UQQ0');
define('ClientSecret','5515e41a-591a-4bfb-9560-b4f4bcbc18ff');
define('RedirectURI','https://dmp.vital-it.ch/orcid');
$verbose = false;
$code = (isset($_GET['code'])) ? filter_input(INPUT_GET,'code',FILTER_SANITIZE_SPECIAL_CHARS) : '';

if($code){
	if($verbose) error_log($code);
	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL,"http://pub.orcid.org/oauth/token");
	curl_setopt($ch, CURLOPT_POST, 1);
	// in real life you should use something like:
	curl_setopt($ch, CURLOPT_POSTFIELDS,
	         http_build_query(array(
			     'client_id' => ClientID,
			     'client_secret' => ClientSecret,
			     'grant_type' => 'authorization_code',
			     'code' => $code,
			     'redirect_uri' => RedirectURI
	         )));

	// receive server response ...
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	$json = curl_exec ($ch);
	curl_close ($ch);
	$data = json_decode($json,JSON_OBJECT_AS_ARRAY);

	if(!isset($data['orcid'])){
		header('Location: '.dirname(RedirectURI)."/#/permissionDenied");
		exit;
	}

	$user = DB::queryFirstRow("SELECT * from users where login = %s",$data['orcid']);
	if(!$user){
		$user = array(
			"login" => $data['orcid'],
			"firstname" => $data['name'],
			"lastname" => $data['name'],
			'code' => $data['access_token'],
			'activation_code' => $data['refresh_token'],
			'is_active' => 'Y',
			'institution' => 'default.ch'
		);
		DB::insert("users",$user);
		$user['user_id'] = DB::insertId();
		$user['authdata'] = base64_encode($user['login'].":".$user['code']);
		$user['permissions'] = array('active');
	}
	else{
		DB::update('users',array('code' => $data['access_token'],'activation_code' => $data['refresh_token']),'user_id = %i',$user['user_id']);

		$user['code'] = $data['access_token'];
		$user['activation_code'] = $data['refresh_token'];
		$user['permissions'] = array();
		$fields = array('admin','active','editor');
		foreach($fields as $field){
			if($user["is_".$field] == 'Y') $user['permissions'][] = $field;
		}
	}

	if(isset($user['code']) && isset($user['login']) && $user['institution'] == 'default.ch'){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,"http://pub.orcid.org/".$user['login']."/record");
		$authorization = "Authorization: Bearer ".$user['code'];
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $authorization ));
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		$json = curl_exec($ch);
		curl_close($ch);

		$updates = array(
			'code' => $user['code'],
			'activation_code' => $user['activation_code']
		);
		$result = json_decode($json,JSON_OBJECT_AS_ARRAY);
		$updates['firstname'] = implode(" ",$result['person']['name']['given-names']);
		$updates['lastname'] = implode(" ",$result['person']['name']['family-name']);
		$user['email'] = (count($result['person']['emails']['email'])) ? $result['person']['emails']['email'] : "";
		if($user['email']) $updates['email'] = $user['email'];
		$grid_id = '';
		if($user['institution'] == 'default.ch'){
			if(isset($result['activities-summary']['employments']['employment-summary']) && count($result['activities-summary']['employments']['employment-summary'])){
				$last_date = '2000-01-01';
				foreach($result['activities-summary']['employments']['employment-summary'] as $institution){
					if(!$institution['end-date'] || $institution['end-date'] > $last_date){
						if(isset($institution['organization']['disambiguated-organization']['disambiguation-source']) && $institution['organization']['disambiguated-organization']['disambiguation-source'] == 'GRID'){
							$grid_id = $institution['organization']['disambiguated-organization']['disambiguated-organization-identifier'];
						}
					}
				}
			}
			if($grid_id){
				$institution_name = DB::queryFirstField("SELECT institution_name from institutions where grid_id = %s",$grid_id);

				if($institution_name){
					$user['institution'] = $institution_name;
				}
				else{
					$institution_name = DB::queryFirstField("SELECT name from grid where grid_id = %s",$grid_id);
					if($institution_name){
						DB::insert("institutions",array('institution_name' => $institution_name,"grid_id" => $grid_id));
						$user['institution'] = $institution_name;
					}
				}
			}
		}
		if($user['institution'] == 'default.ch'){
			$user['institution'] = (preg_match("/@([a-zA-Z-\._]*\.[a-zA-Z]{2-4})$/",$user['email'],$matches)) ? $matches[1] : 'default.ch';
		}
		if($user['institution'] != 'default.ch') $updates['institution'] = $user['institution'];
		DB::update('users',$updates,'user_id = %i',$user['user_id']);
		if($verbose) {
			ob_start();
			print_r($updates);
			error_log(ob_get_clean());
		}
	}

	if($user['user_id']){
		$code = base64_encode($user['login'].":".$user['code']);
		$path = dirname(RedirectURI)."/#/login/user_id/".$user['user_id']."/login/".$user['login']."/code/".$code."/permissions/".(implode(";",$user['permissions']))."/institution/".$user['institution'];
		if($verbose) error_log($path);
		header('Location: '.$path);
	}
	else {
		header('Location: '.dirname(RedirectURI)."/#/permissionDenied");
	}
}
else{
	file_get_contents("https://pub.orcid.org/userStatus.json?logUserOut=true");
	header('Location: https://orcid.org/oauth/authorize?client_id='.ClientID.'&response_type=code&scope=/authenticate&redirect_uri='.RedirectURI);
}
