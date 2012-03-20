<? //this is to shut up the notices 
//TODO: should be removed when everything is finished
if (false == isset($warning))
{
	$warning = array("subject" => "",
					"image" => "",
					"validation" => "",
					"content" => "",
					"nick" => "");
}

if (false == isset($_POST['Post']))
{
	$_POST = array("subject" => "",
					"image" => "",
					"validation" => "",
					"content" => "",
					"nick" => "");
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

Verification goes here. <br/>
<INPUT TYPE=TEXT SIZE=40 NAME="validation" class="text_input"
	VALUE="<?php echo $_POST['validation'];?>"><br/>
	<?php echo $warning['validation']; ?>

<INPUT TYPE=SUBMIT NAME="Post" VALUE="Post" class="button"><br/>
</FORM>
</div>
