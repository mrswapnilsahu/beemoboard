<?php

/* This is the over-arching class with the most abstracted functions for running
the image board.
March, 2012
Brandon Foltz
*/

require_once('CSVedit.class.php');

class Beemo
{
	private $sError = "";
	private $configFile = 0;
	private $configdb = 0;
	private $config = array('BOARD_TITLE' => "Beemoboard",
							'MAX_NICK_LENGTH' => 32,
							'MAX_SUBJECT_LENGTH' => 128,
							'MAX_CONTENT_LENGTH' => 2048,
							'REQUIRE_SUBJECT' => 0,
							'MAX_THREAD_POSTS' => 250,
							'MAX_THREADS' => 250,
							'MAX_THREAD_IDLETIME' => 10080, //in minutes 
							'MAX_UPLOAD_SIZE' => 512, //in KB
							'THREADS_PER_PAGE' => 10, 
							'ACTIVE_THEME' => "default",
							'ACTIVE_THEME_RELATIVE_PATH' => "default/",
							'IMAGES_RELATIVE_PATH' => "images/",
							'THUMBS_RELATIVE_PATH' => "thumbs/",
							'THREADS_RELATIVE_PATH' => "threads/",
							'TEMPLATES_RELATIVE_PATH' => "templates/",
							'THEMES_RELATIVE_PATH' => "themes/",
							); 
							
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
	
	const KILO = 1024;
	
	/* Constructor, will load a non-default config if path is supplied. */
	public function __construct($configFile = 0)
	{
		if ($configFile === 0)
		{
			//default
		}
		else
		{
			$this->loadConfig($configFile);
		}		
	}
	
	private function setError($errorString)
	{
		$this->sError = $errorString;
	}
	
	public function getError()
	{
		return $this->sError;
	}
	
	/* Will load a CSV config file to change the default board settings. */
	public function loadConfig($configFile)
	{
		if (true == file_exists($configFile) && true == is_readable($configFile))
		{
			$this->configdb = new CSVedit($configFile, 0, "=");	
		
			foreach ($this->config as $key => $value)
			{
				$configRow = $this->configdb->search($key, 0, 0);
				if ($configRow != -1)
				{
					$configData = 0;
					$this->configdb->getRow($configData, $configRow);
					$this->config[$key] = $configData[1];
				}
			}
			return 1;
		}
		else
		{
			$this->setError("Couldn't load config file: $configFile!");
			return 0;
		}
	}
	
	/* Will return the value of the current configuration that corresponds to 
	the $key you pass. */
	public function getConfig($key)
	{
		if (true == array_key_exists($key, $this->config))
			return $this->config[$key];
		else
			return false; //is false really any different from 0?
	}
	
