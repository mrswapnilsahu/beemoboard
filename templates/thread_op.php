<div class="post">
	<div class="subject">
	<? echo $thread_post['subject'] ?> 
	<? echo " [".$thread_post['threadid']."]" ?> 
	<? if ($formStyle == "THREAD")
	{
	?>
	<a class="aright" href="nowhere">Reply</a>
	<?
	}
	?>
	</div>
	<div class="nick">
	<? echo $thread_post['num']."." ?>
	<? echo $thread_post['nick'] ?>
	[Posted: <? echo date("F j, Y (H:i:s)", $thread_post['time']) ?>]
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
