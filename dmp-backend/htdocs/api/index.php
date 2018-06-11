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
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
require '../vendor/autoload.php';
require '../../tools/include.php';

// this should activate the debug mode
$app = new \Slim\App([
    'settings' => [
        'displayErrorDetails' => true,
    ]
]);

/**
 * authenticate
 *
 * route middleware for authentication. Gets user login and code from Authorization basic header.
 *
 * @param Route $route
 * @return boolean
 * @author Robin
 */

function authenticate( ){
	$app = new \Slim\App;
	$c = $app->getContainer();
	$req = $c->get('request');
	$headers = $req->getHeaders();
	$login = $headers['PHP_AUTH_USER'][0];
	$code = $headers['PHP_AUTH_PW'][0];
	$user = $GLOBALS['DB']->get("users",array('user_id','is_active','is_admin'),array('AND' => array('login' => $login,'code' => $code)));
	if(!$user) throw new Exception('Invalid username and / or password',501);

	if($user['is_active'] == 'N' && urldecode($req->getUri()->getPath()) !== 'user/'.base64_encode($login.":".$code)) throw new Exception('The account is not active',501);

	return true;
}



$authenticate = function ($request, $response, $next) {
	 authenticate();
	 $newResponse = $next($request, $response);
    return $newResponse;
};


/**
 * check_leader
 *
 * check if a user is a leader of a given group.
 *
 * @param int $group_id
 * @param int $leaderID
 * @return int
 * @author Robin
 */
function check_leader($group_id, $leaderID){
	$groups = $GLOBALS['DB']->select("groups",array('[><]users' => array("leader_id" => "user_id")),array("group_id"),array('AND' => array("users.is_active" => "Y", "users.user_id" => $leaderID,"groups.group_id" => $group_id)));
	$group_id = ($groups && count($groups)) ? $groups[0]['group_id'] : 0;
	return $group_id;
}

/**
 * check_admin
 *
 * provide a user either with a _user_id_ or a _login_ and _code_. Checks if _is_admin_ == 'Y'
 *
 * @param int $ID
 * @param string $login
 * @param string $code
 * @return void
 * @author Robin
 */

function check_admin($ID = '', $login = '', $code = ''){
	if($ID){
		$is_admin = $GLOBALS['DB']->get("users",'is_admin',array('AND' => array("user_id" => $ID,'is_active' => 'Y', 'is_admin' => 'Y')));
	}
	else{
		$is_admin = $GLOBALS['DB']->get('users','is_admin',array('AND' => array('login' => $login, 'code' => $code, 'is_active' => 'Y', 'is_admin' => 'Y')));
	}
	return $is_admin;
}


function check_editor($loggedLogin, $loggedCode){
	$is_editor = DB::queryFirstField("SELECT is_editor from users where login = %s and code=%s",$loggedLogin, $loggedCode);
	return $is_editor;
}

function getUserInstitution($loggedLogin, $loggedCode){
	$institution = DB::queryFirstField("SELECT institution from users where login = %s and code=%s",$loggedLogin, $loggedCode);
	return $institution;
}


function is_localhost(){

	$app = new \Slim\App;
	$container = $app->getContainer();
	$environment = $container['environment']['HTTP_ORIGIN'];
	$localhost =  explode(":", $environment);
	if($localhost[1] == "//localhost"){return TRUE;}
	else{return FALSE;}
}

function db_log($query){
	error_log(preg_replace("/\s+/"," ",$query['query']));
}


$c = $app->getContainer();
$c['errorHandler'] = function ($c) {
    return function ($request, $response, $exception) use ($c) {
		$statusCode = ($exception->getCode() !== 501) ? 500 : 501;
        return $c['response']->withStatus($statusCode)
                             ->withHeader('Content-Type', 'application/json')
                             ->write(json_encode($exception->getMessage()));
    };
};

// function to get the list of user from the website
$app->get('/user',function ($request,$response) {
	require 'user.php';
	$headers = $request->getHeaders();
	$loggedLogin = $headers['PHP_AUTH_USER'][0];
	$loggedCode = $headers['PHP_AUTH_PW'][0];
	$users = listUsers($loggedLogin,$loggedCode);
	return 	$response = $response->withJson($users);

})->add($authenticate);
//  get user info based on his id number
$app->get('/user/{user_id}', function ($request,$response,$args) {
	require 'user.php';
	$user_id = $args['user_id'];
	$headers = $request->getHeaders();
	$leaderLogin = $headers['PHP_AUTH_USER'][0];
	$leaderCode = $headers['PHP_AUTH_PW'][0];
	$user = getUser($user_id,$leaderLogin,$leaderCode);
	return 	$response = $response->withJson($user);

})->add($authenticate);

