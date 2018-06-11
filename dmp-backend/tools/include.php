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


require_once __DIR__."/../conf/config.php";
require_once INCLUDE_PATH."db.inc.php";
########################################
#Function CORS
#

function cors(){
	if(php_sapi_name() === 'cli') return;
        if (isset($_SERVER['HTTP_ORIGIN'])) {
                header("Access-Control-Allow-Credentials: true");
                header("Access-Control-Allow-Origin: http://localhost:9000");
                header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
                header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization");
        }

        // Access-Control headers are received during OPTIONS requests
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

                if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])){
                        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
                }
                header( "HTTP/1.1 200 OK" );
                exit();
        }
}

function rand_str($length = 25, $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890')
{
        // Length of character list
        $chars_length = (strlen($chars) - 1);

        // Start our string
        $string = $chars{rand(0, $chars_length)};

        // Generate random string
        for ($i = 1; $i < $length; $i = strlen($string))
        {
                // Grab a random character from our list
                $r = $chars{rand(0, $chars_length)};

                // Make sure the same two characters don't appear next to each other
                if ($r != $string{$i - 1}) $string .=  $r;
        }

        // Return the string
        return $string;
}
function rmdirtree($dirname) {
    if (is_dir($dirname)) {    //Operate on dirs only
        $result=array();
        if (substr($dirname,-1)!='/') {$dirname.='/';}    //Append slash if necessary
        $handle = opendir($dirname);
        while (false !== ($file = readdir($handle))) {
            if ($file!='.' && $file!= '..') {    //Ignore . and ..
                $path = $dirname.$file;
                if (is_dir($path)) {    //Recurse if subdir, Delete if file
                    $result=array_merge($result,rmdirtree($path));
                }
                                else{
                    if(is_writable($path)||is_link($path)) unlink($path);
                    $result[].=$path;
                }
            }
        }
        closedir($handle);
        rmdir($dirname);    //Remove dir
        $result[].=$dirname;
        return $result;    //Return array of deleted items
    }else{
        return false;    //Return false if attempting to operate on a file
    }
}

function encode_items(&$item, $key)
{
    if(is_string($item)) $item = utf8_encode($item);
}

function safe_json_encode($data){
	if(is_array($data)) array_walk_recursive($data,'encode_items');
	return json_encode($data);
}


ini_set("magic_quotes_sybase",1);
date_default_timezone_set("Europe/Zurich") ;
if(CORS){
	cors();

}
?>
