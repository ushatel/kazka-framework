<?php
 
  if (@preg_match("/includes/i", $_SERVER['PHP_SELF'])) 
    die ("You can't access this file directly...");

	include_once("includes/DatabaseClasses/Db.CommonTable.php");
	include_once("includes/DatabaseClasses/Db.IDataConnector.php");

	/**
	 * The Package collects the Users related classes etc
	 *
	 * @package Users.pkg
	 */
	
	/**
	 * class UsersFriendsTbl
	 *
	 * The main class of the UsersFriends. Used for the operating with the friends
	 * 
	 * @package Users.pkg
	 */
	class UsersFriendsTbl extends CommonTable implements IDataConnector 
	{
		public $tableName = "`build`.`USERS_FRIENDS`";

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
		
		public function LoadPrimaryFriends($limit = 0, $offset = 0)
		{			
			$limit_text = "";
			if($limit > 0 || $offset > 0)
			{
				$limit_text = " LIMIT ".(float)$limit;
				
				if($offset > 0)
				{
					$limit_text = ",".(float)$offset;
				}
			}		
		
			$result = NULL;
			
			$query = "	SELECT * FROM <%prefix%>".$this->tableName." ".
					 "  WHERE `PRIMARY_ID`='".(float)$this->dataRow['PRIMARY_ID']."' OR `PRIMARY_COMPANIES_ID`='".(float)$this->dataRow['PRIMARY_COMPANIES_ID']."' ".$limit_text;

			$result = $this->databaseClass->SqlQuery($query);
			
			return $result;
		}
		
		/** 
		 * Додати друга
		 *
		 * Add the friend
		 */
		public function InsertFriend() 
		{
			$inserted_id = 0;
			
			$query = "INSERT INTO <%prefix%>".$this->tableName." ".
					 " (`GUID`, `CREATED_TIME`, `PRIMARY_ID`, `SECONDARY_ID`, `PRIMARY_COMPANIES_ID`, `SECONDARY_COMPANIES_ID`) ".
					 " VALUES ".
					 " (".$this->databaseClass->GuidColumn().", ".$this->createdTimeValue.", ".
					 "  '".$this->dataRow['PRIMARY_ID']."', '".$this->dataRow['SECONDARY_ID']."', '".$this->dataRow['PRIMARY_COMPANIES_ID']."', '".$this->dataRow['SECONDARY_COMPANIES_ID']."') ";
					 
			$result = $this->databaseClass->SqlQuery($query);
			
			if($result != NULL)
			{
				$inserted_id = mysql_insert_id();
			}
			
			return $inserted_id;
		}
		
		/**
		 * Видалити з друзів
		 */
		public function DeleteFriend()
		{
			$result = false;
			
			$query = "DELETE FROM <%prefix%>".$this->tableName." WHERE (`PRIMARY_ID`='".$this->dataRow['PRIMARY_ID']."' OR `SECONDARY_ID`='".$this->dataRow['SECONDARY_ID']."' OR `PRIMARY_COMPANIES_ID`='".(float)$this->dataRow['PRIMARY_COMPANIES_ID']."' OR `SECONDARY_COMPANIES_ID`='".(float)$this->dataRow['SECONDARY_COMPANIES_ID']."') ";
			
			$result = $this->databaseClass->SqlQuery($query);
			
			if($result != NULL)
			{
				$result = true;
			}
			
			return $result;
		}
	}

//build.USERS

/*
CREATE  TABLE IF NOT EXISTS `INNODB_TABLES`.`USERS_FRIENDS` (
  `USERS_FRIENDS_ID` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `GUID` VARCHAR(36) NULL ,
  `CREATED_TIME` TIMESTAMP NULL ,
  `PRIMARY_ID` BIGINT UNSIGNED NULL DEFAULT 0 ,
  `SECONDARY_ID` BIGINT UNSIGNED NULL DEFAULT 0 ,
  `PRIMARY_COMPANIES_ID` BIGINT UNSIGNED NULL DEFAULT 0 ,
  `SECONDARY_COMPANIES_ID` BIGINT UNSIGNED NULL DEFAULT 0 ,
  PRIMARY KEY (`USERS_FRIENDS_ID`) ,
  UNIQUE INDEX `USERS_FRIENDS_ID_UNIQUE` (`USERS_FRIENDS_ID` ASC) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = 'Таблиця друзів'
*/
?>