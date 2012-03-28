<?php
ini_set('display_errors', 'On');
error_reporting(E_ALL);

/*if ($_SERVER['REMOTE_ADDR'] == "207.97.227.253" ||
	 $_SERVER['REMOTE_ADDR'] == "50.57.128.197")
{*/
	exec("git pull", $output);
	echo "<pre>";
	print_r($output);
//}
?>
