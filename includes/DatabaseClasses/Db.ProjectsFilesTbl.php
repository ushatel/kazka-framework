<?php

	if (@preg_match("/includes/i", $_SERVER['PHP_SELF'])) 
    	die ("You can't access this file directly...");

	include_once("includes/DatabaseClasses/Db.CommonTable.php");
	include_once("includes/DatabaseClasses/Db.IDataConnector.php");

	/**
	 * The Package collects the Projects related classes etc
	 *
	 * @package Projects.pkg
	 */
	
	/**
	 * class ProjectsFilesTbl 
	 *
	 * The main class of the ProjectsFilesTbl . Used for the operating with the table of Projects
	 * 
	 * @package Projects.pkg
	 */
	 
	class ProjectsFilesTbl extends CommonTable implements IDataConnector 
	{
		public $tableName = "`build`.`PROJECTS_FILES`";

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

		public function CreateProjectsFile()
		{
			$result = NULL;
		
			$query = " INSERT INTO <%prefix%>".$this->tableName." (`GUID`, `CREATED_TIME`, `UPDATED_TIME`, `UPDATED_LOGIN`, `PROJECTS_ID`, `PROJECTS_STEPS_ID`, `USERS_FILES_ID`, `IS_PUBLIC`, `WIDTH`, `HEIGHT`) ".
		   			 " VALUES      (".$this->databaseClass->GuidColumn().", ".$this->createdTimeValue.", ".$this->updatedTimeValue.", '".(float)$this->updatedLogin."', '".(float)$this->dataRow['PROJECTS_ID']."', '".(float)$this->dataRow['PROJECTS_STEPS_ID']."', '".(float)$this->dataRow['USERS_FILES_ID']."', '".(int)$this->dataRow['IS_PUBLIC']."', '".(float)$this->dataRow['WIDTH']."', '".(float)$this->dataRow['HEIGHT']."' )";

			$result = $this->databaseClass->SqlQuery($query);

			$result = mysql_insert_id();

			return $result;
		}
		
		public function GetProjectsFiles($limit = 0, $offset = 0, $order_by = '`UPDATED_TIME`')
		{
			$result = NULL;
			
			$limit_text = "";
			if($limit > 0 || $offset > 0)
			{
				$limit_text = " LIMIT ".(float)$limit;
				
				if($offset > 0)
				{
					$limit_text .= ",".(float)$offset;
				}
			}

			$query = "SELECT * FROM <%prefix%>".$this->tableName." WHERE `PROJECTS_ID`='".(float)$this->dataRow['PROJECTS_ID']."' OR (`PROJECTS_ID`='0' AND (`PROJECTS_STEPS_ID`='".(float)$this->dataRow['PROJECTS_STEPS_ID']."' OR `USERS_FILES_ID`='".(float)$this->dataRow['USERS_FILES_ID']."')) ORDER BY ".$order_by." ".$limit_text;

			$result = $this->databaseClass->SqlQuery($query);

			return $result;
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
CREATE  TABLE IF NOT EXISTS `BUILD`.`PROJECTS_FILES` (
  `PROJECTS_FILES_ID` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `GUID` VARCHAR(36) NULL ,
  `CREATED_TIME` TIMESTAMP NULL ,
  `UPDATED_TIME` TIMESTAMP NULL ,
  `UPDATED_LOGIN` BIGINT UNSIGNED NULL DEFAULT 0 ,
  `PROJECTS_ID` BIGINT UNSIGNED NULL DEFAULT 0 ,
  `PROJECTS_STEPS_ID` BIGINT UNSIGNED NULL DEFAULT 0 ,
  `USERS_FILES_ID` BIGINT UNSIGNED NULL DEFAULT 0 ,
  `IS_PUBLIC` INT(1) UNSIGNED NULL DEFAULT 0 ,
  `IS_ERASED` INT(1) UNSIGNED NULL DEFAULT 0 ,
  PRIMARY KEY (`PROJECTS_FILES_ID`) )
ENGINE = InnoDB
*/
?>