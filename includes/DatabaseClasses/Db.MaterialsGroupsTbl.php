<?php


  if (@preg_match("/includes/i", $_SERVER['PHP_SELF'])) 
    die ("You can't access this file directly...");

	include_once("includes/DatabaseClasses/Db.CommonTable.php");
	include_once("includes/DatabaseClasses/Db.IDataConnector.php");

	/**
	 * The Package collects the Materials related classes etc
	 *
	 * @package Materials.pkg
	 */
	
	/**
	 * class MaterialsGroupsTbl 
	 *
	 * The MaterialsGroupsTbl. Used for the operating with the users
	 * 
	 * @package Materials.pkg
	 */
	 
	class MaterialsGroupsTbl extends CommonTable implements IDataConnector 
	{
		public $tableName = "`build`.`MATERIALS_GROUPS`";

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
		
		/**
		 * Повертає групу за ідентифікатором
		 */
		public function GetGroupById()
		{
			$this->isLoaded = false;
			
			$query = "SELECT * FROM <%prefix%>".$this->tableName." WHERE `MATERIALS_GROUPS_ID`='".$this->dataRow['MATERIALS_GROUPS_ID']."' LIMIT 1";
			
			$result = $this->databaseClass->SqlQuery($query);
			if(mysql_num_rows($result) == 1)
			{
				$this->dataRow = NULL;
				$this->dataRow = mysql_fetch_array($result);
				$this->isLoaded = true;
			}
			
			return $this->isLoaded;
		}
		
		/**
		 * Повертає список груп
		 */
		public function LoadGroups()
		{
			$this->isLoaded = false;
			
			$query = "SELECT * FROM <%prefix%>".$this->tableName." ORDER BY PARENT_GROUPS_ID, PARENT_GROUPS1_ID, PARENT_GROUPS2_ID, PARENT_GROUPS3_ID, PARENT_GROUPS4_ID, GROUPS_NAME ";

			$result = $this->databaseClass->SqlQuery($query);
			
			return $result;
		}	

		public function SaveGroup()
		{
			$this->isLoaded = false;
			
			$id = 0;
			
			$query = "SELECT * FROM <%prefix%>".$this->tableName." WHERE `MATERIALS_GROUPS_ID`='".(float)$this->dataRow['PARENT_GROUPS_ID']."' LIMIT 1";
			$result = $this->databaseClass->SqlQuery($query);

			if($result != NULL && $row = mysql_fetch_array($result))
			{
				$query = "INSERT INTO <%prefix%>".$this->tableName."".
						 " (`CREATED_TIME`, `UPDATED_TIME`, `UPDATED_LOGIN`, `UNIQUE_NAME_IDENTIFIER`, `GROUPS_NAME`, `PARENT_GROUPS_ID`, `PARENT_GROUPS1_ID`, `PARENT_GROUPS2_ID`, `PARENT_GROUPS3_ID`, `PARENT_GROUPS4_ID`) VALUES ". 
						 " (".$this->createdTimeValue.", ".$this->updatedTimeValue.", '".(float)$this->updatedLogin."', '".substr($this->dataRow['UNIQUE_NAME_IDENTIFIER'], 0, 200)."', '".substr($this->dataRow['GROUPS_NAME'], 0, 100)."', ".
						 "  '".(float)$this->dataRow['PARENT_GROUPS_ID']."', '".(float)$row['PARENT_GROUPS_ID']."', '".(float)$row['PARENT_GROUPS1_ID']."', '".(float)$row['PARENT_GROUPS2_ID']."', '".(float)$row['PARENT_GROUPS3_ID']."' ) ";

				 $result = $this->databaseClass->SqlQuery($query);
				 $id = mysql_insert_id();
			}

			return $id;
		}

	}

/*
CREATE  TABLE IF NOT EXISTS `build`.`MATERIALS_GROUPS` (
  `MATERIALS_GROUPS_ID` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `CREATED_TIME` TIMESTAMP NULL DEFAULT NOW() ,
  `UPDATED_TIME` TIMESTAMP NULL ,
  `UPDATED_LOGIN` BIGINT NULL ,
  `GROUPS_NAME` VARCHAR(100) NULL ,
  `PARENT_GROUPS_ID` BIGINT UNSIGNED NULL ,
  `PARENT_GROUPS1_ID` BIGINT UNSIGNED NULL ,
  `PARENT_GROUPS2_ID` BIGINT UNSIGNED NULL ,
  `PARENT_GROUPS3_ID` BIGINT UNSIGNED NULL ,
  `PARENT_GROUPS4_ID` BIGINT UNSIGNED NULL ,
  PRIMARY KEY (`MATERIALS_GROUPS_ID`) ,
  UNIQUE INDEX `MATERIALS_GROUPS_ID_UNIQUE` (`MATERIALS_GROUPS_ID` ASC) )
ENGINE = MyISAM
DEFAULT CHARACTER SET = utf8
COMMENT = 'Статична таблиця. \nВ базовій версії буде заповнятись адміністратором\n'
*/
?>