<?php

include 'globals.php';
require_once SCRIPTS_PATH."Beemo.class.php";
require_once SCRIPTS_PATH."Thread.class.php";
require_once SCRIPTS_PATH."Timer.class.php";
$timer = new Timer(1);

$bmo = new Beemo(DB_PATH."defaultconfig.csv");
$pageName = $bmo->getConfig('BOARD_TITLE');

include 'templates/meta.php';
include 'templates/navigation.php';
include 'templates/header.php';

/* Body here (posts, input form, etc.) */

include 'templates/post_form.php';

?>
<div id="post_container">
<?php

$thread = new Thread(THREADS_PATH);

if ($thread->selectThread(1) == 0)
	$thread->selectThread($thread->spawnThread("something"));

$threadID = 1;
if ($thread->selectThread($threadID))
{
	//echo $thread->addPost($_SERVER['REMOTE_ADDR'], "jay", 0, "some random posted shit.").BR;
	//echo "Number of posts: ".$thread->numPosts().BR;
	//echo "NumPosts: ".$thread->getAllPosts($aPostData).BR;
	/*$thread->getPost($apost, 3);
	print_r($apost);*/
	//$thread->getNewestPosts($aPostData, 3);
	//print_r($aPostData);
	
}
else
	echo "Thread $threadID doesn't exist!";


?>
</div>
<?

/* Body ends */

include 'templates/footer.php';
include 'templates/meta-end.php';

echo "Did EVERYTHING in ".$timer->getElapsedTime()." seconds.";

?>
