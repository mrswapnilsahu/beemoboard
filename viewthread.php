<?php

include 'globals.php';
require_once SCRIPTS_PATH."Beemo.class.php";
require_once SCRIPTS_PATH."Thread.class.php";
require_once SCRIPTS_PATH."Timer.class.php";
require_once SCRIPTS_PATH."functions.php";
$timer = new Timer(1);

$bmo = new Beemo(DB_PATH."defaultconfig.csv");

$thread = new Thread(THREADS_PATH);

if (isset($_GET['id']))
{
	if (isint($_GET['id']))
	{
		if (0 == $thread->selectThread($_GET['id']))
			header("Location: ".E404_PAGE);
			
		/*no need for an else case, thread is already selected if we're still
		on this page! */
	}
	else
		header("Location: ".E404_PAGE);
}
else
	header("Location: ".E404_PAGE);

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
		
		if (!empty($_FILES['image']['name']))
		{
			if (0 === $bmo->uploadImage($_FILES['image'], IMAGES_RELATIVE_PATH.$_FILES['image']['name']))
			{
				$warning['image'] = $bmo->getError();
				$errs++;
			}
			else
				$postInput['image'] = IMAGES_RELATIVE_PATH.$_FILES['image']['name'];
		}
		else
			$postInput['image'] = 0;
		
		if ($errs == 0)
		{
			$thread->addPost($_SERVER['REMOTE_ADDR'], $postInput['nick'], $postInput['image'], $postInput['content']);
			unset($_POST);
			$msg = "Posted!";
		}
	}
}
/*End posting code. */

$pageName = $bmo->getConfig('BOARD_TITLE');
include TEMPLATES_PATH.'meta.php';
include TEMPLATES_PATH.'navigation.php';
include TEMPLATES_PATH.'header.php';

/* Body here (posts, input form, etc.) */
$postURL = $_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING'];
$formStyle = "POST"; //alt is "THREAD"
include TEMPLATES_PATH.'post_form.php';

echo "<center><h3>".$msg."</h3></center>";

?>
<div id="post_container">
<?php

$numPosts = $thread->getAllPosts($aPosts);
for ($i = 1; $i <= $numPosts; $i++)
{							
	$thread_post = $aPosts[$i];
	include TEMPLATES_PATH.'thread_post.php';
}

?>
</div>
<?

/* Body ends */

include TEMPLATES_PATH.'footer.php';
include TEMPLATES_PATH.'meta-end.php';

echo "Did EVERYTHING in ".$timer->getElapsedTime()." seconds.";

?>
