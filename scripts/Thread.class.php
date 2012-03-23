<?php

/* TODO: perhaps make a parent-class called "bmoBoard" that Thread will inherit
from. This way we can have "over-arching" functions that aren't thread specific,
but will be useful for maintaining the board as a whole.
*/

require_once('CSVedit.class.php');
require_once('Beemo.class.php');

class Thread extends Beemo
{
	
	private $errorString = "";
	private $selectedThreadID = 0;
	private $thread = 0; //currently selected CSV file/thread object
	private $threadDir = "";
	
	const SUBJECT_COL = 0;
	const THREADID_COL = 1;
	const POSTNUM_COL = 0;
	const IP_COL = 1;
	const NICK_COL = 2;
	const IMAGE_COL = 3;
	const IMAGE_RESX_COL = 4;
	const IMAGE_RESY_COL = 5;
	const IMAGE_SIZE_COL = 6;
	const CONTENT_COL = 7;
	const TIME_COL = 8;
	
	public function __construct($threadDir, $selectThreadID = 0)
	{
		if ($selectThreadID != 0)
			selectThread($selectThreadID);
			
		$this->threadDir = $threadDir;
		
		//else $selectedThreadFile remains default of -1 for "no thread".
	}
	
	private function setError($errorString)
	{
		$this->errorString = $errorString;
	}
	
	public function getError()
	{
		return $this->errorString;
	}
	
	/* Checks the number of threads and spawns the "next" one. Returns the ID 
	of the thread it created. */
	public function spawnThread($subject = "")
	{
		$threadToSpawn = $this->curThreadID() + 1;
		
		$this->thread = new CSVedit($this->threadDir.$threadToSpawn, 1);
		$threadData = array("subject" => $subject,
							"threadid" => $threadToSpawn);
		$this->thread->addRow($threadData);
		
		//touch($this->threadDir.$threadToSpawn);
		
		return $threadToSpawn;
	}
	
	/* Selects a thread to perform actions on. */
	public function selectThread($ID)
	{
		if (true == file_exists(THREADS_PATH.$ID))
		{
			$this->selectedThreadID = $ID;
			$this->thread = new CSVedit($this->threadDir.$ID, 0);
			return 1;
		}
		else
		{
			$this->selectedThreadID = 0;
			return 0;
		}		
	}
	
	
	/*IMMEDIATE TODO XXX FIXME: This needs updated to take account of the new 
	order of data in the thread CSV files. Also complete addPostArray() below.
	It will make your life easier. */
	
	/* Adds a new post to the end of the currently selected thread. */
	public function addPost($ip, $nick, $image, $postContent, $time = 0)
	{
	
		if (true == is_object($this->thread))
		{
			if ($time == 0)
				$time = time();
				
			$postData = array($this->thread->numRows() + 1, $ip, $nick, $image, $postContent, $time);
			$postNum = $this->thread->addRow($postData);
			return $postNum;
		}
		else
			return 0;
	}
	
	/* Same as above, but takes input as an indexed array. */
	public function addPostArray($aPost)
	{
	
	}
	
	/* Delete post $num from the thread. */
	public function deletePost($num)
	{
		if ($num > $this->thread->numRows() || $num < 1)
		{
			$this->setError("That post doesn't exist!");
			return 0;
		}
		else
		{
			//TODO: once deleteRow() gives a return value, return that instead.
			$this->thread->deleteRow($num);
			return 1;
		}
	}
	
	/* Returns the number of posts in the currently selected thread. */
	public function numPosts()
	{
		return $this->thread->numRows();
	}
	
	/* Puts an array of all of the posts in the currently selected thread into
	$aPostData. */
	public function getAllPosts(&$aPostData)
	{
		//TODO: Return posts already indexed like $thread_post['nick'] and such
	
		$this->thread->getTable($aPostData, 1);
		$numPosts =  $this->thread->numRows();
		
		//index the posts
		for ($i = 1; $i <= $numPosts; $i++)
			$this->indexPostArray($aPostData[$i], $aPostData[$i]);
		
		return $numPosts;
	}
	
	/* Puts an array of the post data from $num post into $aPostData. */
	public function getPost(&$aPostData, $num)
	{	
		$this->thread->getRow($aPostData, $num);
		$this->indexPostArray($aPostData, $aPostData);
	}
	
	/* Puts an array of the post data from the most recent posts into 
	$aPostData, the number of which is determined by $howMany. */
	public function getNewestPosts(&$aPostData, $howMany)
	{
		$numPosts = $this->thread->numRows();
		
		$startPost = ($numPosts - $howMany) + 1;
		if ($startPost <= 0)
			$startPost = 1;
			
		for ($i = $startPost; $i <= $numPosts; $i++)
			 $this->getPost($aPostData[], $i);
			 
	}
	
	private function indexPostArray(&$aOutArray, $inArray)
	{	
		$aOutArray['num'] = $inArray[$this::POSTNUM_COL];
		$aOutArray['ip'] = $inArray[$this::IP_COL];
		$aOutArray['nick'] = $inArray[$this::NICK_COL];
		$aOutArray['image'] = $inArray[$this::IMAGE_COL];
		$aOutArray['image_resx'] = $inArray[$this::IMAGE_RESX_COL];
		$aOutArray['image_resy'] = $inArray[$this::IMAGE_RESY_COL];
		$aOutArray['image_size'] = $inArray[$this::IMAGE_SIZE_COL];
		$aOutArray['content'] = $inArray[$this::CONTENT_COL];
		$aOutArray['time'] = $inArray[$this::TIME_COL];
	}
	
}

?>
