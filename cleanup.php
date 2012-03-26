#!/usr/bin/php
<?php

require_once("globals.php");
require_once(SCRIPTS_PATH."Beemo.class.php");

$bmo = new Beemo();
$bmo->pruneThreads();

?>
