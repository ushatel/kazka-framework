<?php

  if (@preg_match("/includes/i", $_SERVER['PHP_SELF'])) 
    die ("You can't access this file directly...");

	include_once("includes/DatabaseClasses/Db.CommonTable.php");
	include_once("includes/DatabaseClasses/Db.IDataConnector.php");

	/**
	 * The Package collects the News related classes etc
	 *
	 * @package News.pkg
	 */
	
	/**
	 * class NewsTbl
	 *
	 * The main class of the Users. Used for the operating with the users
	 * 
	 * @package News.pkg
	 */
	class NewsTbl extends CommonTable implements IDataConnector 
	{
		public $tableName = "`build`.`NEWS`";

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


		/**
		 * Повернути DataRow базового класу
		 */	
		public function GetDataRow() 
		{
			return $this->dataRow;
		}
		
		/**
		 * Повертає кількість записів в таблиці
		 */
		 
		public function RowsCount()
		{
			$result = NULL;
			
			$query = " SELECT COUNT(`NEWS_ID`) FROM ".$this->tableName." ";
			$result = $this->databaseClass->SqlQuery($query);
			
			if($result != NULL)
			{
				$res = mysql_fetch_array($result);
				$result = $res[0];
			}
			
			return $result;
		}
		
		/**
		 * Повертає новини за запитом
		 */
		public function LoadNews($name = '', $limit = 0, $offset = 0, $order_by = '`NEWS_NAME`')
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
			
			$uniq_name = "";
			if(strlen($this->dataRow['UNIQUE_NAME_IDENTIFIER']) > 0) 
			{
				$uniq_name = "OR `UNIQUE_NAME_IDENTIFIER`='".$this->dataRow['UNIQUE_NAME_IDENTIFIER']."'";
			}
			else
			{
				$uniq_name = "";
			}
			
			$where = "";
			
			if($this->dataRow['UPDATED_LOGIN'] !== NULL && (int)$this->dataRow['UPDATED_LOGIN'] > 0)
			{
				if(strlen($where) > 0)
				{
					$where .= " OR ";
				}
				
				$where .= "`UPDATED_LOGIN` = '".(float)$this->dataRow['UPDATED_LOGIN']."'";
			}
			
			if($this->dataRow['NEWS_ID'] > 0)
			{
				if(strlen($where) > 0)
				{
					$where .= " OR ";
				}
			
				$where .= "`NEWS_ID` = '".(float)$this->dataRow['NEWS_ID']."'";
			}
			
			if($this->dataRow['COMPANIES_ID'] > 0)
			{
				if(strlen($where) > 0)
				{
					$where .= " OR ";
				}
				
				$where .= "`COMPANIES_ID` = '".(float)$this->dataRow['COMPANIES_ID']."'";
			}
			
			if($this->dataRow['OBJECTS_ID'] > 0)
			{
				if(strlen($where) > 0)
				{
					$where .= " OR ";
				}
			
				$where .= "`OBJECTS_ID` = '".(float)$this->dataRow['OBJECTS_ID']."'";
			}
			
			if($this->dataRow['IS_PUBLIC'] !== NULL && (int)$this->dataRow['IS_PUBLIC'] > 0)
			{
				if(strlen($where) > 0)
				{
					$where .= " OR ";
				}
				
				$where .= "`IS_PUBLIC` = '".(int) $this->dataRow['IS_PUBLIC']."'";
			}
			
			if($this->dataRow['IS_ACTIVE'] > 0)
			{
				if(strlen($where) > 0)
				{
					$where .= " OR ";
				}
				
				$where .= "`IS_ACTIVE`='".$this->dataRow['IS_ACTIVE']."' ";
			}
			
			if(strlen($where) > 0)
				$where = " WHERE ".$where;
			
			$query = " SELECT * FROM <%prefix%>".$this->tableName." ".
					 " ".$where." ".$uniq_name." ORDER BY ".$order_by." ".$limit_text;

			$result = $this->databaseClass->SqlQuery($query);

			return $result;
		}	
		
		public function LoadNewsById()
		{
			$result = false;
			
			$query = "SELECT * FROM <%prefix%>".$this->tableName." WHERE `NEWS_ID`='".$this->dataRow['NEWS_ID']."' ";
			$result = $this->databaseClass->SqlQuery($query);
			
			if($result != NULL)
			{
				$this->dataRow = NULL;
				$this->dataRow = mysql_fetch_array($result);
			}
			else
			{
				$this->dataRow = NULL;
			}
			
			return $this->dataRow;
		}
		
		
		/**
		 * Перевірка на унікальність
		 */
		public function ValidateUniqueIdentifier()
		{
			$result = NULL;
			
			$query = "SELECT * FROM <%prefix%>".$this->tableName." WHERE `UNIQUE_NAME_IDENTIFIER`='".$this->dataRow['UNIQUE_NAME_IDENTIFIER']."' LIMIT 1";
			$result = $this->databaseClass->SqlQuery($query);
			
			if(mysql_num_rows($result) == 1)
			{
				$this->dataRow = NULL;
				$this->dataRow = mysql_fetch_array($result);
			}
			else
			{
				$this->dataRow = NULL;
			}
			
			return $this->dataRow;
		}
		
		/**
		 * Дозволяє публікацію новини для загального доступу
		 */
		public function ActivateNews()
		{
			$active = false;
			
			$query = "UPDATE <%prefix%>".$this->tableName." ".
					 " SET `UPDATED_LOGIN`='".$this->updatedLogin."', `UPDATED_TIME`=".$this->updatedTimeValue.", `IS_ACTIVE`='1' WHERE `NEWS_ID`='".$this->dataRow['NEWS_ID']."' LIMIT 1";
			
			$result = $this->databaseClass->SqlQuery($query);
			
			if($result != NULL)
			{
				$active = true;
			}
			
			return $active;
		}
		
		/** 
		 *
		 * Creation of the new user
		 */
		public function InsertNews() 
		{
			$inserted_id = 0;

			$query = "INSERT INTO <%prefix%>".$this->tableName." ".
					 " ( `CREATED_TIME`, `UPDATED_TIME`, `UPDATED_LOGIN`, `UNIQUE_NAME_IDENTIFIER`,". 
					 "   `NEWS_NAME`, `NEWS_TEXT`, `COMPANIES_ID`, `OBJECTS_ID`, `ENTITIES_ID`, `LANGS_ID`, `IS_ACTIVE`,".
					 "   `IS_PUBLIC`, `COUNTRIES_ID`, `PUBLICATION_TIME` ) ".
					 " VALUES ".
					 " (".$this->createdTimeValue.", ".$this->updatedTimeValue.", '".$this->updatedLogin."', '".$this->dataRow['UNIQUE_NAME_IDENTIFIER']."', ".
					 "  '".$this->dataRow['NEWS_NAME']."', '".$this->dataRow['NEWS_TEXT']."', '".$this->dataRow['COMPANIES_ID']."', '".$this->dataRow['OBJECTS_ID']."', '".$this->dataRow['ENTITIES_ID']."', '".$this->dataRow['LANGS_ID']."', ".
					 "  '".$this->dataRow['IS_ACTIVE']."', '".(int)$this->dataRow['IS_PUBLIC']."', '".$this->dataRow['COUNTRIES_ID']."', FROM_UNIXTIME(".$this->dataRow['PUBLICATION_TIME'].") ) ";

			$result = $this->databaseClass->SqlQuery($query);

			if($result != NULL) 
			{
				$inserted_id = mysql_insert_id();
			}
			
			return $inserted_id;
		}

		public function SaveNews()
		{
			$insert_id = 0;
			
			$query = "SELECT `NEWS_ID` FROM <%prefix%>".$this->tableName." WHERE `NEWS_ID` = '".$this->dataRow['NEWS_ID']."' LIMIT 1";
			$result = $this->databaseClass->SqlQuery($query);
			
			if($result != NULL && mysql_num_rows($result) > 0)
			{
				$query = "UPDATE <%prefix%>".$this->tableName." SET `UPDATED_TIME` = ".$this->updatedTimeValue.", `UPDATED_LOGIN`='".$this->updatedLogin."', `UNIQUE_NAME_IDENTIFIER`='".$this->dataRow['UNIQUE_NAME_IDENTIFIER']."', ".
						 "   `NEWS_NAME` = '".$this->dataRow['NEWS_NAME']."', `NEWS_TEXT` = '".$this->dataRow['NEWS_TEXT']."', `NEWS_SHORT_TEXT` = '".$this->dataRow['NEWS_SHORT_TEXT']."', `COMPANIES_ID`='".$this->dataRow['COMPANIES_ID']."', `OBJECTS_ID`='".$this->dataRow['OBJECTS_ID']."', `ENTITIES_ID`='".$this->dataRow['ENTITIES_ID']."', `LANGS_ID`='".$this->dataRow['LANGS_ID']."', ".
						 "	 `IS_ACTIVE`='".$this->dataRow['IS_ACTIVE']."', `IS_PUBLIC`='".$this->dataRow['IS_PUBLIC']."', `COUNTRIES_ID`='".$this->dataRow['COUNTRIES_ID']."', PUBLICATION_TIME = FROM_UNIXTIME(".$this->dataRow['PUBLICATION_TIME'].") ".
						 " WHERE `NEWS_ID`='".$this->dataRow['NEWS_ID']."' LIMIT 1";
						 
				$result = $this->databaseClass->SqlQuery($query);
			}
			else
			{
				$query = "INSERT INTO <%prefix%>".$this->tableName." ".
						 "   (`CREATED_TIME`, `UPDATED_TIME`, `UPDATED_LOGIN`, `UNIQUE_NAME_IDENTIFIER`, `NEWS_NAME`, `NEWS_TEXT`, `NEWS_SHORT_TEXT`, `COMPANIES_ID`, ".
						 "    `OBJECTS_ID`, `ENTITIES_ID`, `LANGS_ID`, `IS_ACTIVE`, `IS_PUBLIC`, `COUNTRIES_ID`, `PUBLICATION_TIME` ) ".
						 " VALUES ".
						 "   (".$this->createdTimeValue.", ".$this->updatedTimeValue.", '".$this->updatedLogin."', '".$this->dataRow['UNIQUE_NAME_IDENTIFIER']."', '".$this->dataRow['NEWS_NAME']."', ".
						 "	  '".$this->dataRow['NEWS_TEXT']."', '".$this->dataRow['NEWS_SHORT_TEXT']."', '".$this->dataRow['COMPANIES_ID']."', '".$this->dataRow['OBJECTS_ID']."', '".$this->dataRow['ENTITIES_ID']."', '".$this->dataRow['LANGS_ID']."', ".
						 "    '".$this->dataRow['IS_ACTIVE']."', '".$this->dataRow['IS_PUBLIC']."', '".$this->dataRow['COUNTRIES_ID']."', FROM_UNIXTIME(".$this->dataRow['PUBLICATION_TIME'].") )";
				$result = $this->databaseClass->SqlQuery($query);

				if($result != NULL)
				{
					$result = mysql_insert_id();
				}
			}

			return $result;
		}
		
		public function DeleteNews()
		{
			$result = NULL;
			
			$query = "DELETE FROM <%prefix%>".$this->tableName." WHERE `NEWS_ID`='".$this->dataRow['NEWS_ID']."' LIMIT 1";
			$result = $this->databaseClass->SqlQuery($query);
			
			return $result;
		}
		
	}

