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



require __DIR__.'/../htdocs/vendor/autoload.php';

ini_set("memory_limit","256M");

use Medoo\Medoo;

if(DBTYPE == 'sqlite'){
	$GLOBALS['DB'] = new medoo([
		'database_type' => DBTYPE,
		'database_file' => DBFILE
	]);
}
elseif(DBTYPE == 'mysql'){
	$GLOBALS['DB'] = new medoo([
		// required
		'database_type' => DBTYPE,
		'database_name' => DBBASE,
		'server' => DBSERVER,
		'username' => DBNAME,
		'password' => DBPWD,
		'charset' => 'UTF8'

		// // optional
		// 'port' => 3306,
		// // driver_option for connection, read more from http://www.php.net/manual/en/pdo.setattribute.php
		// 'option' => [
		// 	PDO::ATTR_CASE => PDO::CASE_NATURAL
		// ]
	]);

	// meekro conf
	DB::$user = DBNAME;
	DB::$password = DBPWD;
	DB::$dbName = DBBASE;
	DB::$host = DBSERVER; //defaults to localhost if omitted
	DB::$encoding = 'utf8'; // defaults to latin1 if omitted

	DB::$error_handler = 'my_error_handler';


	function my_error_handler($params) {
	  error_log("SQL Error: " . $params['error']);
	  error_log("SQL Query: " . $params['query']);
	  throw new Exception("Error Processing Request", 501);

	  die; // don't want to keep going if a query broke
	}

}

?>