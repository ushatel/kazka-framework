<?php
  if (@preg_match("/includes/i", $_SERVER['PHP_SELF'])) 
    die ("You can't access this file directly...");

  include_once("includes/DatabaseClasses/Db.CommonClass.php");    
  
  include_once("includes/DatabaseClasses/Db.UsersFilesTbl.php");
  include_once("includes/DatabaseClasses/Db.BannersFilesTbl.php");
  include_once("includes/DatabaseClasses/Db.ProjectsFilesTbl.php");
  include_once("includes/DatabaseClasses/Db.UsersAlbumsTbl.php");
  

/**
 * The Package collects the materials related classes etc
 *
 * @package Album.pkg
 */

/**
 * class Album
 *
 * The main class of the Album. Used for the operating with album, contractors and so on.
 * 
 * @package Album.pkg
 */
class UsersFiles extends CommonClass
{
	private $userFilesTblObj = NULL;
	private $projectsFilesTblObj = NULL;
	private $bannersFilesTblObj = NULL;
	
	public $users_files_id = 0;
	public $guid = "";
	public $filename = "";
	public $filesize = 0;
	public $filetype = "";
	public $filedata = "";
	public $projects_id = 0;
	public $projects_steps_id = 0;
	public $entities_id = 0;
	public $projects_file_ispublic = false;
	public $projects_users_files_id = 0;
	public $description = '';
	public $ip_current = '';
	public $ip_last = '';

	public $users_album_id = 0;
	public $album_login = 0;
	public $album_entities_id = 0;
	public $album_objects_id = 0;
	public $album_id = 0;
	public $album_langs_id = 0;
	public $album_parent0 = 0;
	public $album_parent1 = 0;
	public $album_parent2 = 0;
	public $album_parent3 = 0;
	public $album_parent4 = 0;
	public $album_ord = 0;
	public $album_title = '';
	public $album_description = '';
	public $album_is_trashed = false;
	
	public $width = 0;
	public $height = 0;
	
	public $views = 0;

	function __construct()
	{
		$this->userFilesTblObj = new UsersFilesTbl();
		$this->projectsFilesTblObj = new ProjectsFilesTbl();
		$this->bannersFielsTblObj = new BannersFilesTbl();
		$this->userAlbumsTblObj = new UsersAlbumsTbl();
	
		parent::__construct();
	}
	
	public function CreateFile()
	{	
		$this->ParseFieldsToDataRow();

		$result = $this->userFilesTblObj->CreateFile();

		return $result;
	}
	
	public function GetFile($fileId = 0, $fileGuid = 0)
	{
		$result = NULL;
		
		$this->ParseFieldsToDataRow();
		$result = $this->userFilesTblObj->GetFile($fileId, $fileGuid);
		
		if($result != NULL)
		{
			$this->isLoaded = true;
			$this->ParseDataRow();
		}
		else
		{
			$this->isLoaded = false;
		}
		
		return $this->isLoaded;
	}
	
	public function LoadFiles()
	{
		$result = NULL;
		
		$this->ParseFieldsToDataRow();
		$result = $this->userFilesTblObj->GetUserFiles();
		
		return $result;
	}
	
	public function CreateProjectsFile()
	{
		$result = NULL;
		
		$this->ParseFieldsToDataRow();
		if($this->projects_users_files_id < 1)
		{
			$result = $this->CreateFile();
		}
		
		if((int)$result > 0 || $this->projects_users_files_id > 0)
		{
			if($result > 0)
			{
				$this->projectsFilesTblObj->projects_users_files_id = (float)$result;
			}
			$this->ParseFieldsToDataRow();

			$result = $this->projectsFilesTblObj->CreateProjectsFile();
		}
		
		return $result;
	}

	public function CreateBannersFile()
	{
		$result = NULL;
	
		$this->ParseFieldsToDataRow();
		
		if($this->banners_files_id < 1)
		{
			$result = $this->CreateFile();
		}
		
		if((int)$result > 0 || $this->banners_files_id > 0)
		{
			if($result > 0)
			{
				$this->bannersFilesTblObj->banners_files_id = (float)$result;
			}
			$this->ParseFieldsToDataRow();
			
			$result = $this->bannersFilesTblObj->CreateBannersFile();
			
		}

		return $result;
	}	
	
	public function GetProjectsFiles($limit = 0, $offset = 0)
	{
		$result = NULL;
		$this->ParseFieldsToDataRow();

		$result = $this->projectsFilesTblObj->GetProjectsFiles($limit, $offset);

		return $result;
	}
	
	public function DeleteAlbum()
	{
		$this->albums = new UsersAlbumsTbl();
		//$this->albums->dataRow['OBJECTS_ID'] = (float)$this->album_objects_id;
		//$this->albums->dataRow['ENTITIES_ID'] = 4;
		$this->albums->dataRow['USERS_ALBUMS_ID'] = (float)$this->users_album_id;
		if($result = $this->albums->DeleteAlbumById() > 0)
		{
			$this->albumsFiles = new UsersFiles();
			$this->albumsFiles->DeleteFiles($result);
		}
	}

