<div class="post">
	<div class="subject">
	<? echo $thread_post['subject'] ?> 
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
	<? if ($thread_post['image'] != 0)
	{
	?>
	<a href="<? echo $thread_post['image'] ?>">
	<img width="160" height="200" align="left" src="<? echo $thread_post['image_thumb'] ?>">
	</a>
	<?
	}
	?>
	<? echo $thread_post['content'] ?>
	</div>
</div>
<div class="clearer"></div>