//build.USERS

/*
CREATE  TABLE IF NOT EXISTS `INNODB_TABLES`.`NEWS` (
  `NEWS_ID` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `GUID` VARCHAR(36) NULL ,
  `CREATED_TIME` TIMESTAMP NULL ,
  `UPDATED_TIME` TIMESTAMP NULL ,
  `UPDATED_LOGIN` BIGINT NULL DEFAULT 0 ,
  `UNIQUE_NAME_IDENTIFIER` VARCHAR(150) NULL ,
  `NEWS_NAME` VARCHAR(150) NULL ,
  `NEWS_SHORT_TEXT` VARCHAR(500) NULL ,
  `NEWS_TEXT` VARCHAR(5000) NULL ,
  `COMPANIES_ID` BIGINT UNSIGNED NULL DEFAULT 0 ,
  `OBJECTS_ID` BIGINT UNSIGNED NULL DEFAULT 0 ,
  `ENTITIES_ID` BIGINT UNSIGNED NULL DEFAULT 0 ,
  `LANGS_ID` BIGINT UNSIGNED NULL DEFAULT 0 ,
  `IS_ACTIVE` INT(1) UNSIGNED NULL DEFAULT 0 ,
  `IS_PUBLIC` INT(1) UNSIGNED NULL DEFAULT 0 ,
  `COUNTRIES_ID` BIGINT UNSIGNED NULL DEFAULT 0 ,
  `PUBLICATION_TIME` TIMESTAMP NULL ,
  PRIMARY KEY (`NEWS_ID`) ,
  UNIQUE INDEX `NEWS_TABLE_ID_UNIQUE` (`NEWS_ID` ASC) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = 'Таблиця новин'
*/

?>