	public function DeleteFile($fileId)
	{
		$this->userFilesTblObj = new UsersFilesTbl();
		
		return $this->userFilesTblObj->DeleteFile($fileId);
	}

	public function LoadAlbums()
	{
		$result = NULL;

		$this->ParseFieldsToDataRow();

		$result = $this->userAlbumsTblObj->LoadAlbums();

		return $result;
	}
	
	public function SaveAlbum()
	{
		$result = NULL;
		
		$this->ParseFieldsToDataRow();
		$this->userAlbumsTblObj->dataRow['OBJECTS_ID'] = (float)$this->album_objects_id;

		$result = $this->userAlbumsTblObj->SaveAlbum();
		
		return $result;
	}
	
	public function ParseDataRow()
	{	

		$this->users_album_id = (float)$this->userAlbumsTblObj->dataRow['USERS_ALBUMS_ID'];
		$this->album_login = (float)$this->userAlbumsTblObj->dataRow['UPDATED_LOGIN'];
		$this->album_entities_id = (float)$this->userAlbumsTblObj->dataRow['ENTITIES_ID'];
		$this->album_objects_id = (float)$this->userAlbumsTblObj->dataRow['OBJECTS_ID'];
		$this->album_langs_id = (float)$this->userAlbumsTblObj->dataRow['LANGS_ID'];
		$this->album_parent0 = (float)$this->userAlbumsTblObj->dataRow['PARENT0'];
		$this->album_parent1 = (float)$this->userAlbumsTblObj->dataRow['PARENT1'];
		$this->album_parent2 = (float)$this->userAlbumsTblObj->dataRow['PARENT2'];
		$this->album_parent3 = (float)$this->userAlbumsTblObj->dataRow['PARENT3'];
		$this->album_parent4 = (float)$this->userAlbumsTblObj->dataRow['PARENT4'];
		$this->album_ord = (float) $this->userAlbumsTblObj->dataRow['ORD'];
		$this->album_title = substr($this->userAlbumsTblObj->dataRow['TITLE'], 0, 200);
		$this->album_description = substr($this->userAlbumsTblObj->dataRow['DESCRIPTION'], 0, 5000);
		$this->album_is_trashed = (bool)$this->userAlbumsTblObj->dataRow['IS_TRASHED'];

		$this->users_files_id = (float)$this->userFilesTblObj->dataRow['USERS_FILES_ID'];
		$this->guid = $this->userFielesTblObj->dataRow['GUID'];
		$this->filename = $this->userFilesTblObj->dataRow['FILENAME'];
		$this->views = $this->userFilesTblObj->dataRow['VIEWS'];
		$this->filesize = $this->userFilesTblObj->dataRow['FILESIZE'];
		$this->filetype = $this->userFilesTblObj->dataRow['FILETYPE'];
		$this->filedata = base64_decode($this->userFilesTblObj->dataRow['FILEDATA']);
		$this->entities_id = (float)$this->userFilesTblObj->dataRow['ENTITIES_ID'];
		$this->albums_id = (float)$this->userFilesTblObj->dataRow['USERS_ALBUMS_ID'];
		
		$this->banners_files_id = (float) $this->bannersFilesTblObj->dataRow['USERS_FILES_ID'];
		$this->banners_views = (float) $this->bannersFilesTblObj->dataRow['VIEWS'];
		$this->banners_clicks = (float) $this->bannersFilesTblObj->dataRow['CLICKS'];
		$this->banners_url = $this->bannersFilesTblObj->dataRow['URL'];
		$this->banners_title = $this->bannersFilesTblObj->dataRow['TITLE'];
		
		if($this->userFilesTblObj->dataRow['WIDTH'] > 0)
		{
			$this->width = (int)$this->userFilesTblObj->dataRow['WIDTH'];
		}
		else
		{
			$this->width = (int)$this->userFilesTblObj->dataRow['WIDTH'];
		}

		if($this->userFilesTblObj->dataRow['HEIGHT'] > 0)
		{
			$this->height = (int)$this->userFilesTblObj->dataRow['HEIGHT'];
		}
		else
		{
			$this->height = (int)$this->projectsFilesTblObj->dataRow['HEIGHT'];
		}
		
		$this->projects_id = (float)$this->projectsFilesTblObj->dataRow['PROJECTS_ID'];
		$this->projects_steps_id = (float)$this->projectsFilesTblObj->dataRow['PROJECTS_STEPS_ID'];
		$this->projects_file_ispublic = (bool)$this->projectsFilesTblObj->dataRow['IS_PUBLIC'];
		$this->projects_users_files_id = (float)$this->projectsFilesTblObj->dataRow['USERS_FILES_ID'];

	}
	
