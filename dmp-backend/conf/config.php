<?php

// The email address of the ViKM administrator
if(!defined("CONTACT_EMAIL")) define("CONTACT_EMAIL","fabio.lehmann@sib.swiss");

// The name of the ViKM instance
if(!defined("SITE_TITLE")) define("SITE_TITLE","DMP APP");

// Path to the data directory. All datasets will be stored in this directory.
// This directory must be writable by apache user.
if(!defined('DATA_PATH')) define('DATA_PATH',__DIR__."/../data");
// This directory must be writable by apache user.
if(!defined('TMP_PATH')) define('TMP_PATH',"/tmp");

// The type of RDBMS to use
if(!defined("DBTYPE")) define("DBTYPE","mysql");

// For MySQL connection

// if(!defined("DBBASE")) define("DBBASE","dmp_github");
// if(!defined("DBSERVER")) define("DBSERVER","127.0.0.1");
// if(!defined("DBNAME")) define("DBNAME","mysql_username");
// if(!defined("DBPWD")) define("DBPWD","mysql_user_password");

// For Sqlite3 connection. Path to the DB file
if(!defined("DBFILE")) define("DBFILE",DATA_PATH."/database.sqlite");

// Path to the tools directory
if(!defined('INCLUDE_PATH')) define("INCLUDE_PATH",dirname(__FILE__)."/../tools/");

// Whether to server should accept Cross-Origin request or not. Should be set to false in production.
if(!defined('CORS')) define('CORS',true);

// Set the debug state of the application. Might be used to display some debugging messages
if(!defined('DEBUG')) define('DEBUG',true);

// Path to pandoc soft
if(!defined('PANDOC_PATH')) define('PANDOC_PATH',"/usr/local/bin/pandoc");

// Path to pandoc reference
if(!defined('PANDOC_REFERENCE')) define('PANDOC_REFERENCE',"/Users/flehmann/.pandoc/reference.docx");

// Define if gitlab or github
if(!defined("GIT")) define("GIT","github");

// admin PWD = 4vita_it!DMPadmin?
?>
