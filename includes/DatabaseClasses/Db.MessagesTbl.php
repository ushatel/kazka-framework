<?php

	if (@preg_match("/includes/i", $_SERVER['PHP_SELF'])) 
    	die ("You can't access this file directly...");

	include_once("includes/DatabaseClasses/Db.CommonTable.php");
	include_once("includes/DatabaseClasses/Db.IDataConnector.php");

	/**
	 * The Package collects the Projects related classes etc
	 *
	 * @package Messages.pkg
	 */
	
	/**
	 * class MessagesTbl
	 *
	 * The main class of the MessagesTbl. Used for the operating with the table of messages
	 * 
	 * @package Messages.pkg
	 */
	 
	class MessagesTbl extends CommonTable implements IDataConnector 
	{
		public $tableName = "`build`.`USERS_MESSAGES`";

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

		public function LoadMessages($limit = 0, $offset = 0, $order_by = "`UPDATED_TIME`")
		{
			$result = NULL;

			$limit_text = "";

			if($limit > 0 || $offset > 0)
			{
				$limit_text = ",".(float)$offset;
			}

			$where_text = "";
			if((float)$row['USERS_MESSAGES_ID'] > 0 && (float)$row['SENDER_USERS_ID'] > 0 && (float)$row['RECEIVER_USERS_ID'] > 0)
			{
				$where_text = " WHERE `USERS_MESSAGES_ID`= '".$this->dataRow['USERS_MESSAGES_ID']."' AND `RECEIVER_USERS_ID`= '".$this->dataRow['RECEIVER_USERS_ID']."' ";
			}

			$query = "SELECT * FROM <%prefix%>".$this->tableName." ".$where_text." ".$limit_text;
			$result = $this->databaseClass->SqlQuery($query);

			return $result;
		}
		
		public function GetMessageById()
		{
			$result = NULL;
			
			if($this->dataRow['USERS_MESSAGES_ID'] > 0)
			{
				$query = "SELECT * FROM <%prefix%>".$this->tableName." WHERE `USERS_MESSAGES_ID`='".(float)$this->dataRow['USERS_MESSAGES_ID']."' LIMIT 1";
				
				$result = $this->databaseClass->SqlQuery($query);
				
				$this->dataRow = NULL;
				if(mysql_num_rows($result) == 1)
				{
					$result = $this->dataRow = mysql_fetch_array($result);
				}
			}
			
			return $result;
		}

		public function CreateMessage()
		{
			$result = NULL;

			$insert_id = 0;

			$query = "INSERT INTO <%prefix%>".$this->tableName." ".
				 	" (`GUID`, `CREATED_TIME`, `UPDATED_TIME`, `UPDATED_LOGIN`, `SENDER_USERS_ID`, `SENDER_USERS_NAME`, `SENDER_IP`, `RECEIVER_USERS_ID`, `RECEIVER_USERS_NAME`, `MESSAGE_TITLE`, `MESSAGE_BODY`, `UNREAD`) VALUES ".
					" (".$this->databaseClass->GuidColumn().", ".$this->createdTimeValue.", ".$this->updatedTimeValue.", '".(float)$this->updatedLogin."', '".(float)$this->dataRow['SENDER_USERS_ID']."', '".$this->dataRow['SENDER_USERS_NAME']."', '".$this->dataRow['SENDER_IP']."', '".(float)$this->dataRow['RECEIVER_USERS_ID']."', '".$this->dataRow['RECEIVERS_USERS_NAME']."', '".$this->dataRow['MESSAGE_TITLE']."', '".$this->dataRow['MESSAGE_BODY']."', '".(int)$this->dataRow['UNREAD']."') ";

			$result = $this->databaseClass->SqlQuery($query);

			$insert_id = mysql_insert_id();

			return $this->dataRow['USERS_MESSAGES_ID'] = $insert_id;
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
COMMENT = 'Статична таблиця. \nВ базовій версії буде заповнятись адміністратором\n'
*/
?>