	public function ParseFieldsToDataRow()
	{
		$this->userAlbumsTblObj->dataRow['USERS_ALBUMS_ID'] = $this->users_album_id;
		$this->userAlbumsTblObj->dataRow['UPDATED_LOGIN'] = $this->album_login;
		$this->userAlbumsTblObj->dataRow['ENTITIES_ID'] = (float)$this->album_entities_id;
		$this->userAlbumsTblObj->dataRow['OBJECTS_ID'] = (float)$this->album_objects_id;
		$this->userAlbumsTblObj->dataRow['LANGS_ID'] = (float)$this->album_langs_id;
		$this->userAlbumsTblObj->dataRow['PARENT0'] = (float)$this->album_parent0;
		$this->userAlbumsTblObj->dataRow['PARENT1'] = (float)$this->album_parent1;
		$this->userAlbumsTblObj->dataRow['PARENT2'] = (float)$this->album_parent2;
		$this->userAlbumsTblObj->dataRow['PARENT3'] = (float)$this->album_parent3;
		$this->userAlbumsTblObj->dataRow['PARENT4'] = (float)$this->album_parent4;
		$this->userAlbumsTblObj->dataRow['ORD'] = (float)$this->album_ord;
		$this->userAlbumsTblObj->dataRow['TITLE'] = substr($this->album_title, 0, 200);
		$this->userAlbumsTblObj->dataRow['DESCRIPTION'] = substr($this->album_description, 0, 5000);
		$this->userAlbumsTblObj->dataRow['IS_TRASHED'] = (int) $this->album_is_trashed;

		$this->userFilesTblObj->dataRow['USERS_FILES_ID'] = (float)$this->users_files_id;
		$this->userFilesTblObj->dataRow['GUID'] = substr($this->guid, 0, 36);
		$this->userFilesTblObj->dataRow['FILENAME'] = substr($this->filename, 0, 150);
		$this->userFilesTblObj->dataRow['VIEWS'] = (float)$this->views;
		$this->userFilesTblObj->dataRow['FILESIZE'] = (float)$this->filesize;
		$this->userFilesTblObj->dataRow['FILETYPE'] = substr($this->filetype, 0, 10);
		$this->userFilesTblObj->dataRow['FILEDATA'] = base64_encode($this->filedata);
		$this->userFilesTblObj->dataRow['ENTITIES_ID'] = (float)$this->entities_id;
		$this->userFilesTblObj->dataRow['USERS_ALBUMS_ID'] = (float)$this->albums_id;
		
		$this->userFilesTblObj->dataRow['WIDTH'] = (int)$this->width;
		$this->userFilesTblObj->dataRow['HEIGHT'] = (int)$this->height;
		
		$this->projectsFilesTblObj->dataRow['PROJECTS_ID'] = (float)$this->projects_id;
		$this->projectsFilesTblObj->dataRow['PROJECTS_STEPS_ID'] = (float)$this->projects_steps_id;
		$this->projectsFilesTblObj->dataRow['IS_PUBLIC'] = (int)$this->projects_files_ispublic;
		$this->projectsFilesTblObj->dataRow['USERS_FILES_ID'] = (float)$this->projects_users_files_id;
		$this->projectsFilesTblObj->dataRow['WIDTH'] = (float) $this->width;
		$this->projectsFilesTblObj->dataRow['HEIGHT'] = (float) $this->height;

		$this->bannersFilesTblObj->dataRow['USERS_FILES_ID'] = (float) $this->banners_files_id;
		$this->bannersFilesTblObj->dataRow['VIEWS'] = (float) $this->banners_views;
		$this->bannersFilesTblObj->dataRow['CLICKS'] = (float) $this->banners_clicks;
		$this->bannersFilesTblObj->dataRow['URL'] = substr($this->banners_url, 0, 300);
		$this->bannersFilesTblObj->dataRow['TITLE'] = substr($this->banners_title, 0, 300);
	}
}

/*
CREATE  TABLE IF NOT EXISTS `INNODB_TABLES`.`USERS_FILES` (
  `USER_FILES_ID` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `GUID` VARCHAR(36) NULL ,
  `CREATED_TIME` TIMESTAMP NULL ,
  `UPDATED_TIME` TIMESTAMP NULL ,
  `UPDATED_LOGIN` BIGINT UNSIGNED NULL ,
  `FILENAME` VARCHAR(150) NULL ,
  `VIEWS` BIGINT UNSIGNED NULL ,
  `FILESDATA` MEDIUMBLOB  NULL ,
  PRIMARY KEY (`USER_FILES_ID`) ,
  UNIQUE INDEX `USER_FILES_ID_UNIQUE` (`USER_FILES_ID` ASC) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
*/
?>