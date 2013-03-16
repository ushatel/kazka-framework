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
	 * class SearchVocabularyTbl
	 *
	 * The main class of the SearchVocabularyTbl. Used for the operating with the table of SearchVocabularyTbl
	 * 
	 * @package Search.pkg
	 */
	 
	class SearchVocabularyTbl extends CommonTable implements IDataConnector 
	{
		public $tableName = "`build`.`SEARCH_VOCABULARY`";

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
		
		public function GetWord()
		{
			$result = NULL;

			$query = "SELECT * FROM <%prefix%>".$this->tableName." WHERE `WORDS_ID`='".(float)$this->dataRow['WORDS_ID']."' OR `NAME`='".substr($this->dataRow['NAME'], 0, 150)."' LIMIT 1";

			if($this->dataRow['WORDS_ID'] > 0 || strlen($this->dataRow['NAME']) > 0)
			{
				$result = $this->databaseClass->SqlQuery($query);
			}

			if($result != NULL && (mysql_num_rows($result) == 1))
			{
				$result = $this->dataRow = mysql_fetch_array($result);
			}
			else
			{
				$result = NULL;
			}
			
			return $result;
		}
		
		public function UpdateWordRating()
		{
			$result = NULL; 
			
			$query = "UPDATE FROM <%prefix%>".$this->tableName." SET `UPDATED_TIME`=".$this->updatedTimeValue.", `UPDATED_LOGIN`='".(float)$this->updatedLogin."', `RATING`='".(float)$this->dataRow['RATING']."' WHERE (`WORDS_ID`='".(float)$this->dataRow['WORDS_ID']."' OR `NAME`='".substr($this->dataRow['NAME'], 0, 150)."') LIMIT 1";
			
			if($this->dataRow['WORDS_ID'] > 0 || strlen($this->dataRow['NAME']) > 0 )
			{
				$result = $this->databaseClass->SqlQuery($query);
			}
			
			if($result == 1)
			{
				$result = $this->GetWord();
			}
			
			return $result;
		}
		
		public function IncWordRating()
		{
			$result = NULL; 
						
			if($this->dataRow['WORDS_ID'] > 0 || strlen($this->dataRow['NAME']) > 0 )
			{
				$query = "UPDATE FROM <%prefix%>".$this->tableName." SET `UPDATED_TIME`=".$this->updatedTimeValue.", `UPDATED_LOGIN`='".(float)$this->updatedLogin."', `RATING`=`RATING`+".(float)$this->dataRow['RATING']." WHERE (`WORDS_ID`='".(float)$this->dataRow['WORDS_ID']."' OR `NAME`='".substr($this->dataRow['NAME'], 0, 150)."') LIMIT 1";
			
				$result = $this->databaseClass->SqlQuery($query);
			}
			
			if($result == 1)
			{
				$result = $this->GetWord();
			}
			
			return $result;
		}		
		
		public function CreateWord()
		{
			$result = NULL;

			if(strlen($this->dataRow['NAME']) > 0)
			{
				$query = " INSERT INTO <%prefix%>".$this->tableName." (`GUID`, `CREATED_TIME`, `UPDATED_TIME`, `UPDATED_LOGIN`, `NAME`, `RATING`, `LANGS_ID`) ".
						 " VALUES (".$this->databaseClass->GuidColumn().", ".$this->createdTimeValue.", ".$this->updatedTimeValue.", '".(float)$this->updatedLogin."', '".substr($this->dataRow['NAME'], 0, 150)."', '".(float)$this->dataRow['RATING']."', '".(float)$this->dataRow['LANGS_ID']."') ";
			
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
CREATE  TABLE IF NOT EXISTS `MYISAM_TABLES`.`MATERIALS_GROUPS` (
  `MATERIALS_GROUPS_ID` BIGINT NOT NULL AUTO_INCREMENT ,
  `CREATED_TIME` TIMESTAMP NULL DEFAULT NOW() ,
  `UPDATED_TIME` TIMESTAMP NULL DEFAULT NOW() ,
  `UPDATED_LOGIN` BIGINT NULL ,
  `GROUPS_NAME` VARCHAR(100) NULL ,
  PRIMARY KEY (`MATERIALS_GROUPS_ID`) )
ENGINE = MyISAM
DEFAULT CHARACTER SET = utf8
COMMENT = '&#65533;&#65533;&#65533;&#65533;&#65533;&#65533;&#65533;&#65533; &#65533;&#65533;&#65533;&#65533;&#65533;&#65533;&#65533;. \n&#65533; &#65533;&#65533;&#65533;&#65533;&#65533;&#65533; &#65533;&#65533;&#65533;&#65533; &#65533;&#65533;&#65533;&#65533; &#65533;&#65533;&#65533;&#65533;&#65533;&#65533;&#65533;&#65533;&#65533;&#65533;&#65533; &#65533;&#65533;&#65533;&#65533;&#65533;&#65533;&#65533;&#65533;&#65533;&#65533;&#65533;&#65533;&#65533;\n'
*/
?>