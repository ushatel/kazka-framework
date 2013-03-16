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
	 * class Landmarks
	 *
	 * The main class of the Steps. Used for the operating with the steps
	 * 
	 * @package Projects.pkg
	 */
	 
	class ProjectsLandmarksTbl extends CommonTable implements IDataConnector 
	{
		public $tableName = "`build`.`PROJECTS_LANDMARKS`";

		/**
		 * Таблиці можуть бути на різних серверах. За замовченням, використовуються $this->commonCredentials
		 * Також із цією метою було створено додатковий класс Database
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
		
		public function DeleteProjectsLandmarks()
		{
			$result = NULL;
			
			$query = "DELETE FROM <%prefix%>".$this->tableName." WHERE `PROJECTS_ID`='".(float)$this->dataRow['PROJECTS_ID']."' ";
			$result = $this->databaseClass->SqlQuery($query);
			
			return mysql_affected_rows();
		}
		
		public function GetLandmarks($limit = 0, $offset = 0, $order_by = "`NAME`")
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
			
			$landmarks_text = "";
			if(strlen($this->dataRow['LANDMARKS_TEXT']) > 0)
			{
				$landmarks_text = "`LANDMARKS_TEXT`='%".$this->dataRow['LANDMARKS_TEXT']."%' OR ";
			}
			
			$query = " SELECT * FROM <%prefix%>".$this->tableName." ".
				 " WHERE ".$landmarks_text." (`PROJECTS_ID`='".(float)$this->dataRow['PROJECTS_ID']."' OR (`PROJECTS_ID`='0' AND `PROJECTS_STEPS_ID`='".(float)$this->dataRow['PROJECTS_STEPS_ID']."')) ORDER BY `LDATE` DESC ";

			$result = $this->databaseClass->SqlQuery($query);
			
			return $result;
		}

		public function GetLandmarksById()
		{
			$result = NULL;

			$query = " SELECT * FROM <%prefix%>".$this->tableName." ".
				 	 " WHERE `PROJECTS_LANDMARKS_ID`='".(float)$this->dataRow['PROJECTS_LANDMARKS_ID']."' LIMIT 1";
			$result = $this->databaseClass->SqlQuery($query);

			if(mysql_num_rows($result))
			{
				$this->dataRow = mysql_fetch_array($result);
			}
			else 
			{
				$this->dataRow = NULL;
			}

			return $this->dataRow;
		}		
		
		/**
		 * Створення нової вєхі
		 */
		public function CreateLandmark()
		{
			$insert_id = 0;

			$query = "INSERT INTO <%prefix%>".$this->tableName." ".
					 " (`GUID`, `CREATED_TIME`, `UPDATED_TIME`, `UPDATED_LOGIN`, ".
					 "  `PROJECTS_ID`, `PROJECTS_STEPS_ID`, `LDATE`, `IS_PUBLIC`, `LANGS_ID`, `LANDMARKS_TEXT` ) VALUES ".
					 " ( ".$this->databaseClass->GuidColumn().", ".$this->createdTimeValue.", ".$this->updatedTimeValue.", ".
					 "  '".(float)$this->updatedLogin."', '".(float)$this->dataRow['PROJECTS_ID']."', '".(float)$this->dataRow['PROJECTS_STEPS_ID']."', FROM_UNIXTIME(".$this->dataRow['LDATE']."), '".(int)$this->dataRow['IS_PUBLIC']."', '".(float)$this->dataRow['LANGS_ID']."', '".substr($this->dataRow['LANDMARKS_TEXT'], 0, 200)."');";

			$result = $this->databaseClass->SqlQuery($query);

			$insert_id = mysql_insert_id();

			return $this->dataRow['PROJECTS_STEPS_ID'] = (float)$insert_id;
		}

	}

/* 
CREATE  TABLE IF NOT EXISTS `BUILD`.`PROJECTS_LANDMARKS` (
  `PROJECTS_LANDMARKS_ID` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `GUID` VARCHAR(36) NULL ,
  `CREATED_TIME` TIMESTAMP NULL ,
  `UPDATED_TIME` TIMESTAMP NULL ,
  `UPDATED_LOGIN` BIGINT UNSIGNED NULL DEFAULT 0 ,
  `PROJECTS_ID` BIGINT UNSIGNED NULL DEFAULT 0 ,
  `PROJECTS_STEPS_ID` BIGINT UNSIGNED NULL DEFAULT 0 ,
  `LDATE` TIMESTAMP NULL ,
  `IS_PUBLIC` INT(1) UNSIGNED NULL DEFAULT 0 ,
  `LANGS_ID` BIGINT UNSIGNED NULL DEFAULT 0 ,
  `LANDMARKS_TEXT` VARCHAR(200) NULL ,
  PRIMARY KEY (`PROJECTS_LANDMARKS_ID`) ,
  UNIQUE INDEX `PROJECTS_LANDMARKS_ID_UNIQUE` (`PROJECTS_LANDMARKS_ID` ASC) )
ENGINE = InnoDB
COMMENT = 'Таблиця віх'*/
?>