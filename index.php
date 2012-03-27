<?php

include 'globals.php';
require_once SCRIPTS_PATH."Beemo.class.php";
require_once SCRIPTS_PATH."Thread.class.php";
require_once SCRIPTS_PATH."Timer.class.php";
require_once SCRIPTS_PATH."Thumbnailer.class.php";
require_once SCRIPTS_PATH."Querificator.class.php";
require_once SCRIPTS_PATH."functions.php";
$timer = new Timer(1);
session_start();

$bmo = new Beemo(DB_PATH."defaultconfig.csv");

$thread = new Thread(THREADS_PATH);

/*Here begins code that will take form input and post it. */
if (isset($_POST['Post']))
{	
	/*TODO: maybe encapsulate all this in a function in beemo or thread to 
	shrink wrap it all and make viewthread.php and posting pages in general
	a little cleaner? This could be broken down a lot and made to look nicer. */

	$errs = 0;
	//if ($thread->numPosts() < $thread->getConfig('MAX_THREAD_POSTS'))
	//{
		if (0 == $bmo->validatePostForm($warning, $_POST, "THREAD"))
		{
			if ($_SESSION['verification_answer'] == $_POST['verification'])
			{
				$bmo->processValidatedPostForm($postInput, $_POST, "THREAD");
			
				if (!empty($_FILES['image']['name']))
				{
					if (0 === $bmo->uploadImage($_FILES['image'], IMAGES_RELATIVE_PATH.$_FILES['image']['name']))
					{
						$warning['image'] = $bmo->getError();
						$errs++;
					}
					else
					{
						$postInput['image'] = $_FILES['image']['name'];
			
						$bmo->getPostedImageProperties($_FILES['image'], 
														$postInput['image_resx'], 
														$postInput['image_resy'],
														$postInput['image_size']);				
						$thm = new Thumbnailer();
						$thm->setThumbParams(2, 160);
						$thm->makeThumb(IMAGES_RELATIVE_PATH.$postInput['image'], THUMBS_RELATIVE_PATH.$postInput['image']);
					}
				}
				else
					$postInput['image'] = 0;
			}
			else
			{
				$errs++;
				$warning['verification'] = "Sorry, your answer was incorrect!";
			}
		}
	//}
	/*else
	{
		$errs++;
		$msg = "Thread has reached it's maximum post limit!";
	}*/		
	
	//if everything passed, put the post in the file.
	if ($errs == 0)
	{
		$postInput['ip'] = $_SERVER['REMOTE_ADDR'];
		$newThread = $thread->spawnThread($_POST['subject']);
		$thread->selectThread($newThread);
		$thread->addPostArray($postInput);
		header("Location: viewthread.php?id=".$newThread);
		unset($_POST);
		$msg = "Posted!";
	}
	
}
/*End posting code. */

$queri = new Querificator();
$_SESSION['verification_answer'] = $queri->generateQuestion($post_form['verification']);
$_POST['verification'] = "";

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
