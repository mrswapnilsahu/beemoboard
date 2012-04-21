<?php

require_once 'globals.php';
require_once SCRIPTS_PATH."Beemo.class.php";
require_once SCRIPTS_PATH."Thread.class.php";
require_once SCRIPTS_PATH."Timer.class.php";
require_once SCRIPTS_PATH."Thumbnailer.class.php";
require_once SCRIPTS_PATH."Querificator.class.php";
require_once SCRIPTS_PATH."misc.php";

session_start();
$bmo = new Beemo(DB_PATH."defaultconfig.csv");
$thread = new Thread(THREADS_PATH);

function processNewThreadForm()
{
	global $bmo;
	global $thread;
	global $warning;
	
	/*Here begins code that will take form input and post it. */
	if (isset($_POST['Post']))
	{	
		/*TODO: maybe encapsulate all this in a function in beemo or thread to 
		shrink wrap it all and make viewthread.php and posting pages in general
		a little cleaner? This could be broken down a lot and made to look nicer. */

		$errs = 0;
		if ($bmo->numThreads() < $bmo->getConfig('MAX_THREADS'))
		{
			processpost:
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
					//echo $_POST['verification'].BR;
					//echo $_SESSION['verification_answer'];
				}
			}
		}
		else
		{
			/* TODO: Rather than just telling the user that the thread limit is
			reached, delete the oldest thread and spawn a new one. */
			//$errs++;
			//$msg = "Maximum number of threads has been reached! Please wait for one to 404.";
		
			$bmo->deleteThread($bmo->getMostDeadThread());
			goto processpost; //XXX BAD BAD BAD BAD BAD BAD JUST FOR TESTING
		}
	
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
}

function processNewPostForm()
{
	global $bmo;
	global $thread;
	global $warning;
	
	//initializing warning array.
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
		a little cleaner? This could be broken down a lot and made to look nicer. */

		$errs = 0;
		if ($thread->numPosts() < $thread->getConfig('MAX_THREAD_POSTS'))
		{
			if (0 == $bmo->validatePostForm($warning, $_POST, "POST"))
			{
				if ($_SESSION['verification_answer'] == $_POST['verification'])
				{
					$bmo->processValidatedPostForm($postInput, $_POST, "POST");
			
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
		}
		else
		{
			$errs++;
			$msg = "Thread has reached it's maximum post limit!";
		}		
	
		//if everything passed, put the post in the file.
		if ($errs == 0)
		{
			$postInput['ip'] = $_SERVER['REMOTE_ADDR'];
			$thread->addPostArray($postInput);
			unset($_POST);
			$msg = "Posted!";
			//should redirect and use $_GET to avoid refresh issues
		}
	
	}
	/*End posting code. */
}

function displayThreadPreviews($indexPage, $formStyle)
{
	global $bmo;
	global $thread;
	
	$numToShow = $bmo->getConfig('THREADS_PER_PAGE');
	
	$bmo->getActiveThreads($aThreadList, THREADS_PATH);
	$numThreads = count($aThreadList);

	//10 per page
	$rangeStart = ($indexPage - 1) * $numToShow;

	if ($numThreads < $rangeStart + $numToShow)
		$rangeEnd = $numThreads;
	else
		$rangeEnd = $rangeStart + $numToShow;

	$numPages = ceil($numThreads / $numToShow);

	for ($i = $rangeStart; $i < $rangeEnd; $i++)
	{
		$thread->selectThread($aThreadList[$i]);
		$thread->getThreadPreview($aPosts);
	
		$numPosts = count($aPosts);
		for ($x = 0; $x < $numPosts; $x++)
		{				
			if ($x == 0)
			{
				/* I feel like this is a messy way to handle the "subject", but it works
				for now. */
				$thread_post = $aPosts[++$x];
				$thread_post['subject'] = $aPosts[0][$thread::SUBJECT_COL];
				$thread_post['threadid'] = $aPosts[0][$thread::THREADID_COL];
				include $bmo->getConfig('THEMES_RELATIVE_PATH').$bmo->getConfig('ACTIVE_THEME_RELATIVE_PATH')."thread_op.php";
			}
			else
			{	
				$thread_post = $aPosts[$x];
				include $bmo->getConfig('THEMES_RELATIVE_PATH').$bmo->getConfig('ACTIVE_THEME_RELATIVE_PATH').'thread_post.php';
			}
		}
	
		if ($i < $rangeEnd - 1)
			echo "<div class=\"separator\"></div>";
		
		unset($aPosts);
	}
}

function displayPagesNavigation($indexPage)
{
	global $bmo;

	//start of page numbering
	//display page links
	
	$threadsPerPage = $bmo->getConfig('THREADS_PER_PAGE');

	$numPages = ceil($bmo->numThreads() / $threadsPerPage);

	if ($indexPage > 1)
	{
		$pp = $indexPage - 1;
		echo "<a href=\"index.php?page=$pp\">[Previous]</a>";
	}

	for ($i = 1; $i <= $numPages; $i++)
	{
		if ($i == $indexPage)
			echo "<b><a class=\"selected\" href=\"index.php?page=$i\">[$i]</a></b>";
		else
			echo "<a href=\"index.php?page=$i\">[$i]</a>";
	}

	if ($indexPage < $numPages)
	{
		$np = $indexPage + 1;
		echo "<a href=\"index.php?page=$np\">[Next]</a>";
	}
}

function displayThreadPosts()
{
	global $thread;
	$formStyle = "POST";	
	
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
			include $bmo->getConfig('THEMES_RELATIVE_PATH').$bmo->getConfig('ACTIVE_THEME_RELATIVE_PATH')."thread_op.php";
		}
		else
		{	
			$thread_post = $aPosts[$i];
			include $bmo->getConfig('THEMES_RELATIVE_PATH').$bmo->getConfig('ACTIVE_THEME_RELATIVE_PATH').'thread_post.php';
		}
	}
}

function includeThemeElement($file)
{
	global $bmo;
	include $bmo->getConfig('THEMES_RELATIVE_PATH').$bmo->getConfig('ACTIVE_THEME_RELATIVE_PATH').$file;
}

?>
