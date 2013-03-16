<?php

	if (@preg_match("/includes/i", $_SERVER['PHP_SELF'])) 
    	die ("You can't access this file directly...");

	include_once("includes/DatabaseClasses/Db.CommonTable.php");
	include_once("includes/DatabaseClasses/Db.IDataConnector.php");

	/**
	 * The Package collects the Projects related classes etc
	 *
	 * @package Services.pkg
	 */
	
	/**
	 * class ServicesTbl
	 *
	 * The main class of the ServicesTbl. Used for the operating with the table of services
	 * 
	 * @package Services.pkg
	 */
	 
	class ServicesTbl extends CommonTable implements IDataConnector 
	{
		public $tableName = "`build`.`SERVICES`";
		//public $tableGroupsName = "`build`.`MATERIALS_GROUPS`";

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

		public function LoadServices($name = "", $limit = 0, $offset = 0, $order_by = '`UPDATED_TIME`')
		{
			$result = NULL;
			
			$limit_text = "";
			if($limit > 0 || $offset > 0)
			{
				$limit_text = " LIMIT ".(float)$offset;
				
				if($limit > 0)
				{
					$limit_text .= ",".(float)$limit;
				}
			}

			$name_text = "";
			if(strlen($name) > 0)
			{
				$name_text = "WHERE `TITLE` like '%".StaticDatabase::CleanupTheField($name)."%'";
			}

			/*$materials_inner_join = '';
			if(strlen($this->dataRow['MATERIALS_GROUPS_IDENTIFIER']) > 0)
			{
				$materials_inner_join = " INNER JOIN ".$this->tableGroupsName." mg ON mg.`UNIQUE_NAME_IDENTIFIER` = '".$this->dataRow['MATERIALS_GROUPS_IDENTIFIER']."' AND mg.`MATERIALS_GROUPS_ID` = mt.`MATERIALS_GROUPS_ID` ";
			}

			if((float)$this->dataRow['MATERIALS_GROUPS_ID'] > 0)
			{
				$materials_inner_join = " INNER JOIN ".$this->tableGroupsName." mg ON mg.`MATERIALS_GROUPS_ID` = '".(float)$this->dataRow['MATERIALS_GROUPS_ID']."' ";
			}
			*/

			if((float)$this->dataRow['VENDOR_COMPANIES_ID'] > 0)
			{
				if(strlen($name_text) > 0)
				{
					$name_text .= " OR ";
				}
				else
				{
					$name_text .= " WHERE ";
				}

				$name_text .= "`VENDOR_COMPANIES_ID`='".$this->dataRow['VENDOR_COMPANIES_ID']."'";
			}

			$query = "SELECT mt.* FROM <%prefix%>".$this->tableName." mt ".$materials_inner_join." ".$name_text." ORDER BY ".$order_by." ".$limit_text;

			$result = $this->databaseClass->SqlQuery($query);

			return $result;
		}

		/**
		 * Check if materials code is unique
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
		
		public function RowsCount()
		{
			$result = NULL;

			$query = "SELECT COUNT(`SERVICES_ID`) FROM ".$this->tableName;//." WHERE `MATERIALS_ID` = '".(float)$this->dataRow['MATERIALS_ID']."' ";
			$result = $this->databaseClass->SqlQuery($query);

			if($result != NULL)
			{
				$res = mysql_fetch_array($result);
				$result = $res[0];
			}
			
			return $result;
		}
		
		public function DeleteServices()
		{
			$result = NULL;
			
			if($this->dataRow['SERVICES_ID'] > 0)
			{
				$query = "DELETE FROM <%prefix%>".$this->tableName." WHERE `SERVICES_ID`='".(float)$this->dataRow['SERVICES_ID']."' LIMIT 1";
				
				$result = $this->databaseClass->SqlQuery($query);
			}

			return $result;
		}
		
		public function GetServicesById()
		{
			$result = NULL;
			
			if($this->dataRow['SERVICES_ID'] > 0)
			{
				$query = "SELECT * FROM <%prefix%>".$this->tableName." WHERE `SERVICES_ID`='".(float)$this->dataRow['SERVICES_ID']."' LIMIT 1";

				$result = $this->databaseClass->SqlQuery($query);

				$this->dataRow = NULL;
				if(mysql_num_rows($result) == 1)
				{
					$result = $this->dataRow = mysql_fetch_array($result);
				}
			}

			return $result;
		}
		
		/**
		 * Додати послугу
		 */
		public function CreateService()
		{
			$result = NULL;

			$insert_id = 0;

			$query = "INSERT INTO <%prefix%>".$this->tableName." ".
					 " (`GUID`, `CREATED_TIME`, `UPDATED_TIME`, `UPDATED_LOGIN`, `COMPANIES_ID`, `BIDASK`, `HANDS_UP`, `HANDS_DOWN`, `PHONE`, `PRICE`, ".
					 " `TITLE`, `UNIQUE_NAME_IDENTIFIER`, `EMAIL`, `AGENT`,".
					 " `IP`, `LOCATION`, `DESCRIPTION` ) VALUES ".
					 " (".$this->databaseClass->GuidColumn().", ".$this->createdTimeValue.", ".$this->updatedTimeValue.", '".(float)$this->updatedLogin."', ".
					 "  '".(float)$this->dataRow['COMPANIES_ID']."', '".(int)$this->dataRow['BIDASK']."', '".(int)$this->dataRow['HANDS_UP']."', '".(int)$this->dataRow['HANDS_DOWN']."', '".substr($this->dataRow['PHONE'], 0, 100)."', '".(float)$this->dataRow['PRICE']."', '".substr($this->dataRow['TITLE'], 0, 100)."', ".
					 "  '".$this->dataRow['UNIQUE_NAME_IDENTIFIER']."', '".$this->dataRow['EMAIL']."', '".$this->dataRow['AGENT']."', '".$this->dataRow['IP']."', '".$this->dataRow['LOCATION']."', '".substr($this->dataRow['DESCRIPTION'], 0, 5000)."')";

			$result = $this->databaseClass->SqlQuery($query);

			$insert_id = mysql_insert_id();
			
			return $this->dataRow['SERVICES_ID'] = $insert_id;
		}

		/**
		 * Збереження послуги
		 */
		public function SaveService()
		{
			$result = NULL;
			
			$query = "UPDATE <%prefix%>".$this->tableName." SET ".
					 " `UPDATED_TIME`=".$this->updatedTimeValue.", `UPDATED_LOGIN`='".(float)$this->updatedLogin."', ".
					 " `COMPANIES_ID`='".(float)$this->dataRow['COMPANIES_ID']."', `BIDASK`='".(int)$this->dataRow['BIDASK']."', `HANDS_UP`='".$this->dataRow['HANDS_UP']."', `HANDS_DOWN`='".(int)$this->dataRow['HANDS_DOWN']."', `PHONE`='".(int)$this->dataRow['PHONE']."', `PRICE`='".(float)$this->dataRow['PRICE']."', ".
					 " `TITLE`='".$this->dataRow['TITLE']."', `UNIQUE_NAME_IDENTIFIER`='".$this->dataRow['UNIQUE_NAME_IDENTIFIER']."', `EMAIL`='".substr($this->dataRow['EMAIL'], 0, 200)."', `AGENT`='".substr($this->dataRow['AGENT'], 0, 500)."',".
					 " `IP`='".substr($this->dataRow['IP'], 0, 100)."', `LOCATION`='".substr($this->dataRow['LOCATION'], 0, 150)."', `DESCRIPTION`='".substr($this->dataRow['DESCRIPTION'], 0, 5000)."' WHERE `SERVICES_ID`='".(float)$this->dataRow['SERVICES_ID']."' LIMIT 1";

			$result = $this->databaseClass->SqlQuery($query);

			if($result != NULL)
			{
				$result = true;	
			}
			else
			{
				$result = false;
			}

			return $result;
		}
	}

