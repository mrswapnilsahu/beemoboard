<div class="post">
	<div class="subject">
	<? echo $thread_post['subject'] ?> <a class="aright" href="nowhere">Reply</a>
	</div>
	<div class="nick">
	<? echo $thread_post['nick'] ?>
	[Posted: <? $thread_post['time']; ?>]
	</div>
	<div class="content">
	<a href="<? echo $thread_post['image'] ?>"><img width="160" height="200" align="left" src="<? echo $thread_post['image_thumb'] ?>"></a>
	<? $thread_post['content'] ?>
	</div>
</div>
