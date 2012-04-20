<? 
//post to whatever includes this file
if ($_SERVER['QUERY_STRING'] === "" || 
	$_SERVER['QUERY_STRING'] == NULL)
	$postURL = $_SERVER['PHP_SELF'];
else
	$postURL = $_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING'];


//this is to shut up the notices 
//TODO: should be removed when everything is finished
if (false == isset($warning))
{
	$warning = array("subject" => "",
					"image" => "",
					"verification" => "",
					"content" => "",
					"nick" => "");
}

if (false == isset($_POST['Post']))
{
	$_POST = array("subject" => "",
					"image" => "",
					"validation" => "",
					"content" => "",
					"nick" => "",
					"verification" => "");
}
?>

<div id="post_form">
<FORM ACTION="<?php echo $postURL ?>" METHOD=POST enctype="multipart/form-data">

<? 
if (false == isset($formStyle))
	$formStyle = "THREAD";
if ($formStyle != "POST")
{
?>
Subject: <?php echo $warning['subject']; ?><br/>
<INPUT TYPE=TEXT SIZE=40 NAME="subject" class="text_input"
	VALUE="<?php echo $_POST['subject'];?>"><br/>
<?
}
?>

Nickname: <?php echo $warning['nick']; ?><br/>
<INPUT TYPE=TEXT SIZE=40 NAME="nick" class="text_input"
	VALUE="<?php echo $_POST['nick'];?>"><br/>
	

Content: <?php echo $warning['content'];?><br/>
<TEXTAREA ROWS=4 COLS=46 NAME="content" class="text_input"><?php echo $_POST['content'];?>
</TEXTAREA><br/>


<INPUT TYPE="file" NAME="image" size="">
<?php echo $warning['image'];?><br/>

Verification question: <? echo $post_form['verification'] ?> <br/>
<?php echo $warning['verification'] ?>
<INPUT TYPE=TEXT SIZE=40 NAME="verification" class="text_input"
	VALUE="<?php echo $_POST['verification'];?>"><br/>

<INPUT TYPE=SUBMIT NAME="Post" VALUE="Post" class="button"><br/>
</FORM>
</div>
