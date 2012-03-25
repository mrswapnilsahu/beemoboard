<?php

include 'globals.php';
require_once SCRIPTS_PATH."Beemo.class.php";
require_once SCRIPTS_PATH."Thread.class.php";
require_once SCRIPTS_PATH."Timer.class.php";
$timer = new Timer(1);

$bmo = new Beemo(DB_PATH."defaultconfig.csv");

$thread = new Thread(THREADS_PATH);

$threadID = 1;
if ($thread->selectThread($threadID) == 0)
	$thread->selectThread($thread->spawnThread("something"));

$pageName = $bmo->getConfig('BOARD_TITLE');
include TEMPLATES_PATH.'meta.php';
include TEMPLATES_PATH.'navigation.php';
include TEMPLATES_PATH.'header.php';

/* Body here (posts, input form, etc.) */
$postURL = $_SERVER['PHP_SELF'];
$formStyle = "THREAD";
include TEMPLATES_PATH.'post_form.php';

?>
<div id="post_container">
<?php

$bmo->getActiveThreads($aThreadList, THREADS_PATH);

?>
</div>
<?

/* Body ends */

include TEMPLATES_PATH.'footer.php';
include TEMPLATES_PATH.'meta-end.php';

echo "Did EVERYTHING in ".$timer->getElapsedTime()." seconds.";

?>
