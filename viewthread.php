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
	
$warning = array("subject" => "",
					"image" => "",
					"verification" => "",
					"content" => "",
					"nick" => "");

/*Here begins code that will take form input and post it. */
if (isset($_POST['Post']))
{	
	/*TODO: maybe encapsulate all this in a function in beemo or thread to 
		shrink wrap it all and make viewthread.php and posting pages in general
		a little cleaner? Verification is done before form validation here atm
		so only if your question is right are any other warnings returned... */

	$errs = 0;
	if ($_SESSION['verification_answer'] == $_POST['verification'])
	{
		if (0 == $bmo->validatePostForm($warning, $_POST, "POST"))
		{
			$bmo->processValidatedPostForm($postInput, $_POST);
		
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
		
			if ($errs == 0)
			{
				//$thread->addPost($_SERVER['REMOTE_ADDR'], $postInput['nick'], $postInput['image'], $postInput['content']);
				$postInput['ip'] = $_SERVER['REMOTE_ADDR'];
				$thread->addPostArray($postInput);
				unset($_POST);
				$msg = "Posted!";
			}
		}
	}
	else
	{
		$errs++;
		$warning['verification'] = "Sorry, your answer was incorrect!";
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
$postURL = $_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING'];
$formStyle = "POST"; //alt is "THREAD"
include TEMPLATES_PATH.'post_form.php';

if (isset($msg))
	echo "<center><h3>".$msg."</h3></center>";

?>
<div id="post_container">
<?php

$numPosts = $thread->getAllPosts($aPosts);
for ($i = 0; $i <= $numPosts; $i++)
{				
	if ($i == 0)
	{
		/* I feel like this is a messy way to handle the "subject", but it works
		for now. */
		$thread_post = $aPosts[++$i];
		$thread_post['subject'] = $aPosts[0][$thread::SUBJECT_COL];
		$thread_post['threadid'] = $aPosts[0][$thread::THREADID_COL];
		include TEMPLATES_PATH."thread_op.php";
	}
	else
	{	
		$thread_post = $aPosts[$i];
		include TEMPLATES_PATH.'thread_post.php';
	}
}

?>
</div>
<?

/* Body ends */

include TEMPLATES_PATH.'footer.php';
include TEMPLATES_PATH.'meta-end.php';

echo "Did EVERYTHING in ".$timer->getElapsedTime()." seconds.";

?>
