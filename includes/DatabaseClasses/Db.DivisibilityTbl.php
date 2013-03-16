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
	 * class DivisibilityTbl
	 *
	 * The main class of the DivisibilityTbl. Used for the operating with the table of divisibilities of materials
	 * 
	 * @package Projects.pkg
	 */
	 
	class DivisibilityTbl extends CommonTable implements IDataConnector 
	{
		public $tableName = "`build`.`DIVISIBILITY`";

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
		
		public function LoadDivisibilityList()
		{
			$result = NULL;
			
			$query = "SELECT * FROM <%prefix%>".$this->tableName." ORDER BY `ORD`, `NAME`";
			
			$result = $this->databaseClass->SqlQuery($query);
			
			return $result;
		}
		
		public function GetDivisibilityById()
		{
			$result = NULL;
			
			if($this->dataRow['DIVISIBILITY_ID'] > 0)
			{
				$query = "SELECT * FROM <%prefix%>".$this->tableName." WHERE `DIVISIBILITY_ID`='".(float)$this->dataRow['DIVISIBILITY_ID']."' LIMIT 1";
				$result = $this->databaseClass->SqlQuery($query);

				if($result != NULL && mysql_num_rows($result) == 1)
				{
					$result = $this->dataRow = mysql_fetch_array($result);
				}
			}

			return $result;
		}
	}

/*
CREATE  TABLE IF NOT EXISTS `build`.`DIVISIBILITY` (
  `DIVISIBILITY_ID` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `GUID` VARCHAR(36) NULL ,
  `NAME` VARCHAR(200) NULL ,
  `WEIGHT_VOL` INT NULL ,
  `WIDTH` INT UNSIGNED NULL ,
  `LENGTH` INT UNSIGNED NULL ,
  `HEIGHT` INT UNSIGNED NULL ,
  `ORD` INT UNSIGNED NULL ,
  `PARENT_ID` BIGINT UNSIGNED NULL ,
  `PARENT1_ID` BIGINT UNSIGNED NULL ,
  `PARENT2_ID` BIGINT UNSIGNED NULL ,
  `PARENT3_ID` BIGINT UNSIGNED NULL ,
  PRIMARY KEY (`DIVISIBILITY_ID`) ,
  UNIQUE INDEX `DIVISIBILITY_ID_UNIQUE` (`DIVISIBILITY_ID` ASC) )
ENGINE = MyISAM
DEFAULT CHARACTER SET = utf8
COMMENT = 'Подільність матеріалів'
*/
?>