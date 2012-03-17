<?php

include 'globals.php';
include 'templates/meta.php';
include 'templates/navigation.php';
include 'templates/header.php';

/* Body here (posts, input form, etc.) */

?>

<?php

require_once SCRIPTS_PATH."Thread.class.php";
require_once SCRIPTS_PATH."Timer.class.php";


$thread = new Thread(THREADS_PATH);
$thread->selectThread($thread->spawnThread("something"));

$threadID = 3;
if ($thread->selectThread($threadID))
	echo $thread->addPost($_SERVER['REMOTE_ADDR'], "jay", 0, "some random posted shit."); 
else
	echo "Thread $threadID doesn't exist!";


?>

<?

/* Body ends */

include 'templates/footer.php';
include 'templates/meta-end.php';

?>
