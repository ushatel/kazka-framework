<?php

  if (@preg_match("includes/i", $_SERVER['PHP_SELF'])) 
    die ("You can't access this file directly...");
    
//Error_Reporting(E_ALL & ~E_NOTICE);


	/**
	 * The Package  classes etc
	 *
	 * @package FileFunctions.pkg
	 */
	
	/**
	 * class Files
	 *
	 * The class holds the common files methods and operations. 
	 * 
	 * @package FileFunctions.pkg
	 */

class FileFunctions
{
	public $fileName = "";
	public $fileData = "";
	public $fileSize = 0;
	public $mimeType = "";
	
	public $iWidth = 0;
	public $iHeight = 0;

	public function ValidateFile ()
	{
		$imagetype = getimagesize($this->fileName);
		$image = NULL;
		
		$this->mimeType = $imagetype['mime'];
		$this->iWidth = (float)$imagetype[0];
		$this->iHeight = (float)$imagetype[1];
		
		ob_start();

		switch($this->mimeType)
		{
			case "image/gif":
				$image = imagecreatefromgif($this->fileName);
				imageinterlace($image, true);

				imagegif($image);
			break;
			
			case "image/jpeg":
				$image = imagecreatefromjpeg($this->fileName);
				imageinterlace($image, true);
				
				imagejpeg($image, NULL, 100);
				
			break;
			
			case "image/png":
				$image = imagecreatefrompng($this->fileName);
				imageinterlace($image, true);
				
				imagepng($image);
			break;
			
			default:
				$image = NULL;
			break;
		}
		
		$this->fileData = ob_get_contents();

		ob_end_clean();
		
		$this->fileSize = strlen($this->fileData); // bytes
		
		if($image != NULL)
		{ 
			imagedestroy($image);
		}
    }
    
    public function PrepareOutputImage($stream = NULL)
    {
    
    	if($stream != NULL)
		{
			//$image = NULL;
			$image = imagecreatefromstring($stream);
			
			ob_start();
	
			switch($this->mimeType)
			{
				case "image/gif":
					imageinterlace($image, true);
	
					imagegif($image);
				break;
				
				case "image/jpeg":
					imageinterlace($image, true);
					
					imagejpeg($image, NULL, 100);
					
				break;
				
				case "image/png":
					imageinterlace($image, true);
					
					imagepng($image);
				break;
				
				default:
					$image = NULL;
				break;
			}
			
			$this->fileData = ob_get_contents();
	
			ob_end_clean();
			
			$this->fileSize = strlen($this->fileData); // bytes
			
			if($image != NULL)
			{ 
				imagedestroy($image);
			}
		}
		
		return $this->fileData;
    }
}