<?php

	if (@preg_match("/includes/i", $_SERVER['PHP_SELF'])) 
    	die ("You can't access this file directly...");

	include_once("includes/DatabaseClasses/Db.CommonTable.php");
	include_once("includes/DatabaseClasses/Db.IDataConnector.php");

	/**
	 * The Package collects the Search related classes etc
	 *
	 * @package Search.pkg
	 */
	
	/**
	 * class SearchTbl
	 *
	 * The main class of the SearchTbl. Used for the operating with the table of search engine
	 * 
	 * @package Search.pkg
	 */
	 
	class SearchIndexTbl extends CommonTable implements IDataConnector 
	{
		public $tableName = "`build`.`SEARCH_INDEX`";

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
		
		public function GetWordsByDocId()
		{
			
		}
		
		public function GetIndex()
		{
			$result = NULL;
			
			$query = "SELECT * FROM <%prefix%>".$this->tableName." WHERE `WORDS_ID`='".$this->dataRow['WORDS_ID']."' AND `ENTITIES_ID`='".$this->dataRow['ENTITIES_ID']."' AND (`OBJECT_ID`='".$this->dataRow['OBJECT_ID']."' OR `OBJECT_GUID`='".$this->dataRow['OBJECT_GUID']."') AND (`LANGS_ID` = '0' OR `LANGS_ID` = '".(float)$this->dataRow['LANGS_ID']."') ORDER BY `LANGS_ID` ASC LIMIT 1 ";
			$result = $this->databaseClass->SqlQuery($query);
			
			if($result != NULL && mysql_num_rows($result) == 1)
			{
				$result = $this->dataRow = mysql_fetch_array($result);
			}
			else
			{
				$result = NULL;
			}
			
			return $result;
		}
		
		public function UpdateIndex()
		{
			$result = NULL;
	
			if($this->GetIndex() != NULL)
			{	
				$query = "UPDATE <%prefix%>".$this->tableName." SET ".
						 " `UPDATED_TIME`=".$this->updatedTimeValue.", `UPDATED_LOGIN`='".$this->updatedLogin."', `QUANTITY`='".(float)$this->dataRow['QUANTITY']."' ".
						 "WHERE `WORDS_ID`='".(float)$this->dataRow['WORDS_ID']."' AND `ENTITIES_ID`='".(float)$this->dataRow['ENTITIES_ID']."' AND (`OBJECT_ID`='".(float)$this->dataRow['OBJECT_ID']."' OR `OBJECT_GUID`='".$this->dataRow['OBJECT_GUID']."') AND (`LANGS_ID` = '0' OR `LANGS_ID` = '".(float)$this->dataRow['LANGS_ID']."')  ORDER BY `LANGS_ID` ASC LIMIT 1";
				
				if($this->dataRow['WORDS_ID'] > 0 && $this->dataRow['ENTITIES_ID'] > 0 && $this->dataRow['OBJECT_ID'] > 0)
				{
					$result = $this->databaseClass->SqlQuery($query);
				}
				
				if($result == 1)
				{
					$result = $this->GetIndex();
				}
			}
			
			return $result;
		}
		
		public function CreateIndex()
		{
			$result = NULL;
			
			$query = "INSERT INTO <%prefix%>".$this->tableName." ".
					 "(`CREATED_TIME`, `UPDATED_TIME`, `UPDATED_LOGIN`, `WORDS_ID`, `ENTITIES_ID`, `LANGS_ID`, `QUANTITY`, `OBJECT_ID`, `OBJECT_GUID`) ".
					 " VALUES (".$this->createdTimeValue.", ".$this->updatedTimeValue.", '".(float)$this->updatedLogin."', '".(float)$this->dataRow['WORDS_ID']."', '".(float)$this->dataRow['ENTITIES_ID']."', '".(float)$this->dataRow['LANGS_ID']."', ".
					 " 		   '".$this->dataRow['QUANTITY']."', '".$this->dataRow['OBJECT_ID']."', '".$this->dataRow['OBJECT_GUID']."') ";
					 
			if($this->dataRow['WORDS_ID'] > 0 && $this->dataRow['ENTITIES_ID'] > 0 && $this->dataRow['OBJECT_ID'])
			{
				$result = $this->databaseClass->SqlQuery($query);
			}
			
			if($result != NULL)
			{
				$result = mysql_insert_id();
			}
			
			return $result;
		}
	}

/*
CREATE  TABLE IF NOT EXISTS `INNODB_TABLES`.`SEARCH_INDEX` (
  `SEARCH_INDEX_ID` BIGINT UNSIGNED NOT NULL ,
  `CREATED_TIME` TIMESTAMP NULL ,
  `UPDATED_TIME` TIMESTAMP NULL ,
  `UPDATED_LOGIN` BIGINT UNSIGNED NULL DEFAULT 0 ,
  `WORDS_ID` BIGINT UNSIGNED NULL DEFAULT 0 ,
  `ENTITIES_ID` BIGINT UNSIGNED NULL DEFAULT 0 ,
  `LANGS_ID` BIGINT UNSIGNED NULL DEFAULT 0 ,
  `QUANTITY` BIGINT UNSIGNED NULL DEFAULT 0 ,
  `OBJECT_ID` BIGINT UNSIGNED NULL DEFAULT 0 ,
  `OBJECT_GUID` VARCHAR(36) NULL ,
  PRIMARY KEY (`SEARCH_INDEX_ID`) ,
  UNIQUE INDEX `SEARCH_INDEX_ID_UNIQUE` (`SEARCH_INDEX_ID` ASC) ,
  INDEX `FK_ENTITIES` (`ENTITIES_ID` ASC) ,
  INDEX `WORDS` (`ENTITIES_ID` ASC, `OBJECT_ID` ASC, `WORDS_ID` ASC) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = 'Таблиця із індексами документів'
*/
?>