<?php
include 'functions.php';

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

$pageName = $bmo->getConfig('BOARD_TITLE');
include TEMPLATES_PATH.'meta.php';
include TEMPLATES_PATH.'navigation.php';
include TEMPLATES_PATH.'header.php';

/* Body here (posts, input form, etc.) */
$formStyle = "THREAD";
include TEMPLATES_PATH.'post_form.php';
?>
<div id="post_container">
<?php
$threadsPerPage = 10;
displayThreadPreviews($threadsPerPage, $indexPage, $formStyle);
?> 
</div>
<div id="page_nav">
<? displayPagesNavigation($threadsPerPage, $indexPage); ?>
</div>
<?

/* Body ends */
include TEMPLATES_PATH.'footer.php';
include TEMPLATES_PATH.'meta-end.php';

?>
