<?php

/* This is the over-arching class with the most abstracted functions for running
the image board. */

require_once('CSVedit.class.php');

class Beemo
{
	private $sError = "";
	private $configFile = 0;
	private $configdb = 0;
	private $config = array('BOARD_TITLE' => "Beemoboard",
							'MAX_NICK_LENGTH' => 128,
							'MAX_SUBJECT_LENGTH' => 128,
							'MAX_CONTENT_LENGTH' => 2048,
							'REQUIRE_SUBJECT' => 0,
							'MAX_THREAD_POSTS' => 250,
							'MAX_THREADS' => 250,
							'MAX_THREAD_LIFETIME' => 10080, //in minutes 
							'MAX_UPLOAD_SIZE' => 512, //in KB
							); 
							
	private $threadDir = "threads/"; //FIXME: do this the "proper" way
	
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
		$this->errorString = $errorString;
	}
	
	private function getError()
	{
		return $this->errorString;
	}
	
	/* Will load a CSV config file to change the default board settings. */
	public function loadConfig($configFile)
	{
		if (true == file_exists($configFile) && true == is_readable($configFile))
		{
			$this->configdb = new CSVedit($configFile, 0);	
		
			foreach ($this->config as $key => $value)
			{
				$configRow = $this->configdb->search($key, 0, 0);
				if ($configRow != -1)
				{
					$configData = 0;
					$this->configdb->getRow($configData, $configRow);
					$this->config[$key] = $configData[1];
					//echo $config[$key]."<br/>";
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
	
	/* Will take $aFile (passed in from $_FILS presumably, needs to be in this
	format of array), validate the image and place it in $sDest upon validation.
	Will return the path to the uploaded file upon validation and upload. */
	public function uploadImage($aFile, $sDest)
	{
		$sErrorString = "";
		if (1 == validateImageUpload($aFile, $sErrorString))
		{
			//copy code goes here
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
						if ($aFile['size'] <= ($config['MAX_UPLOAD_SIZE']))
						{
							$sErrorString = "";
							return 1; //image is valid!
						}
						else
							$sErrorString = "File too large! Max file size ".$config['MAX_UPLOAD_SIZE']." KB.";
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
	public function validatePostForm(&$aWarnings, $aPostInput)
	{
		$aWarnings = array("subject" => "",
					"image" => "",
					"validation" => "",
					"content" => "",
					"nick" => "");
	
		$fail = 0;
		if (strlen($aPostInput['nick']) > $this->getConfig('MAX_NICK_LENGTH'))
		{
			$aWarnings['nick'] = "Max nick length is ".$this->getConfig('MAX_NICK_LENGTH');
			$fail++;
		}
		
		if (strlen($aPostInput['subject']) > $this->getConfig('MAX_SUBJECT_LENGTH'))
		{
			$aWarnings['subject'] = "Max subject length is ".$this->getConfig('MAX_SUBJECT_LENGTH');
			$fail++;
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
	
	/* This will search for and delete threads that are past the maximum life
	span. */
	public function pruneThreads()
	{
	
	}
	
	/* This will return an array of thread ID's from most recently updated to
	least recently. */
	public function getActiveThreads()
	{
	
	}
	
	public function displayPostForm($postURL, $mode = 0)
	{
		//not sure how I want to approach this one yet.
	}

}

?>
