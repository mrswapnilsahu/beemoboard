<?php

/* TODO: perhaps make a parent-class called "bmoBoard" that Thread will inherit
from. This way we can have "over-arching" functions that aren't thread specific,
but will be useful for maintaining the board as a whole.
*/

require_once('CSVedit.class.php');

class Thread
{
	
	private $errorString = "";
	private $selectedThreadID = 0;
	private $thread = 0; //currently selected CSV file/thread object
	private $threadDir = "";
	
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
	
	private function getError()
	{
		return $this->errorString;
	}
	
	/* Returns the number of the threads that have been created in the past
	to determine the next threads ID. */
	public function curThreadID()
	{
		/*For now this uses the "get the highest number thread in the dir and
		use that" method. Might be better to use a thread index db instead. */
		//Try both ways! - John Carmack
		$threadIndex = 0;
		
		if (is_dir($this->threadDir))
		{
			if ($dh = opendir($this->threadDir))
			{
				while (($file = readdir($dh)) != false)
				{
					if ($file != "." && $file != "..")
					{						
						if ($threadIndex < intval($file))
							$threadIndex = intval($file);
							
						
					}
				}
			}
		}
	
		return $threadIndex;
	}
	
	
	/* Checks the number of threads and spawns the "next" one. Returns the ID 
	of the thread it created. */
	public function spawnThread($subject = "")
	{
		$threadToSpawn = $this->curThreadID() + 1;
		
		$this->thread = new CSVedit($this->threadDir.$threadToSpawn, 1);
		$threadData = array("subject" => $subject);
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
	
	/* Returns the number of posts in the currently selected thread. */
	public function numPosts()
	{
		return $this->thread->numRows();
	}
	
	public function getAllPosts(&$aPostData)
	{
		$this->thread->getTable($aPostData, 1);
		$numPosts =  $this->thread->numRows();
		return $numPosts;
	}
	
	public function getPost(&$aPostData, $num)
	{
		$this->thread->getRow($aPostData, $num);
	}
	
	public function getNewestPosts(&$aPostData, $howMany)
	{
		$numPosts = $this->thread->numRows();
		
		$startPost = ($numPosts - $howMany) + 1;
		if ($startPost <= 0)
			$startPost = 1;
			
		for ($i = $startPost; $i <= $numPosts; $i++)
			 $this->thread->getRow($aPostData[], $i);
	}
	
}

?>
