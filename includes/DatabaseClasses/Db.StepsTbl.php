<?php

	if (@preg_match("/includes/i", $_SERVER['PHP_SELF'])) 
    	die ("You can't access this file directly...");

	include_once("includes/DatabaseClasses/Db.CommonTable.php");
	include_once("includes/DatabaseClasses/Db.IDataConnector.php");

	/**
	 * The Package collects the Projects related classes etc
	 *
	 * @package Steps.pkg
	 */
	
	/**
	 * class Steps
	 *
	 * The main class of the Steps. Used for the operating with the steps
	 * 
	 * @package Steps.pkg
	 */
	 
	class StepsTbl extends CommonTable implements IDataConnector 
	{
		public $tableName = "`build`.`PROJECTS_STEPS`";

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

		public function GetStepById()
		{
			$result = NULL;

			$query = " SELECT * FROM <%prefix%>".$this->tableName." ".
				 	 " WHERE `PROJECTS_STEPS_ID`='".$this->dataRow['PROJECTS_STEPS_ID']."' ORDER BY `UPDATED_TIME`";
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
		
		public function FinishStep()
		{
			$result = NULL;
			
			$query = " UPDATE <%prefix%>".$this->tableName." ".
					 " SET `UPDATED_LOGIN`='".$this->updatedLogin."', `UPDATED_TIME`=".$this->updatedTimeValue.", `IS_FINISHED`='".(int)$this->dataRow['IS_FINISHED']."' WHERE `PROJECTS_STEPS_ID`='".(float)$this->dataRow['PROJECTS_STEPS_ID']."' LIMIT 1";
			$result = $this->databaseClass->SqlQuery($query);
			
			if($result != NULL)
			{
				$result = true;
			}
			else
			{
				$result = false;
			}
			
			return $result;
		}

		public function SaveStep()
		{
			$result = NULL;

			$query = " UPDATE <%prefix%>".$this->tableName." ".
				 " SET `UPDATED_LOGIN`='".$this->updatedLogin."', `UPDATED_TIME`=".$this->updatedTimeValue.", `PROJECTS_ID`='".$this->dataRow['PROJECTS_ID']."', ".
				 "     `START_TIME`=FROM_UNIXTIME(".$this->dataRow['START_TIME']."), `END_TIME`=FROM_UNIXTIME(".$this->dataRow['END_TIME']."), `STEPS_TYPES_ID`='".$this->dataRow['STEPS_TYPES_ID']."', ".
				 "     `STEPS_TYPES_NAME`='".$this->dataRow['STEPS_TYPES_NAME']."', `STEP_NAME`='".$this->dataRow['STEP_NAME']."', `IS_PUBLIC`='".$this->dataRow['IS_PUBLIC']."', `IS_FINISHED`='".$this->dataRow['IS_FINISHED']."', `COMMENT`='".$this->dataRow['COMMENT']."' ".
				 " WHERE `PROJECTS_STEPS_ID`='".$this->dataRow['PROJECTS_STEPS_ID']."' LIMIT 1 ";

			$result = $this->databaseClass->SqlQuery($query);
			

			if($result != NULL)
			{
				$result = true;
			}
			else
			{
				$result = false;
			}
			
			return $result;
		}
		
		public function LoadProjectsSteps()
		{
			$result = NULL;
			
			$query = " SELECT * FROM <%prefix%>".$this->tableName." ".
					 " WHERE `PROJECTS_ID`='".$this->dataRow['PROJECTS_ID']."' ORDER BY `STEP_NAME`, `UPDATED_TIME`";
			$result = $this->databaseClass->SqlQuery($query);
			
			return $result;
		}
		
		/**
		 * Створення нового кроку проекту
		 */
		public function CreateStep()
		{
			$insert_id = 0;

			$query = "INSERT INTO <%prefix%>".$this->tableName." ".
					 " (`GUID`, `CREATED_TIME`, `UPDATED_TIME`, `UPDATED_LOGIN`, ".
					 "  `PROJECTS_ID`, `START_TIME`, `END_TIME`, `STEPS_TYPES_ID`, `STEPS_TYPES_NAME`, `STEP_NAME`, `IS_PUBLIC`, `COMMENT` ) VALUES ".
					 " (".$this->databaseClass->GuidColumn().", ".$this->createdTimeValue.", ".$this->updatedTimeValue.", ".
					 " '".(float)$this->updatedLogin."', '".(float)$this->dataRow['PROJECTS_ID']."', FROM_UNIXTIME(".$this->dataRow['START_TIME']."), FROM_UNIXTIME(".$this->dataRow['END_TIME']."), '".$this->dataRow['STEPS_TYPES_ID']."', '".$this->dataRow['STEPS_TYPES_NAME']."', '".$this->dataRow['STEP_NAME']."', '".$this->dataRow['IS_PUBLIC']."', '".$this->dataRow['COMMENT']."');";

			$result = $this->databaseClass->SqlQuery($query);

			$insert_id = mysql_insert_id();

			return $this->dataRow['PROJECTS_STEPS_ID'] = $insert_id;
		}

	}

/*
CREATE  TABLE IF NOT EXISTS `INNODB_TABLES`.`PROJECTS_STEPS` (
  `PROJECTS_STEPS_ID` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `GUID` VARCHAR(36) NULL ,
  `CREATED_TIME` TIMESTAMP NULL ,
  `UPDATED_TIME` TIMESTAMP NULL ,
  `UPDATED_LOGIN` BIGINT NULL ,
  `PROJECTS_ID` BIGINT UNSIGNED NULL ,
  `START_DATE` TIMESTAMP NULL ,
  `END_DATE` TIMESTAMP NULL ,
  `STEP_TYPES_ID` BIGINT NULL ,
  `STEP_TYPES_NAME` VARCHAR(200) NULL COMMENT 'Таблиця типів шагів' ,
  `STEP_NAME` VARCHAR(200) NULL ,
  `COMMENT` VARCHAR(5000) NULL ,
  PRIMARY KEY (`PROJECTS_STEPS_ID`) ,
  UNIQUE INDEX `PROJECTS_STEPS_ID_UNIQUE` (`PROJECTS_STEPS_ID` ASC) ,
  INDEX `FK_PROJECTS` (`PROJECTS_ID` ASC) ,
  CONSTRAINT `FK_PROJECTS`
    FOREIGN KEY (`PROJECTS_ID` )
    REFERENCES `INNODB_TABLES`.`PROJECTS` (`PROJECTS_ID` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = 'Кроки розробки проектів'
*/
?>