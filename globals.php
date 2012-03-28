<?php

define("DEBUG", 1);

if (DEBUG == 1)
{
	ini_set('display_errors', 'On');
	error_reporting(E_ALL);
}

//global strings
define("SITE_NAME", "Beemoboard");
define("VERSION_STRING", "Version 0.1 (Alpha), March 28, 2012 (gitpull, yeah!)");
define("BR", "<br/>");

define("ROOT_PATH", dirname(__FILE__).'/');
define("IMAGES_PATH", ROOT_PATH."images/");
define("THUMBS_PATH", ROOT_PATH."thumbs/");
define("IMAGES_RELATIVE_PATH", "images/");
define("THUMBS_RELATIVE_PATH", "thumbs/");
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
