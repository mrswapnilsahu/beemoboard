<?php
require_once 'functions.php';
$bench = new Timer(1);

if (isset($_GET['page']))
{
	if (isint($_GET['page']))
	{
		$indexPage = $_GET['page'];
	}
	else
		header("Location: ".E404_PAGE);
}
else
	$indexPage = 1;

processNewThreadForm();

$queri = new Querificator();
$_SESSION['verification_answer'] = $queri->generateQuestion($post_form['verification']);
$_POST['verification'] = "";

includeThemeElement('meta.php');
includeThemeElement('navigation.php');
includeThemeElement('header.php');

/* Body here (posts, input form, etc.) */
$formStyle = "THREAD";
includeThemeElement('post_form.php');
includeThemeElement('motd.php');
?>
<div id="post_container">
<?php
displayThreadPreviews($indexPage, $formStyle);
?> 
</div>
<div id="page_nav">
<? displayPagesNavigation($indexPage); ?>
</div>
<?

/* Body ends */
includeThemeElement('footer.php');
includeThemeElement('meta-end.php');

echo "Finished EVERYTHING in ".$bench->getElapsedTime()." seconds.";
?>
