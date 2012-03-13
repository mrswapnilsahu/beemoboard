<?php

define("DEBUG", 0);

if (DEBUG == 1)
{
	ini_set('display_errors', 'On');
	error_reporting(E_ALL);
	echo "DEBUG MODE ON<br/>";
}

//global strings
define("SITE_NAME", "Beemoboard");
define("VERSION_STRING", "Version 0.1, March 12, 2012");
define("BR", "<br/>");

define("ROOT_PATH", dirname(__FILE__).'/');
define("IMAGES_PATH", ROOT_PATH."image/");
define("THUMBS_PATH", ROOT_PATH."thumb/");
define("IMAGES_RELATIVE_PATH", "image/");
define("THUMBS_RELATIVE_PATH", "thumb/");
define("SCRIPTS_PATH", ROOT_PATH."scripts/");
define("DB_PATH", ROOT_PATH."db/");
define("INCLUDES_PATH", ROOT_PATH."templates/");
define("TEMPLATES_PATH", ROOT_PATH."templates/");
define("MAX_UPLOAD_SIZE", 1024);
define("KILO", 1000);

?>
