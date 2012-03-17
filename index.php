<?php

include 'globals.php';
include 'templates/meta.php';
include 'templates/navigation.php';
include 'templates/header.php';

/* Body here (posts, input form, etc.) */

?>

<?php

require_once SCRIPTS_PATH."Thread.class.php";
require_once SCRIPTS_PATH."Timer.class.php";


$thread = new Thread(THREADS_PATH);
echo $thread->spawnThread("something")."<br/>";


?>

<?

/* Body ends */

include 'templates/footer.php';
include 'templates/meta-end.php';

?>
