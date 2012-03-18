<?php

//need a function to "validate" image uploads

require_once '../globals.php';

/* This function will validate an image upload. $aFile should be in the format 
that $_FILES passes. Returns 1 on success, 0 on failure with $sErrorString 
returned by reference. */
function validateImageUpload($aFile, &$sErrorString = 0)
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
					if ($aFile['size'] <= (MAX_UPLOAD_SIZE * KILO))
					{
						return 1; //image is valid!
					}
					else
						$sErrorString = "File too large! Max file size ".MAX_UPLOAD_SIZE." KB.";
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

?>
