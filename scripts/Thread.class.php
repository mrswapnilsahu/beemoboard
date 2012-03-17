<?php

/* TODO: perhaps make a parent-class called "bmoBoard" that Thread will inherit
from. This way we can have "over-arching" functions that aren't thread specific,
but will be useful for maintaining the board as a whole.
*/

class Thread
{
	
	private $errorString = "";
	private $selectedThreadID = 0;
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
	public function spawnThread()
	{
		$threadToSpawn = $this->curThreadID() + 1;
		
		touch($this->threadDir.$threadToSpawn);
		
		return $threadToSpawn;
	}
	
	/* Selects a thread to perform actions on. */
	public function selectThread($ID)
	{
		$this->selectedThreadFile = $ID;
	}
	
	/* Adds a new post to the end of the currently selected thread. */
	public function addPost($ip, $nick, $image, $postContent)
	{
	
	} 
	
}

?>