/*

CREATE TABLE `SERVICES` (
  `SERVICES_ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `GUID` varchar(36) DEFAULT NULL,
  `CREATED_TIME` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `UPDATED_TIME` timestamp NULL DEFAULT NULL,
  `UPDATED_LOGIN` bigint(20) DEFAULT NULL,
  `COMPANIES_ID` bigint(20) DEFAULT NULL,
  `HANDS_UP` bigint(20) DEFAULT NULL,
  `HANDS_DOWN` bigint(20) DEFAULT NULL,
  `BIDASK` int DEFAULT 0,
  `PHONE` varchar(45) DEFAULT NULL,
  `PRICE` double DEFAULT NULL,
  `TITLE` varchar(200) DEFAULT NULL,
  `UNIQUE_NAME_IDENTIFIER` varchar(100) DEFAULT NULL,
  `EMAIL` varchar(200) DEFAULT NULL,
  `AGENT` varchar(100) DEFAULT NULL,
  `IP` varchar(45) DEFAULT NULL,
  `LOCATION` text,
  `DESCRIPTION` text,
  PRIMARY KEY (`SERVICES_ID`),
  UNIQUE KEY `SERVICES_ID_UNIQUE` (`SERVICES_ID`),
  UNIQUE KEY `UNIQUE_NAME_IDENTIFIER_UNIQUE` (`UNIQUE_NAME_IDENTIFIER`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Таблиця послуг'$$

*/
?>