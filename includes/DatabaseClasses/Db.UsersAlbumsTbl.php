<?php

  if (@preg_match("/includes/i", $_SERVER['PHP_SELF'])) 
    die ("You can't access this file directly...");

	include_once("includes/DatabaseClasses/Db.CommonTable.php");
	include_once("includes/DatabaseClasses/Db.IDataConnector.php");

	/**
	 * The Package collects the UsersAlbums related classes etc
	 *
	 * @package Users.pkg
	 */
	
	/**
	 * class UsersAlbumsTbl 
	 *
	 * The UsersAlbumsTbl. Used for the operating with the users
	 * 
	 * @package Users.pkg
	 */
	 
	class UsersAlbumsTbl extends CommonTable implements IDataConnector 
	{
		public $tableName = "`build`.`USERS_ALBUMS`";

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
			
			$query = "SELECT * FROM <%prefix%>".$this->tableName." WHERE `MATERIALS_GROUPS_ID`='".(float)$this->dataRow['MATERIALS_GROUPS_ID']."' LIMIT 1";
			
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
			
			$query = "SELECT * FROM <%prefix%>".$this->tableName." ORDER BY `PARENT_GROUPS_ID`, `PARENT_GROUPS1_ID`, `PARENT_GROUPS2_ID`, `PARENT_GROUPS3_ID`, `PARENT_GROUPS4_ID`, `GROUPS_NAME` ";
			$result = $this->databaseClass->SqlQuery($query);
			
			return $result;
		}	
		
		public function DeleteAlbumById()
		{
			$result = NULL;

			$query = "DELETE FROM <%prefix%>".$this->tableName." WHERE `USERS_ALBUMS_ID`='".(float)$this->dataRow['USERS_ALBUMS_ID']."' LIMIT 1";
			$result = $this->databaseClass->SqlQuery($query);
			
			$result = mysql_affected_rows();

			return result;
		}
		
		public function DeleteAlbums()
		{
			$result = NULL;
			
			$resultOld = $this->LoadAlbums();
			if(mysql_num_rows($resultOld) > 0)
			{			
				$query = "DELETE FROM <%prefix%>".$this->tableName." WHERE (`ENTITIES_ID`='".$this->dataRow['ENTITIES_ID']."' AND `OBJECTS_ID`='".$this->dataRow['OBJECTS_ID']."') ";
				$result = $this->databaseClass->SqlQuery($query);
				
				if(mysql_affected_rows())
					$result = $resultOld;
				else 
					$result = NULL;
			}
			
			return $resultOld;
		}
		
		public function LoadAlbums()
		{
			$this->isLoaded = false;
			
			$where = "";

			if((float)$this->dataRow['OBJECTS_ID'] > 0)
			{
				$where = " `OBJECTS_ID`='".(float)$this->dataRow['OBJECTS_ID']."' ";
			}
			
			if((float)$this->dataRow['ENTITIES_ID'] > 0)
			{
				if(strlen($where) > 0)
				{
					$where .= ' AND ';
				}
				
				$where .= " `ENTITIES_ID`='".(float)$this->dataRow['ENTITIES_ID']."' ";
			}
			
			$query = "SELECT * FROM <%prefix%>".$this->tableName." WHERE ".$where." ORDER BY `PARENT0`, `PARENT1`, `PARENT2`, `PARENT3`, `PARENT4`, `TITLE` ";
			$result = $this->databaseClass->SqlQuery($query);

			return $result;
		}
		
		public function SaveAlbum()
		{
			$this->isLoaded = false;
			
			$id = 0;
		
			$query = "SELECT * FROM <%prefix%>".$this->tableName." WHERE `USERS_ALBUM_ID` = '".(float)$this->dataRow['USERS_ALBUM_ID']."' LIMIT 1";
			$result = $this->databaseClass->SqlQuery($query);

			if($result != NULL && $row = mysql_fetch_array($result))
			{
				$query = "UPDATE TABLE <%prefix%>".$this->tableName." SET `UPDATED_TIME`='".(float)$this->updatedTimeValue."', `UPDATED_LOGIN`='".(float)$this->updatedLogin."', `ENTITIES_ID`='".(float)$this->dataRow['ENTITIES_ID']."', `OBJECTS_ID`='".(float)$this->dataRow['OBJECTS_ID']."', `LANGS_ID`='".(float)$this->dataRow['LANGS_ID']."', `PARENT0`='".(float)$this->dataRow['PARENT0']."', `PARENT1`='".(float)$this->dataRow['PARENT1']."', `PARENT2`='".(float)$this->dataRow['PARENT2']."', `PARENT3`='".(float)$this->dataRow['PARENT3']."', `PARENT4`='".(float)$this->dataRow['PARENT4']."', `ORD`='".(float)$this->dataRow['ORD']."', `TITLE`='".substr($this->dataRow['TITLE'], 0, 200)."', `DESCRIPTION`='".substr($this->dataRow['DESCRIPTION'], 0, 5000)."'  WHERE `USERS_ALBUM_ID` = '".(float)$this->dataRow['USERS_ALBUM_ID']."' LIMIT 1";
				$result = $this->databaseClass->SqlQuery($query); 
				
				if($result != NULL)
					$result = true;
				else
					$result = false;
			}
			else
			{
				$result = $this->CreateAlbum();
			}
			
			return $result;
		}
		
		public function CreateAlbum()
		{
			$query = "INSERT INTO <%prefix%>".$this->tableName." (`GUID`, `CREATED_TIME`, `UPDATED_TIME`, `UPDATED_LOGIN`, `ENTITIES_ID`, `OBJECTS_ID`, `LANGS_ID`, `PARENT0`, `PARENT1`, `PARENT2`, `PARENT3`, `PARENT4`, `ORD`, `TITLE`, `DESCRIPTION`) ".
					 " 	VALUES    (".$this->databaseClass->GuidColumn().", ".$this->createdTimeValue.", ".$this->updatedTimeValue.", '".(float)$this->updatedLogin."', '".(float)$this->dataRow['ENTITIES_ID']."', '".(float)$this->dataRow['OBJECTS_ID']."', '".(float)$this->dataRow['LANGS_ID']."', '".(float)$this->dataRow['PARENT0']."', '".(float)$this->dataRow['PARENT1']."', '".(float)$this->dataRow['PARENT2']."', '".(float)$this->dataRow['PARENT3']."', '".(float)$this->dataRow['PARENT4']."', ".
					 "             '".(float)$this->dataRow['ORD']."', '".substr($this->dataRow['TITLE'], 0, 200)."', '".substr($this->dataRow['DESCRIPTION'], 0, 5000)."')";
					 
			$result = $this->databaseClass->SqlQuery($query);

			$id = mysql_insert_id();
			return $id;
		}
	}

/*
CREATE  TABLE IF NOT EXISTS `build`.`USERS_ALBUM` (
  `USERS_ALBUM_ID` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `GUID` VARCHAR(45) NULL ,
  `CREATED_TIME` TIMESTAMP NULL ,
  `UPDATED_TIME` TIMESTAMP NULL ,
  `UPDATED_LOGIN` BIGINT UNSIGNED NULL ,
  `ENTITIES_ID` BIGINT UNSIGNED 0 ,
  `OBJECTS_ID` BIGINT UNSIGNED 0 ,
  `PARENT0` BIGINT UNSIGNED NULL ,
  `PARENT1` BIGINT UNSIGNED NULL ,
  `PARENT2` BIGINT UNSIGNED NULL ,
  `PARENT3` BIGINT UNSIGNED NULL ,
  `PARENT4` BIGINT UNSIGNED NULL ,
  `ORD` INT NULL ,
  `DESCRIPTION` VARCHAR(5000) NULL ,
  PRIMARY KEY (`USERS_ALBUM_ID`) ,
  UNIQUE INDEX `USERS_ALBUM_ID_UNIQUE` (`USERS_ALBUM_ID` ASC) )
ENGINE = InnoDB
COMMENT = 'Альбом користувача'
*/
?>