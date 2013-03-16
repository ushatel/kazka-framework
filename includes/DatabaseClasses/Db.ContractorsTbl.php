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
	 * class UsersTbl
	 *
	 * The main class of the Users. Used for the operating with the users
	 * 
	 * @package Users.pkg
	 */
	class ContractorsTbl extends CommonTable implements IDataConnector 
	{
		public $tableName = "`build`.`CONTRACTORS`";

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
		 * Завантаження полей користувача за логіном 
		 *
		 * Загрузка полей пользователя, по полю Login
		 */
		public function LoadUser($userLogin)
		{
			$query = "select * from <%prefix%>".$this->tableName." where login=`".$userLogin."` limit `1`";

			$result = $this->databaseClass->SqlQuery($query);
			if(($result != NULL) && mysql_num_rows($result) == 1) 
			{
				$this->dataRow = mysql_fetch_array($result);
				$this->isLoaded = true;
			}
			else 
			{
				$this->isLoaded = false;
			}
			
			return $this->dataRow;
		}
		
		/** 
		 * Створення запису нового користувача
		 *
		 * Creation of the new user
		 */
		public function CreateContractor() 
		{
			$inserted_id = 0;
			$updated_id = Session::GetUserId();
			
			$query = "SELECT `CONTRACTORS_ID` FROM <%prefix%>".$this->tableName." ".
					 "WHERE `USERS_ID` = '".$this->dataRow['USERS_ID']."' LIMIT 1";
			
			$result = $this->databaseClass->SqlQuery($query);

			if(mysql_num_rows($result) == 1) {
				$row = mysql_fetch_array($result);
				
				$query = "UPDATE <%prefix%>".$this->tableName." SET ".
						 " `UPDATED_TIME` = ".$this->updatedTimeValue.", `UPDATED_LOGIN`='".(int)$updated_id."' , ".
						 " `COMPANIES_ID` = '".$this->dataRow['COMPANIES_ID']."', `COUNTRY_ID`='".$this->dataRow['COUNTRY_ID']."', `CITY_ID`='".$this->dataRow['CITY_ID']."', `FIRST_NAME`='".$this->dataRow['FIRST_NAME']."', `SECOND_NAME`='".$this->dataRow['SECOND_NAME']."', ". 
						 " `EMAIL2`='".$this->dataRow['EMAIL2']."' WHERE `CONTRACTORS_ID`='".$row['CONTRACTORS_ID']."' LIMIT 1 ;";

				$inserted_id = $row['CONTRACTORS_ID'];
			}
			else {		 
			
				$query = "INSERT INTO <%prefix%>".$this->tableName." ".
						 " ( `GUID`, `CREATED_TIME`, `UPDATED_TIME`, `UPDATED_LOGIN`, `USERS_ID`, `COMPANIES_ID`, `COUNTRY_ID`, `CITY_ID`, ".
						 "	`FIRST_NAME`, `SECOND_NAME`, `EMAIL2` ) ".
						 " VALUES ".
						 " (".$this->databaseClass->GuidColumn().", ".$this->createdTimeValue.", ".$this->updatedTimeValue.", '".(int)$updated_id."', '".$this->dataRow['USERS_ID']."', ".
						 "  '".$this->dataRow['COMPANIES_ID']."', '".$this->dataRow['COUNTRY_ID']."', ".
						 "	'".$this->dataRow['CITY_ID']."', '".$this->dataRow['FIRST_NAME']."', '".$this->dataRow['SECOND_NAME']."',".
						 "  '".$this->dataRow['EMAIL2']."'); ";
				}
					 
			$result = $this->databaseClass->SqlQuery($query);

			if($result != NULL) 
			{
				$inserted_id = mysql_insert_id();
				$this->isLoaded = true;
			}
			else 
			{
				$this->isLoaded = false;
			}
			
			return $inserted_id;
		}
	}

//build.CONTRACTORS

/*
CREATE  TABLE IF NOT EXISTS `INNODB_TABLES`.`CONTRACTORS` (
  `CONTRACTORS_ID` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `GUID` VARCHAR(36) NULL ,
  `CREATED_TIME` TIMESTAMP NULL ,
  `UPDATED_TIME` TIMESTAMP NULL ,
  `UPDATED_LOGIN` BIGINT UNSIGNED NULL ,
  `USERS_ID` BIGINT UNSIGNED NULL DEFAULT 0 ,
  `COMPANIES_ID` BIGINT UNSIGNED NULL DEFAULT 0 ,
  `COUNTRY_ID` BIGINT UNSIGNED NULL ,
  `CITY_ID` BIGINT UNSIGNED NULL ,
  `FIRST_NAME` VARCHAR(100) NULL ,
  `SECOND_NAME` VARCHAR(100) NULL ,
  `EMAIL2` VARCHAR(100) NULL ,
  PRIMARY KEY (`CONTRACTORS_ID`) ,
  UNIQUE INDEX `CONTRACTORS_ID_UNIQUE` (`CONTRACTORS_ID` ASC) ,
  INDEX `FK_CONTRACTORS_COMPANIES` (`COMPANIES_ID` ASC) ,
  INDEX `FK_CONTRACTORS_USERS` (`USERS_ID` ASC) ,
  CONSTRAINT `FK_CONTRACTORS_COMPANIES`
    FOREIGN KEY (`COMPANIES_ID` )
    REFERENCES `INNODB_TABLES`.`COMPANIES` (`COMPANIES_ID` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `FK_CONTRACTORS_USERS`
    FOREIGN KEY (`USERS_ID` )
    REFERENCES `MYISAM_TABLES`.`USERS` (`USERS_ID` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = 'Підрядники/робітники'
*/
?>