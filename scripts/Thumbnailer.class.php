<?php

class Thumbnailer
{
	private $sError;
	private $aThumbParams = array("Mode" => 0,
								"Percent" => 25,
								"Width" => 400,
								"Height" => 300,
								"Fixed" => 250); //set up default thumbnail params
								
	/**
	* @desc		Constructor with optional parameters to set up thumbnailing mode
	* See setThumbParams() for parameter descriptions.
	*/
	public function __constructor($iMode = 0, $iParam = 25)
	{
		$this->setThumbParams($iMode, $iParam);
	}
								
	/**
	* @desc		Sets class error variable
	* @param str $sError		error description
	*/
	private function setError($sError)
	{
		$this->sError = $sError;
	}
	
	/**
	* @desc		Returns class error variable
	*/
	public function getError()
	{
		return $this->sError;
	}
	
	/**
	* @desc		Returns extension of the given file
	* @param str $filename		filename/path to file
	*/
	private function getExtension($filename)
	{
		$path_info = pathinfo($filename);
		return $path_info['extension'];
	}

	/**
	* @desc		Sets the method in which images will be thumbnailed
	* @param int $iMode		thumbnail mode (0-3)
	* @param int $iParam	parameter corresponding with set mode
	* Mode 0: Scale the image to $iParam percent of original size
	* Mode 1: Scale image to fixed width set by $iParam
	* Mode 2: Scale image to fixed height set by $iParam
	* Mode 3: Scale height/width to same lengths set by $iParam
	*/
	public function setThumbParams($iMode, $iParam)
	{
		if(!is_int($iMode) ||
			$iMode > 3 ||
			$iMode < 0)
		{
			$this->setError("Invalid SetThumbParams mode! Must be integer 0-3.");
			return 0;
		}
		else
			$this->aThumbParams["Mode"] = $iMode;
		
		//other way
		switch ($iMode)
		{
			case 0:
				if ($iParam >= 1 && $iParam <= 100)
					$this->aThumbParams["Percent"] = $iParam;
				else
				{
					$this->setError("Invalid Percent, 1-100 only.");
					return 0;
				}
				break;
			case 1:
			case 2:
			case 3:
				if ($iParam >= 1)
				{	
					if ($iMode == 1)
						$this->aThumbParams["Width"] = $iParam;
					elseif ($iMode == 2)
						$this->aThumbParams["Height"] = $iParam;
					elseif ($iMode == 3)
						$this->aThumbParams["Fixed"] = $iParam;
				}
				break;
			default:
				$this->setError("Invalid SetThumbs Mode. Must be ".
								"integer 0-3.");
				return 0;
		}		
		
		return 1;
	}

	/**
	* @desc		Creates the thumbnail image!
	* @param str $sSource	source image path
	* @param str $sDest		path to destination file (will be created)
	*/
	public function makeThumb($sSource, $sDest)
	{
		$sImgExt = strtolower($this->getExtension($sSource));
		switch ($sImgExt)
		{
			case "jpg":
			case "jpeg":
				$SourceImg = imagecreatefromjpeg($sSource);
				break;
			case "png":
				$SourceImg = imagecreatefrompng($sSource);
				break;
			case "gif":
				$SourceImg = imagecreatefromgif($sSource);
				break;
			default:
				$this->setError("Incompatible extension: ".$sImgExt);
				return 0;
		}
	
		$iWidth = imagesx($SourceImg);
		$iHeight = imagesy($SourceImg);
	
		if ($this->aThumbParams['Mode'] == 0)
		{
			$iNewHeight = $iHeight * ($this->aThumbParams['Percent'] / 100);
			$iNewWidth = $iWidth * ($this->aThumbParams['Percent'] / 100);
		}	
		elseif ($this->aThumbParams['Mode'] == 1)
		{
			$iNewWidth = $this->aThumbParams['Width'];
			$iNewHeight = floor($iHeight*($iNewWidth/$iWidth));
		}	
		elseif ($this->aThumbParams['Mode'] == 2)
		{
			$iNewHeight = $this->aThumbParams['Height'];
			$iNewWidth = floor($iWidth*($iNewHeight/$iHeight));
		}
		elseif ($this->aThumbParams['Mode'] == 3)
		{
			$iNewHeight = $this->aThumbParams['Fixed'];
			$iNewWidth = $this->aThumbParams['Fixed'];
		}
		else
		{
			$this->setError("Invalid thumbnail mode!");
			return 0;
		}
	
		$tmpImage = imagecreatetruecolor($iNewWidth,$iNewHeight);
		imagecopyresized($tmpImage,$SourceImg,0,0,0,0,$iNewWidth,$iNewHeight,$iWidth,$iHeight);
		if ($sImgExt == "jpg" || $sImgExt == "jpeg")
		{
			imagejpeg($tmpImage,$sDest);
			return 1;
		}
		elseif ($sImgExt == "png")
		{
			imagepng($tmpImage,$sDest);
			return 1;
		}
		elseif ($sImgExt == "gif")
		{
			imagegif($tmpImage,$sDest);
			return 1;
		}
	}
}

?>
