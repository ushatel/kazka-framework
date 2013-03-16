<?php

  if (@preg_match("includes/i", $_SERVER['PHP_SELF'])) 
    die ("You can't access this file directly...");

	include_once("includes/DatabaseClasses/Parts.UsersFiles.php");
	include_once("includes/common/Main.FileFunctions.php");

	class Image extends FileFunctions
	{
		private $usr;

		public $userFile = NULL;
		
		function __construct() 
		{
			
		}
		
		public function GetFileContents()
		{
			$this->userFile = new UsersFiles();
			$fId = (float)Request::$page;
			//$fPath = preg_split("/./",Request::$page); 
			
			if(is_numeric($fId))
			{
				$this->userFile->users_files_id = $fId;
			}
			elseif(strlen($fId) < 38)
			{
				$this->userFile->guid = $fId;
			}

			if($this->userFile->GetFile())
			{
				$this->fileName = $this->userFile->filename;
				$this->fileData = $this->userFile->filedata;
				$this->fileSize = $this->userFile->filesize;
				$this->mimeType = $this->userFile->filetype;				
			}
		}
	}
?>