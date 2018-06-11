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
 * check_news
 *
 * checks the validity of a news (contains title, content and author which is admin).
 *
 * @param string $news
 * @param string $loggedLogin
 * @param string $loggedCode
 * @return array
 * @author Robin
 */

function check_news($news,$loggedLogin,$loggedCode){
	
	$is_admin = check_admin(null,$loggedLogin,$loggedCode);
	$is_editor = DB::queryFirstField("SELECT is_editor from users where login = %s and code = %s",$loggedLogin, $loggedCode);
	
	
	if($is_editor == "N") throw new Exception("Permission denied",501);
	
	if($news['is_active'] == "N" and !$is_admin)throw new Exception("Permission denied",501);
	$news['user_id'] = $GLOBALS['DB']->get("users","user_id",array('AND' => array("login" => $loggedLogin, "code" => $loggedCode)));
	if(!$news['title'] || !$news['content'] || !$news['user_id']) throw new Exception("News invalid. Missing title or content", 501);
	return $news;
}

/**
 * listNews
 *
 * lists the news for a given user in a given project.
 *
 * @param int $project_id
 * @param string $loggedLogin
 * @param string $loggedCode
 * @return array
 * @author Robin
 */

function listNews( $loggedLogin, $loggedCode){
	$is_admin = check_admin(null,$loggedLogin,$loggedCode);
	$curdate = date('Y-m-d');
	
	$institution = DB::queryFirstField("SELECT institution from users where login = %s and code = %s",$loggedLogin, $loggedCode);
	
	
	

	DB::$param_char = '##';
	$news = DB::query("SELECT news.content as content ,DATE_FORMAT(timestamp,'%d.%m.%Y') as timestamp,news.expiration_date as  expiration_date, news.is_active as active, news_id,title, firstname, lastname, news.user_id as user_id,institution_name  from news
		left join users on news.user_id = users.user_id
	where (expiration_date >= ##s or expiration_date is null) and (institution_name is null or institution_name = ##s)
	",$curdate,$institution);
	

	DB::$param_char = '%';

	
	
	foreach($news as $idx => $info){
		
		// $news[$idx]['timestamp'] = date('d.m.Y',strtotime($info['timestamp']));
	}
	
	return $news;
}
/**
 * getNews
 *
 * restricted to *admin*. Gets all details of a news for edition.
 *
 * @param int $news_id
 * @param string $loggedLogin
 * @param string $loggedCode
 * @return array
 * @author Robin
 */

function getNews($news_id,$loggedLogin, $loggedCode){
	// $is_admin = check_admin(null,$loggedLogin,$loggedCode);
	// if(!$is_admin) throw new Exception("Permission  denied",501);
	$news = $GLOBALS['DB']->select(
	'news',
	array(
		"[>]users" => "user_id"
	),
	array(
		"news.news_id",
		"news.title",
		"news.content",
		"news.institution_name",
		"news.timestamp",
		"news.user_id",
		"news.is_active",
		"users.firstname",
		"users.lastname",
		"news.expiration_date"
	),
	array(
		"news.news_id" => $news_id
	)
	);
	if(!count($news)) throw new Exception("No news can be found", 501);
	$theNews = $news[0];
	$theNews['timestamp'] = date('d.m.Y',strtotime($theNews['timestamp']));
	
	check_news($theNews,$loggedLogin, $loggedCode);
	
	return $theNews;

}

/**
 * createNews
 *
 * Register a news. Restricted to admin via the check_news function.
 *
 * @param stdClass $news
 * @param string $loggedLogin
 * @param string $loggedCode
 * @return array
 * @author Robin
 */

function createNews($news,$loggedLogin, $loggedCode){
	$news = check_news($news,$loggedLogin, $loggedCode);
	$newsletter = false;
	
	
	if($news->newsletter){ $newsletter = true; unset($news->newsletter);}
	
	if($news["is_active"] == "N"){$news["institution_name"]=NULL;}
	else{$news["institution_name"]=getUserInstitution($loggedLogin, $loggedCode);}
	
	
	
	DB::insert('news', array(
	  'user_id' => $news["user_id"],
	  'title' => $news["title"],
	  'content' => $news["content"],
	  'is_active' =>$news["is_active"],
	  'expiration_date'=>$news["expiration_date"],
	  'institution_name' => $news["institution_name"]
	));


	if($newsletter){
		$user_newsletter = $GLOBALS['DB']->select(
			"users",
			array(
				"firstname",
				"lastname",
				"email"
			),
			array("users.newsletter" => 'Y')
		);

		$content = "Last news : ".$news['title']."\r\n\r\n";
		$content = $news['title'] . "\r\n\r\n";

		foreach($user_newsletter as $user){
			$headers = 'From: '. CONTACT_EMAIL . "\r\n" .
			    'Reply-To: '. CONTACT_EMAIL . "\r\n" .
			    'X-Mailer: PHP/' . phpversion();
			$out = mail($user['email'],'VIKM - newsletter ',$content,$headers);
		}
	}
	return (count($list)) ? $list[0] : array();
}

/**
 * updateNews
 *
 * Update a news. Restricted to admin via the check_news function.
 *
 * @param stdClass $news
 * @param string $loggedLogin
 * @param string $loggedCode
 * @return array
 * @author Robin
 */


function updateNews($news,$loggedLogin, $loggedCode){
	$news = check_news($news,$loggedLogin, $loggedCode);
	if($news["is_active"] == "N"){$news["institution_name"]=NULL;}
	else{$news["institution_name"]=getUserInstitution($loggedLogin, $loggedCode);}
	
	
	$GLOBALS['DB']->update('news',array(
		'title' => $news['title'],
		'content' => $news['content'],
		'institution_name' => $news['institution_name'],
		'expiration_date' => $news['expiration_date']
		
	),array('news_id' => $news['news_id']));

	$list = $GLOBALS['DB']->select(
		"news",
		array(
			"[><]users" => "user_id"
		),
		array(
			"firstname",
			"lastname",
			"news.news_id",
			"news.user_id",
			"news.title",
			"news.content",
			"news.timestamp",
			"news.is_active",
			"news.institution_name",
			"news.expiration_date"
		),
		array("news.news_id" => $news['news_id'])
	);

	return (count($list)) ? $list[0] : array();

}

/**
 * deleteNews
 *
 * deleted the news.
 *
 * @param id $new_id
 * @param string $loggedLogin
 * @param string $loggedCode
 * @return boolean
 * @author Robin
 */

function deleteNews($news_id,$loggedLogin, $loggedCode){
	
	$news=DB::queryFirstRow("SELECT * from news where news_id =%i",$news_id);
	
	
	check_news($news,$loggedLogin, $loggedCode);
	
	$GLOBALS['DB']->delete('news',array("news_id" => $news_id));
	return true;
}





?>