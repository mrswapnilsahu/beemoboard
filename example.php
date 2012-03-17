<?php

include 'globals.php';
include 'templates/meta.php';
include 'templates/navigation.php';
include 'templates/header.php';

/* Body here (posts, input form, etc.) */

?>

<div id="post_form">
<FORM ACTION="<?php $_SERVER['PHP_SELF'] ?>" METHOD=POST enctype="multipart/form-data">
Nickname: <br>
<INPUT TYPE=TEXT SIZE=40 NAME="nick" class="text_input"
	VALUE="<?php echo $_POST['nick'];?>"><br><?php echo $warning['nick']; ?>
	
Message:<br>
<TEXTAREA ROWS=4 COLS=46 NAME="message" class="text_input"><?php echo $_POST['message'];?>
</TEXTAREA><br><?php echo $warning['message'];?>
 
<INPUT TYPE="file" NAME="image" size="30"><br><?php echo $warning['image'];?>

Verification goes here. <br>
<INPUT TYPE=TEXT SIZE=40 NAME="validation" class="text_input"
	VALUE="<?php echo $_POST['validation'];?>"><br><?php echo $warning['validation']; ?>
	
<INPUT TYPE=SUBMIT NAME="Post" VALUE="Post" class="button"><br>
</FORM>
</div>

<div id="post_container">

	<div class="post">
		<div class="subject">
		Some awesome subject <a class="aright" href="nowhere">Reply</a>
		</div>
		<div class="nick">
		Jaydub [Posted: 14:34:55 March 16, 2012]
		</div>
		<div class="content">
		<a href="image/hfm.jpg"><img width="160" height="200" align="left" src="image/hfm.jpg"></a>
		Some text. Some text. Some text. Some text. Some text. Some text. Some text.
		</div>
	</div>
	
	<div class="clearer"></div>
	
	<div class="post">
		<div class="nick">
		Jaydub [Posted: 14:34:55 March 16, 2012]
		</div>
		<div class="content">
		<a href="image/hfm.jpg"><img width="160" height="200" align="left" src="image/hfm.jpg"></a>
		Some textual awesomeness. AWESOMENESS I SAY!
		</div>
	</div>
	
	<div class="separator"></div>
	
	<div class="post">
		<div class="subject">
		Some awesome subject <a class="aright" href="nowhere">Reply</a>
		</div>
		<div class="nick">
		Jaydub [Posted: 14:34:55 March 16, 2012]
		</div>
		<div class="content">
		<a href="image/hfm.jpg"><img width="160" height="200" align="left" src="image/hfm.jpg"></a>
		Some text lulz.
		</div>
	</div>
	
	<div class="clearer"></div>
	
	<div class="post">
		<div class="nick">
		Jaydub [Posted: 14:34:55 March 16, 2012]
		</div>
		<div class="content">
		<a href="image/hfm.jpg"><img width="160" height="200" align="left" src="image/hfm.jpg"></a>
		Some text POOPSHOOTZZZZZZZZZ. POOPSHOOTZZZZZZZZZ. 
		</div>
	</div>

</div>
<?

/* Body ends */

include 'templates/footer.php';
include 'templates/meta-end.php';

?>
