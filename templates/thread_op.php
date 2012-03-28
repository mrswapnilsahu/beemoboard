<div class="post">
	<div class="subject">
	<? echo $thread_post['subject'] ?> 
	<? echo " [".$thread_post['threadid']."]" ?> 
	<? if ($formStyle == "THREAD")
	{
	?>
	<a class="aright" href="viewthread.php?id=<? echo $thread_post['threadid'] ?>">Reply</a>
	<?
	}
	?>
	</div>
	<div class="nick">
	<? echo $thread_post['num']."." ?>
	<? echo $thread_post['nick'] ?>
	[Posted: <? echo date("F j, Y (H:i:s)", $thread_post['time']) ?>]
	
	<? 
	if ($thread_post['image'] != "0")
	{
		echo "<br/>";
		echo "Image: ";
		echo "<a href=\"".IMAGES_RELATIVE_PATH.$thread_post['image']."\"/>".$thread_post['image']."</a> ";
		echo $thread_post['image_resx']."x".$thread_post['image_resy'].", ";
		echo $thread_post['image_size']." KB";
	}
	?>
	</div>
	<div class="content">
	<? if ($thread_post['image'] != "0")
	{
	?>
	<a href="<? echo IMAGES_RELATIVE_PATH.$thread_post['image'] ?>">
	<img align="left" src="<? echo THUMBS_RELATIVE_PATH.$thread_post['image'] ?>">
	</a>
	<?
	}
	?>
	<? echo $thread_post['content'] ?>
	</div>
</div>
<div class="clearer"></div>
