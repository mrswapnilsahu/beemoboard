<?php

define("DEBUG", 1);

if (DEBUG == 1)
{
	ini_set('display_errors', 'On');
	error_reporting(E_ALL);
	//echo "DEBUG MODE ON<br/>";
}

//global strings
define("SITE_NAME", "Beemoboard");
define("VERSION_STRING", "Version 0.1 (Alpha), March 21, 2012");
define("BR", "<br/>");

define("ROOT_PATH", dirname(__FILE__).'/');
define("IMAGES_PATH", ROOT_PATH."image/");
define("THUMBS_PATH", ROOT_PATH."thumb/");
define("IMAGES_RELATIVE_PATH", "image/");
define("THUMBS_RELATIVE_PATH", "thumb/");
define("SCRIPTS_PATH", ROOT_PATH."scripts/");
define("DB_PATH", ROOT_PATH."db/");
define("THREADS_PATH", ROOT_PATH."threads/");
define("INCLUDES_PATH", ROOT_PATH."templates/");
define("TEMPLATES_PATH", ROOT_PATH."templates/");
define("MAX_UPLOAD_SIZE", 1024);
define("KILO", 1000);

//page names
define("E404_PAGE", "404.php");

?>