$app->post('/user', function ($request,$response) {
	require 'user.php';
	$request = $request->getParsedBody();
	$user = register($request);
	echo safe_json_encode($user);
});

$app->put('/user', function ($request,$response) {
	require 'user.php';
	$headers = $request->getHeaders();
	$is_localhost = is_localhost();
	$leaderLogin = $headers['PHP_AUTH_USER'][0];
	$leaderCode = $headers['PHP_AUTH_PW'][0];
	$request = $request->getParsedBody();
	$user = updateUser($request,$leaderLogin,$leaderCode);
	return $response->withJson($user);
})->add($authenticate);

$app->post('/resetpass', function ($request,$response) {
	require 'user.php';
	$request = $request->getParsedBody();
	echo resetPassword($request['email']);
});


// identifcation function
$app->post('/authenticate', function ($request,$response) {
	require 'user.php';
	$user = $request->getParsedBody();
	$login = ldap_login($user);
	return 	$response = $response->withJson($login);
});


// send the formulary in a json format to the front
$app->get('/form', function ($request,$response) {
	require 'form.php';
	$headers = $request->getHeaders();
	$loggedLogin = $headers['PHP_AUTH_USER'][0];
	$loggedCode = $headers['PHP_AUTH_PW'][0];
	$form = getForm($loggedLogin,$loggedCode);
	return $response->withJson($form);

})->add($authenticate);

// receive the form from the front to create the dmp canavas
$app->put('/form', function ($request,$response) {
	require INCLUDE_PATH.'/php2Word.php';
	$headers = $request->getHeaders();
	$loggedLogin = $headers['PHP_AUTH_USER'][0];
	$loggedCode = $headers['PHP_AUTH_PW'][0];
	$request = $request->getParsedBody();
	$code = create_html_doc($request,$loggedLogin,$loggedCode);
	return $code;
})->add($authenticate);

// Once the creat_html_doc function is finished, download the docx file
$app->get('/download/{userID}/code/{code}', function ($request,$response,$args) {
	require 'form.php';
	$headers = $request->getHeaders();
	$user_id = $args['userID'];
	$code = $args['code'];
	getReadyForDownload($user_id,$code);
});


// allows the user to load a formulary previously filled
$app->get('/template/{templateId}', function ($request,$response,$args) {
	require 'form.php';
	$headers = $request->getHeaders();
	$loggedLogin = $headers['PHP_AUTH_USER'][0];
	$loggedCode = $headers['PHP_AUTH_PW'][0];
	$templateId= $args['templateId'];
	$previousTemplate = getPreviousTemplate($templateId);
	return $response->withJson($previousTemplate);
})->add($authenticate);



// allows the user to delete previous formulary
$app->delete('/template/{template_id}', function ($request,$response ,$args) {
	require 'form.php';
	$headers = $request->getHeaders();
	$loggedLogin = $headers['PHP_AUTH_USER'][0];
	$loggedCode = $headers['PHP_AUTH_PW'][0];
	$template_id = $args['template_id'];
	$deletedTemplate = deleteTemplate($template_id,$loggedLogin,$loggedCode);
	return $response->withJson($deletedTemplate);
})->add($authenticate);

$app->get('/news', function ($request,$response,$args) {
	require 'news.php';
	$headers = $request->getHeaders();
	$loggedLogin = $headers['PHP_AUTH_USER'][0];
	$loggedCode = $headers['PHP_AUTH_PW'][0];
	$news = listNews($loggedLogin,$loggedCode);
	return $response->withJson($news);

})->add($authenticate);



$app->get('/news/{news_id}', function ($request,$response,$args) {
	require 'news.php';
	$headers = $request->getHeaders();
	$loggedLogin = $headers['PHP_AUTH_USER'][0];
	$loggedCode = $headers['PHP_AUTH_PW'][0];
	$news_id = $args['news_id'];
	$news = getNews($news_id,$loggedLogin,$loggedCode);
	return  $response->withJson($news);

})->add($authenticate);

