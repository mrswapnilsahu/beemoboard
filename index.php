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

if ($thread->selectThread($threadID))
{
	$gotThread = 1;
	/*Here begins code that will take form input and post it. */
	if (isset($_POST['Post']))
	{
		$errs = 0;
		if (0 == $bmo->validatePostForm($warning, $_POST))
		{
			$postInput['nick'] = $bmo->sanitizeString($_POST['nick'], $bmo->getConfig('MAX_NICK_LENGTH'));
			$postInput['content'] = $bmo->sanitizeString($_POST['content'], $bmo->getConfig('MAX_CONTENT_LENGTH'));
			if ($postInput['nick'] == "")
				$postInput['nick'] = "Anonymous";
			
			if (isset($_FILES['image']))
			{
				if (0 != $bmo->uploadImage($_FILES['image'], IMAGES_PATH.$_FILES['image']['name']))
				{
				
				}
				else
				{
					$warning['image'] = $bmo->getError();
					$errs++;
				}
			}
			
			if ($errs == 0)
				$thread->addPost($_SERVER['REMOTE_ADDR'], $postInput['nick'], 0, $postInput['content']);
			else
				die($errs;)
			
		}
	}
	/*End posting code. */
}
else
{
	header("Location: ".E404_PAGE);
}



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

if ($gotThread)
{	
	$numPosts = $thread->getAllPosts($aPosts);
	for ($i = 1; $i <= $numPosts; $i++)
	{							
		$thread_post = $aPosts[$i];
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
