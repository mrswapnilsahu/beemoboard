<?php
require_once 'functions.php';
$bench = new Timer(1);

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
	
processNewPostForm();

$queri = new Querificator();
$_SESSION['verification_answer'] = $queri->generateQuestion($post_form['verification']);
$_POST['verification'] = "";

includeThemeElement('meta.php');
includeThemeElement('navigation.php');
includeThemeElement('header.php');

/* Body here (posts, input form, etc.) */
$formStyle = "POST"; //alt is "THREAD"
includeThemeElement('post_form.php');

if (isset($msg))
	echo "<center><h3>".$msg."</h3></center>";

?>
<div id="post_container">
<?php displayThreadPosts(); ?>
</div>
<?

/* Body ends */
includeThemeElement('footer.php');
includeThemeElement('meta-end.php');;

echo "Finished EVERYTHING in ".$bench->getElapsedTime()." seconds.";
?>
