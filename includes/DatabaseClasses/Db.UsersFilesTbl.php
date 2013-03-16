<?php
 
	if (@preg_match("/includes/i", $_SERVER['PHP_SELF'])) 
    	die ("You can't access this file directly...");

	include_once("includes/DatabaseClasses/Db.CommonTable.php");
	include_once("includes/DatabaseClasses/Db.IDataConnector.php");

	/**
	 * The Package collects the Files related classes etc
	 *
	 * @package Files.pkg
	 */
	
	/**
	 * class UsersFilesTbl
	 *
	 * The main class of the UsersFilesTbl. Used for the operating with the table of usersfiles
	 * 
	 * @package Files.pkg
	 */
	 
	class UsersFilesTbl extends CommonTable implements IDataConnector 
	{
		public $tableName = "`build`.`USERS_FILES`";

		/**
		 * 
		 */			
		public function __construct() 
		{
			$this->databaseClass = new Database();
			
			$this->databaseClass->SqlConnectCredentials($this->commonCredentials["host"], 
				$this->commonCredentials["username"], $this->commonCredentials["pass"], 
				$this->commonCredentials["dbname"], $this->commonCredentials["prefix"]);
				
			parent::__construct();
		}
		
		public function GetDataRow()
		{
			return $this->dataRow;
		}

		public function CreateFile()
		{
			$result = NULL;

			$query = " INSERT INTO <%prefix%>".$this->tableName." (`GUID`, `CREATED_TIME`, `UPDATED_TIME`, `UPDATED_LOGIN`, `FILENAME`, `VIEWS`, `ENTITIES_ID`, `USERS_ALBUMS_ID`, `FILESIZE`, `FILETYPE`, `FILEDATA`, `WIDTH`, `HEIGHT`, `DESCRIPTION`) ".
		   			 " VALUES      (".$this->databaseClass->GuidColumn().", ".$this->createdTimeValue.", ".$this->updatedTimeValue.", '".(float)$this->updatedLogin."', '".substr($this->dataRow['FILENAME'], 0, 150)."', '".(float)$this->dataRow['VIEWS']."', '".(float)$this->dataRow['ENITIES_ID']."', '".(float)$this->dataRow['USERS_ALBUMS_ID']."', '".(float)$this->dataRow['FILESIZE']."', '".$this->dataRow['FILETYPE']."', '".$this->dataRow['FILEDATA']."', '".(float)$this->dataRow['WIDTH']."', '".(float)$this->dataRow['HEIGHT']."', '".substr($this->dataRow['DESCRIPTION'], 0, 5000)."')";

			$result = $this->databaseClass->SqlQuery($query);

			$result = mysql_insert_id();

			return $result;
		}

		public function GetUserFiles($needFetch = false)
		{
			$result = NULL;
			
			$where = "";
			
			if((float)$this->dataRow['USERS_FILES_ID'] > 0)
			{
				$where .= " `USERS_FILES_ID` = '".$this->dataRow['USERS_FILES_ID']."' ";
			}
			else
			{
				if((float)$this->dataRow['USERS_ALBUMS_ID'] > 0)
				{
					$where .= " `USERS_ALBUMS_ID`='".$this->dataRow['USERS_ALBUMS_ID']."' ";
				}
				
				if((float)$this->dataRow['PROJECTS_ID'] > 0)
				{
					if(strlen($where) > 0)
					{
						$where .= " OR ";
					}
				
					$where .= " `PROJECTS_ID`='".$this->dataRow['PROJECTS_ID']."' ";
				}
				
				if((float)$this->dataRow['PROJECTS_STEPS_ID'] > 0)
				{
					if(strlen($where) > 0)
					{
						$where .= " OR ";
					}
					
					$where .= " `PROJECTS_STEPS_ID` = '".$this->dataRow['PROJECTS_STEPS_ID']."' ";
				}
			}
			

			$query = " SELECT * FROM <%prefix%>".$this->tableName." ".
					 " WHERE (".$where.") ORDER BY `UPDATED_TIME` ";

			$result = $this->databaseClass->SqlQuery($query);

			if($result != NULL && mysql_num_rows($result) == 1 && $needFetch)
			{
				$this->dataRow = mysql_fetch_array($result);
			}
			else
			{
				$this->dataRow = NULL;
			}

			return $result;
		}
		
		public function DeleteFile($deleteFileId)
		{
			$result = null;

			$query = "DELETE FROM <%prefix%>".$this->tableName." WHERE `USERS_FILES_ID`='".(float)$deleteFileId."' LIMIT 1";
			$result = $this->databaseClass->SqlQuery($query);

			return mysql_affected_rows();
		}
		
		public function DeleteFiles($result)
		{
			if(mysql_num_rows($result) > 0)
			{
				$files = " `USERS_ALBUMS_ID` IN ( ";
				$first = true;
				while($file = mysql_fetch_array($result))
				{
					$files .= (float)$file['USERS_ALBUMS_ID'];
					if($first)
					{
						$first = false;
					}
					else
					{
						$files .= ",";
					}
				}
				
				$query = "DELETE FROM <%prefix%>".$this->tableName." WHERE ".$files." ";
				$result = $this->databaseClass->SqlQuery($query);
			}
			
			return mysql_affected_rows();
		}
		
		public function GetFile($fileId = 0, $fileGuid = "")
		{
			if(strlen($fileGuid) > 0)
			{
				$guid = "OR `GUID`='".$this->dataRow['GUID']."' ";
			}
			
			$query = "SELECT * FROM <%prefix%>".$this->tableName." WHERE `USERS_FILES_ID`='".(float)$this->dataRow['USERS_FILES_ID']."' ".$guid." LIMIT 1 ";

			$result = $this->databaseClass->SqlQuery($query);

			if($result != NULL && mysql_num_rows($result) == 1)
			{
				$this->dataRow = mysql_fetch_array($result);
			}
			else
			{
				$this->dataRow = NULL;
			}

			return $this->dataRow;
		}
	}

/*
CREATE TABLE `USERS_FILES` (
  `USERS_FILES_ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `GUID` varchar(36) DEFAULT NULL,
  `CREATED_TIME` timestamp NULL DEFAULT NULL,
  `UPDATED_TIME` timestamp NULL DEFAULT NULL,
  `UPDATED_LOGIN` bigint(20) unsigned DEFAULT '0',
  `FILENAME` varchar(150) DEFAULT NULL,
  `VIEWS` bigint(20) unsigned DEFAULT '0',
  `FILESIZE` bigint(20) unsigned DEFAULT '0',
  `FILETYPE` varchar(10) DEFAULT NULL,
  `FILEDATA` mediumblob,
  PRIMARY KEY (`USERS_FILES_ID`),
  UNIQUE KEY `USER_FILES_ID_UNIQUE` (`USERS_FILES_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8$$
*/
?>