$app->put('/news', function ($request,$response) {
	require 'news.php';
	$headers = $request->getHeaders();
	$loggedLogin = $headers['PHP_AUTH_USER'][0];
	$loggedCode = $headers['PHP_AUTH_PW'][0];
	$request = $request->getParsedBody();
	$news =  updateNews($request,$loggedLogin,$loggedCode);
	return $response->withJson($news);
})->add($authenticate);


$app->delete('/news/{news_id}', function ($request,$response,$args) {
	require 'news.php';
	$headers = $request->getHeaders();
	$loggedLogin = $headers['PHP_AUTH_USER'][0];
	$loggedCode = $headers['PHP_AUTH_PW'][0];
	$news_id = $args['news_id'];
	$news = deleteNews($news_id,$loggedLogin,$loggedCode);
	return $response->withJson($news);

})->add($authenticate);


$app->post('/project/news', function ($request,$response,$args) {
	require 'news.php';
	$headers = $request->getHeaders();
	$loggedLogin = $headers['PHP_AUTH_USER'][0];
	$loggedCode = $headers['PHP_AUTH_PW'][0];
	$project_id = $args['project_id'];
	$request = $request->getParsedBody();
	$news = createNews($request,$loggedLogin,$loggedCode);
	return  $response->withJson($news);
})->add($authenticate);

$app->get('/templates', function ($request,$response,$args) {
	require 'templates.php';
	$headers = $request->getHeaders();
	$loggedLogin = $headers['PHP_AUTH_USER'][0];
	$loggedCode = $headers['PHP_AUTH_PW'][0];
	$templates = getAlltemplates($loggedLogin,$loggedCode);
	return $response->withJson($templates);

})->add($authenticate);

$app->post('/templateSpecific', function ($request,$response,$args) {
	require 'templates.php';
	$headers = $request->getHeaders();
	$loggedLogin = $headers['PHP_AUTH_USER'][0];
	$loggedCode = $headers['PHP_AUTH_PW'][0];
	$request = $request->getParsedBody();
	$templateSpecific = createTemplateSpecific($request,$loggedLogin,$loggedCode);
	return  $response->withJson($templateSpecific);
})->add($authenticate);



$app->get('/recommendation', function ($request,$response,$args) {
	require 'templates.php';
	$headers = $request->getHeaders();
	$loggedLogin = $headers['PHP_AUTH_USER'][0];
	$loggedCode = $headers['PHP_AUTH_PW'][0];
	$recommendations = getRecommendations($loggedLogin,$loggedCode);
	return $response->withJson($recommendations);

})->add($authenticate);

$app->post('/recommendation', function ($request,$response,$args) {
	require 'templates.php';
	$headers = $request->getHeaders();
	$loggedLogin = $headers['PHP_AUTH_USER'][0];
	$loggedCode = $headers['PHP_AUTH_PW'][0];
	$request = $request->getParsedBody();
	addRecommendation($request,$loggedLogin,$loggedCode);
})->add($authenticate);
// /not_parameter[/not_parameter_id]

$app->get('/paramKeyNames', function ($request,$response,$args) {
	require 'templates.php';
	$headers = $request->getHeaders();
	$loggedLogin = $headers['PHP_AUTH_USER'][0];
	$loggedCode = $headers['PHP_AUTH_PW'][0];
	$paramKeyNames = getParamKeyNames($loggedLogin,$loggedCode);
	return $response->withJson($paramKeyNames);

})->add($authenticate);


$app->get('/aboutContact', function ($request,$response,$args) {
	require 'templates.php';
	$headers = $request->getHeaders();
	$loggedLogin = $headers['PHP_AUTH_USER'][0];
	$loggedCode = $headers['PHP_AUTH_PW'][0];
	$recommendations = getAboutContact($loggedLogin,$loggedCode);
	return $response->withJson($recommendations);

})->add($authenticate);

$app->post('/aboutContact', function ($request,$response,$args) {
	require 'templates.php';
	$headers = $request->getHeaders();
	$loggedLogin = $headers['PHP_AUTH_USER'][0];
	$loggedCode = $headers['PHP_AUTH_PW'][0];
	$request = $request->getParsedBody();
	addAboutContact($request,$loggedLogin,$loggedCode);
})->add($authenticate);

$app->get('/git', function ($request,$response,$args) {
	return $response->withJson([GIT]);

});

$app->run();

?>