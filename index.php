<?php

include 'globals.php';
require_once SCRIPTS_PATH."Beemo.class.php";
require_once SCRIPTS_PATH."Thread.class.php";
require_once SCRIPTS_PATH."Timer.class.php";
$timer = new Timer(1);

$bmo = new Beemo(DB_PATH."defaultconfig.csv");
$pageName = $bmo->getConfig('BOARD_TITLE');

$thread = new Thread(THREADS_PATH);

if ($thread->selectThread(1) == 0)
	$thread->selectThread($thread->spawnThread("something"));
	
$threadID = 1;
if ($thread->selectThread($threadID))
{
	$gotThread = 1;
}
else
{
	//$loc = 404_PAGE;
	header("Location: ".E404_PAGE);
	//echo 404_PAGE;
	
}
	

include TEMPLATES_PATH.'meta.php';
include TEMPLATES_PATH.'navigation.php';
include TEMPLATES_PATH.'header.php';

/* Body here (posts, input form, etc.) */

include TEMPLATES_PATH.'post_form.php';

?>
<div id="post_container">
<?php

if ($gotThread)
{
	if ($thread->numPosts() < 12)
		$thread->addPost($_SERVER['REMOTE_ADDR'], "jay", 0, "lolz");
		
	$numPosts = $thread->getAllPosts($aPosts);
	for ($i = 1; $i <= $numPosts; $i++)
	{
		$thread_post = array('nick' => $aPosts[$i][2],
							'time' => $aPosts[$i][5],
							'content' => $aPosts[$i][4],
							'image' => $aPosts[$i][3]);
		include TEMPLATES_PATH.'thread_post.php';
	}
}
else
	echo "Thread $threadID doesn't exist!";


?>
</div>
<?

/* Body ends */

include TEMPLATES_PATH.'footer.php';
include TEMPLATES_PATH.'meta-end.php';

echo "Did EVERYTHING in ".$timer->getElapsedTime()." seconds.";

?>