	/* Returns the number of the threads that have been created in the past
	to determine the next threads ID. */
	public function curThreadID()
	{
		/*For now this uses the "get the highest number thread in the dir and
		use that" method. Might be better to use a thread index db instead. */
		//Try both ways! - John Carmack
		$threadIndex = 0;
		
		if (is_dir($this->getConfig("THREADS_RELATIVE_PATH")))
		{
			if ($dh = opendir($this->getConfig("THREADS_RELATIVE_PATH")))
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
	
	public function processValidatedPostForm(&$aOutData, $aInForm, $formStyle)
	{
		//TODO: getConfig bit could be optimized
		$aOutData['nick'] = $this->sanitizeString($aInForm['nick'], $this->getConfig('MAX_NICK_LENGTH'));
		if ($aInForm['nick'] == "")
			$aOutData['nick'] = "Anonymous";
			
		$aOutData['content'] = $this->sanitizeString($aInForm['content'], $this->getConfig('MAX_CONTENT_LENGTH'));
		$aOutData['content'] = $this->formatContentInput($aOutData['content']);
		
		if ($formStyle == "THREAD")
		{
			$aOutData['subject'] = $this->sanitizeString($aInForm['subject'], $this->getConfig('MAX_SUBJECT_LENGTH'));
		}	
			
		/* TODO: insert a <br/> into the content string if there are no newlines
		for x chars. */	
			
		return 1;
	}
	
	private function formatContentInput($content)
	{
		$rv = str_replace("\n", "<br/>", $content);
		return $rv;
	}
	
	public function getPostedImageProperties($aImage, &$iResx, &$iResy, &$iSizeKB)
	{
		$extension = strtolower(pathinfo($aImage['name'], PATHINFO_EXTENSION));

		$imgResource = null;
		if ($extension == "jpg" || $extension == "jpeg")
			$imgResource = imagecreatefromjpeg($aImage['tmp_name']);
		else if ($extension == "png")
			$imgResource = imagecreatefrompng($aImage['tmp_name']);
		else if ($extension == "gif")
			$imgResource = imagecreatefromgif($aImage['tmp_name']);
		else
		{
			$this->setError("Unknown extension!");
			return 0;
		}
		
		$iResx = imagesx($imgResource);
		$iResy = imagesy($imgResource);
		$iSizeKB = (float)$aImage['size'] / $this::KILO;
		return 1;
			
	}
	
	public function getImageProperties($sImageFile, &$iResx, &$iResy, &$iSizeKB)
	{
		$extension = strtolower(pathinfo($sImageFile, PATHINFO_EXTENSION));

		$imgResource = null;
		if ($extension == "jpg" || $extension == "jpeg")
			$imgResource = imagecreatefromjpeg($sImageFile);
		else if ($extension == "png")
			$imgResource = imagecreatefrompng($sImageFile);
		else if ($extension == "gif")
			$imgResource = imagecreatefromgif($sImageFile);
		else
		{
			$this->setError("Unknown extension!");
			return 0;
		}
		
		$iResx = imagesx($imgResource);
		$iResy = imagesy($imgResource);
		$iSizeKB = $aImage['size'] * $this::KILO;
		return 1;		
	}
	
	/* Will take $aFile (passed in from $_FILS presumably, needs to be in this
	format of array), validate the image and place it in $sDest upon validation.
	Will return the path to the uploaded file upon validation and upload. */
	public function uploadImage($aFile, $sDest)
	{
		$sErrorString = "";
		if (1 == $this->validateImageUpload($aFile, $sErrorString))
		{
			if (false == copy($aFile['tmp_name'], $sDest))
			{
				$this->setError("Couldn't upload image!");
				return 0;
			}
			else
				return $sDest;
		}	
		else
		{
			$this->setError($sErrorString);
			return 0;
		}	
	}
	
	/* TODO: this needs cleaned up to be more contiguous with the rest of the
	style of this class. */
	public function validateImageUpload($aFile, &$sErrorString = 0)
	{
		$imageName = $aFile['name'];
		if ($imageName != "")
		{
			if (preg_match("/^[A-Za-z0-9\.\_\-\~\ ]{1,128}$/", $imageName))
			{
				$extension = strtolower(pathinfo($aFile['name'], PATHINFO_EXTENSION));

				$imgResource = null;
				if ($extension == "jpg" || $extension == "jpeg")
					$imgResource = imagecreatefromjpeg($aFile['tmp_name']);
				else if ($extension == "png")
					$imgResource = imagecreatefrompng($aFile['tmp_name']);
				else if ($extension == "gif")
					$imgResource = imagecreatefromgif($aFile['tmp_name']);
				else
					$warnings['imgupload'] = "Unknown extension!";
	
				if ($imgResource != false && $imgResource != null)
				{
					$imageX = imagesx($imgResource);
					if ($imageX != false)
					{
						if ($aFile['size'] <= ($this->config['MAX_UPLOAD_SIZE']) * $this::KILO)
						{
							$sErrorString = "";
							return 1; //image is valid!
						}
						else
							$sErrorString = "File too large! Max file size ".$this->config['MAX_UPLOAD_SIZE']." KB.";
					}
					else
						$sErrorString = "Invalid image!";
				}
				else
					$sErrorString = "Invalid image!";
			}
			else
				$sErrorString = "Thar be some strange characters among this file name matey!";
		}
		else
			$sErrorString = "No file selected!";
		
		return 0; //image not valid!
	}
	
	/* Validates text input that might be used for subject/nick/message etc.
	Returns the original string if valid, 0 if not. */
	public function validateTextInput($input, $maxLength, $multiLine)
	{
		if ($multiLine == 0)
		{
			if(preg_match("/^[A-Za-z0-9]{1,$maxLength}$/", $input))
				return $input;
			else
				return 0;
		}
		else if ($multiLine == 1)
		{
			if(preg_match("/^[A-Za-z0-9.]{1,$maxLength}$/", $input))
				return $input;
			else
				return 0;
		}
	}
	
	public function sanitizeString($string, $maxLength)
	{
		return filter_var(substr($string, 0, $maxLength), FILTER_SANITIZE_STRING);
	}
	
	/* Validates subject, nick, content fields of the post_form. Returns 0 if
	all passed, number of problems if not. Warnings are passed into $aWarnings. */
	public function validatePostForm(&$aWarnings, $aPostInput, $formStyle = "THREAD")
	{
		$aWarnings = array("subject" => "",
					"image" => "",
					"verification" => "",
					"content" => "",
					"nick" => "");
	
		$fail = 0;
		if (strlen($aPostInput['nick']) > $this->getConfig('MAX_NICK_LENGTH'))
		{
			$aWarnings['nick'] = "Max nick length is ".$this->getConfig('MAX_NICK_LENGTH');
			$fail++;
		}
		
		if ($formStyle === "THREAD")
		{
			if (strlen($aPostInput['subject']) > $this->getConfig('MAX_SUBJECT_LENGTH'))
			{
				$aWarnings['subject'] = "Max subject length is ".$this->getConfig('MAX_SUBJECT_LENGTH');
				$fail++;
			}
		}
		
		if (strlen($aPostInput['content']) > $this->getConfig('MAX_CONTENT_LENGTH'))
		{
			$aWarnings['content'] = "Max content length is ".$this->getConfig('MAX_CONTENT_LENGTH');
			$fail++;
		}
		else if ($aPostInput['content'] == "")
		{
			$aWarnings['content'] = "Content is empty!";
			$fail++;
		}
		
		return $fail;
	}
	
	/* This will search for and delete threads that havent been posted in for 
	MAX_THREAD_IDLETIME. It will also delete all the images + thumbs associated 
	with that thread.
	
	This function is intended to be used from a maintainence script, not from
	the actual board web-pages (not that it CANT be....). */
	public function pruneThreads()
	{
		/* TODO: Decide whether deleteThread() function should be used 
		over current method. Inlining all this code is probably faster. */
	
		$threadsPath = $this->getConfig("THREADS_RELATIVE_PATH");
		$dh = opendir($threadsPath);
		
		while ($file = readdir($dh))
		{
			if ($file != ".." && $file != "." && $file != " " && $file != ".empty")
			{
				//if thread hasn't been posted in for x number of minutes...
				if (filemtime($threadsPath.$file) < time() - (intval($this->getConfig('MAX_THREAD_IDLETIME') * 60)))
				{
					$th = new CSVedit($file, 0);
					$th->getTable($threadData, 1);
					if (!isset($threadData))
						return 0; //fail case
					
					$numRows = $th->numRows();
					for ($i = 0; $i <= $numRows; $i++)
					{
						//All this checking to avoid "undefined offset" warnings.
						if (isset($threadData[$i][$this::IMAGE_COL]) &&
							$threadData[$i][$this::IMAGE_COL] != "0" && 
							$threadData[$i][$this::IMAGE_COL] != 0)
						{
							unlink($this->getConfig('IMAGES_RELATIVE_PATH').$threadData[$i][$this::IMAGE_COL]);
							unlink($this->getConfig('THUMBS_RELATIVE_PATH').$threadData[$i][$this::IMAGE_COL]);
						}
					}
					
					//finally delete the thread.
					unlink($threadsPath.$file);
					echo "Removed thread: ".$file."\n";
				}
			}
		}
	}
	
	/* Deletes thread $id and all associated data (thumbs, images, posts). */
	public function deleteThread($id)
	{
		$threadPath = $this->getConfig("THREADS_RELATIVE_PATH").$id;
		$th = new CSVedit($threadPath, 0);
		$th->getTable($threadData, 1);
		if (!isset($threadData))
			return 0; //fail case
			
		$numRows = count($threadData);
		for ($i = 0; $i <= $numRows; $i++)
		{
			//All this checking to avoid "undefined offset" warnings.
			if (isset($threadData[$i][$this::IMAGE_COL]) &&
				$threadData[$i][$this::IMAGE_COL] != "0" && 
				$threadData[$i][$this::IMAGE_COL] != 0)
			{
				unlink($this->getConfig('IMAGES_RELATIVE_PATH').$threadData[$i][$this::IMAGE_COL]);
				unlink($this->getConfig('THUMBS_RELATIVE_PATH').$threadData[$i][$this::IMAGE_COL]);
			}
		}
		
		//finally delete the thread.
		unlink($threadPath);
		return 1;
	}
	
	/* This will return an array of thread ID's from most recently updated to
	least recently. */
	public function getActiveThreads(&$aActiveThreadList, $sThreadDir)
	{
		if (true == is_dir($sThreadDir) && is_readable($sThreadDir))
		{
			$dh = opendir($sThreadDir);
			while ($file = readdir($dh))
			{
				if ($file != "." && $file != ".." && $this->isint($file))
				{
					$aThreadList[$file] = filemtime($sThreadDir.$file);
				}
			}
			
			arsort($aThreadList, SORT_REGULAR);
			$i = 0;
			foreach ($aThreadList as $key => $value)
			{
				$aActiveThreadList[$i++] = $key;
			}
		}
	}
	
	//Will return the least recently posted in thread ID
	public function getMostDeadThread()
	{
		/*TODO if warranted, speed this up by inlining the process instead of 
		relying on another function in the class. */
		$this->getActiveThreads($threadList, $this->getConfig('THREADS_RELATIVE_PATH'));
		return $threadList[count($threadList) - 1];
	}
	
	/* Will return the number of threads that currently exist. */
	public function numThreads()
	{
		$numThreads = 0;
		$dh = opendir($this->getConfig('THREADS_RELATIVE_PATH'));
		while ($file = readdir($dh))
		{
			if ($file != "." && $file != ".." && $this->isint($file))
			{
				$numThreads++;
			}
		}
		return $numThreads;
	}

	/* Uses a regular expression to determine if the data passed in is a valid
	integer via characters, rather than datatype. */
	private function isint($mixed)
	{
		return (preg_match( '/^\d*$/', $mixed) == 1);
	}

}

?>
