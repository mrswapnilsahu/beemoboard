<?php
ini_set('display_errors', 'On');
error_reporting(E_ALL);

exec("git pull", $output);
echo "<pre>";
print_r($output);
?>
