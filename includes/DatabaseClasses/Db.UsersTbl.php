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
	class UsersTbl extends CommonTable implements IDataConnector 
	{
		public $tableName = "`build`.`USERS`";

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
		 * Завантаження користувачів
		 */
		public function LoadUsers($limit = 0, $offset = 0, $order_by = '`LOGIN`')
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

			$login = "";
			if(strlen($this->dataRow['LOGIN']) > 0)
			{
				$login = "`LOGIN` like '%{$this->dataRow['LOGIN']}%' OR ";
			}

			$uniq_name = $this->dataRow['UNIQUE_NAME_IDENTIFIER'];

			$query = " SELECT * FROM <%prefix%>".$this->tableName." ".
					 " WHERE `ACTIVE`='1' OR ( `ACTIVE`='1' AND ( {$login} `USERS_ID`='".(float)$this->dataRow['USERS_ID']."')) ORDER BY {$order_by} {$limit_text} ";

			$result = $this->databaseClass->SqlQuery($query);

			return $result;
		}
		
		/**
		 * Завантаження користувача
		 */
		public function LoadUserById($userId)
		{
			$this->dataRow = NULL;
			
			$query = "SELECT * FROM <%prefix%>".$this->tableName." WHERE `USERS_ID`='".(float)$userId."' LIMIT 1";
			
			$result = $this->databaseClass->SqlQuery($query);
			if(($result != NULL) && mysql_num_rows($result) == 1)
			{
				$this->dataRow = mysql_fetch_array($result);
			}
			
			return $this->dataRow;
		}
		
		/**
		 * Завантаження полей користувача за логіном 
		 *
		 * Загрузка полей пользователя, по полю Login
		 */
		public function LoadUser($userLogin)
		{
			$this->dataRow = NULL;
			$query = "select * from <%prefix%>".$this->tableName." where `LOGIN`='".mysql_real_escape_string($userLogin)."' limit 1";

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
		 * Перевірка користувача, на логін та пароль
		 */
		public function ValidateUser()
		{
			$query = "select * from <%prefix%>".$this->tableName." where `LOGIN`='".$this->dataRow['LOGIN']."' AND `PASSWORD`='".$this->dataRow['PASSWORD']."' AND ACTIVE='1' LIMIT 1";
			
			$ip_current = $this->dataRow['IP_CURRENT'];

			$result = $this->databaseClass->SqlQuery($query);

			if($result != NULL && mysql_num_rows($result) == 1)
			{
				$this->dataRow = mysql_fetch_array($result);
				$this->isLoaded = true;
				
				$query = "UPDATE <%prefix%>".$this->tableName." SET `UPDATED_TIME`=".$this->updatedTimeValue.", `LAST_LOGIN_TIME`=".$this->updatedTimeValue.", `IP_LAST`=`IP_CURRENT`, `IP_CURRENT`='".$ip_current."' LIMIT 1";
				$result = $this->databaseClass->SqlQuery($query);
			}
			else 
			{
				$this->isLoaded = false;
			}

			return $this->isLoaded;
		}

		/** 
		 * Створення запису нового користувача
		 *
		 * Creation of the new user
		 */
		public function InsertUser() 
		{
			$inserted_id = 0;

			$query = "INSERT INTO <%prefix%>".$this->tableName." ".
					 " ( `CREATED_TIME`, `UPDATED_TIME`, `UPDATED_LOGIN`, `LOGIN`, `PASSWORD`, `EMAIL`, `LOGIN_ATTEMPTS`, ".
					 "	`LOCK_TIME`, `ACTIVE`, `CONFIRM_DATE`, `CONFIRM_TEXT`, `IP_CURRENT` ) ".
					 " VALUES ".
					 " (".$this->createdTimeValue.", ".$this->updatedTimeValue.", '".Session::GetUserId()."', '".$this->dataRow['LOGIN']."', ".
					 "  '".$this->dataRow['PASSWORD']."', '".$this->dataRow['EMAIL']."', '0', ".
					 "	'NULL', '0', FROM_UNIXTIME(".$this->dataRow['CONFIRM_DATE']."), '".$this->dataRow['CONFIRM_TEXT']."', '".$this->dataRow['IP_CURRENT']."');";
					 
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
		
		public function SaveUser()
		{
			$result = NULL;
			$oldRow = $this->dataRow;

			if($this->LoadUserById((float)$this->dataRow['USERS_ID'], false) != NULL)
			{
				$confirm = '';
				if($this->dataRow['CONFIRM_DATE'] > 0)
				{
					$confirm = ", `CONFIRM_DATE`=FROM_UNIXTIME(".$this->dataRow['CONFIRM_DATE'].") ";
				}
			
				$this->dataRow = $oldRow;
				$query = " UPDATE <%prefix%>".$this->tableName." SET ".
						 " `UPDATED_TIME`={$this->updatedTimeValue}, `UPDATED_LOGIN`='".Session::GetUserId()."', `EMAIL`='".$this->dataRow['EMAIL']."', `PUBLIC_EMAIL`='".$this->dataRow['PUBLIC_EMAIL']."', ".
						 " `ADDRESS`='".$this->dataRow['ADDRESS']."', `PHONE`='".$this->dataRow['PHONE']."', `PHONE2`='".$this->dataRow['PHONE2']."', `DESCRIPTION`='".$this->dataRow['DESCRIPTION']."', `IP_LAST`=`IP_CURRENT`, `IP_CURRENT`='".$this->dataRow['IP_CURRENT']."' ".$confirm." WHERE `USERS_ID`='".$this->dataRow['USERS_ID']."' LIMIT 1 ";
						 
				$result = $this->databaseClass->SqlQuery($query);
				$result = mysql_affected_rows();
			}
			else
			{
				$result = $this->InsertUser();
			}
			
			return $result;
		}
		
		public function ActivateUser()
		{
			$active = false;
			
			$query = "UPDATE <%prefix%>".$this->tableName." ".
					 " SET `ACTIVE`='1', `CONFIRM_DATE`=FROM_UNIXTIME(".$this->dataRow['CONFIRM_DATE']."), `IP_LAST`=`IP_CURRENT`, `IP_CURRENT`='".$this->dataRow['IP_CURRENT']."' WHERE `LOGIN`='".$this->dataRow['LOGIN']."' LIMIT 1";

			$result = $this->databaseClass->SqlQuery($query);
			
			if($result != NULL)
			{					 
				$active = true;
			}
			
			return $active;
		}
		
		public function SetUsersSIDbyLogin()
		{
			$query = "UPDATE <%prefix%>".$this->tableName." ".
					 " SET `SID`='".$this->dataRow['SID']."' WHERE `LOGIN`='".$this->dataRow['LOGIN']."' LIMIT 1";
			
			$result = $this->databaseClass->SqlQuery($query);
			
			if($result != NULL)
			{
				return true;
			}
			else 
			{
				return false;
			}
		}
		
		public function LoadUserBySID($SID)
		{
			$this->dataRow = NULL;
		
			$query = "SELECT * FROM <%prefix%>".$this->tableName." ".
					 " WHERE `SID`='".mysql_real_escape_string($SID)."' LIMIT 1";

			$result = $this->databaseClass->SqlQuery($query);
			if($result != NULL)
			{
				$this->dataRow = mysql_fetch_array($result);
				
				// new var for better protection!
				$this->dataRow['SID'] = Security::CreateSessionVar($this->dataRow['LOGIN']);
				
				// !!! ONCE IN 36 hours - no influe on speed
				$query = "UPDATE <%prefix%>".$this->tableName." SET `UPDATED_TIME`=".$this->updatedTimeValue.", `LAST_LOGIN_TIME`=".$this->updatedTimeValue.", `SID`='".$this->dataRow['SID']."' WHERE `LOGIN`='".$this->dataRow['LOGIN']."' LIMIT 1";
				$result = mysql_query($query);
 			}
			
			return $this->dataRow;
		}
	}

//build.USERS

/*
CREATE  TABLE IF NOT EXISTS `build`.`USERS` (
  `USERS_ID` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `CREATED_TIME` TIMESTAMP NULL ,
  `UPDATED_TIME` TIMESTAMP NULL default CURRENT_TIMESTAMP on update NOW(),
  `UPDATED_LOGIN` BIGINT UNSIGNED NULL ,
  `LOGIN` VARCHAR(50) NULL ,
  `PASSWORD` VARCHAR(50) NULL ,
  `EMAIL` VARCHAR(45) NULL ,
  `LOGIN_ATTEMPTS` INT NULL DEFAULT 0 ,
  `LAST_LOGIN_TIME` TIMESTAMP NULL ,
  `LOCK_TIME` TIMESTAMP NULL ,
  `ACTIVE` TINYINT(4)  NULL DEFAULT 0 ,
   `CONFIRM_DATE` TIMESTAMP NULL ,
  `CONFIRM_TEXT` VARCHAR(100) NULL ,
  PRIMARY KEY (`USERS_ID`) ,
  UNIQUE INDEX `USERS_ID_UNIQUE` (`USERS_ID` ASC) )
ENGINE = MyISAM
DEFAULT CHARACTER SET = utf8
*/

?>