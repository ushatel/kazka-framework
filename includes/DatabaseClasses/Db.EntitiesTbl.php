<?php

  if (@preg_match("/includes/i", $_SERVER['PHP_SELF'])) 
    die ("You can't access this file directly...");

	include_once("includes/DatabaseClasses/Db.CommonTable.php");
	include_once("includes/DatabaseClasses/Db.IDataConnector.php");

	/**
	 * The Package collects the City related classes etc
	 *
	 * @package Entities.pkg
	 */
	
	/**
	 * class Entities
	 *
	 * The main class of the Entities. Used for the operating with the entities
	 * 
	 * @package Entities.pkg
	 */
	 
	class EntitiesTbl extends CommonTable implements IDataConnector 
	{
		public $tableName = "`build`.`ENTITIES`";

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
		
		public function ParseResult($result = NULL)
		{
			if($result != NULL)
			{
				$this->dataRow = mysql_fetch_array($result);
			}
			
			$this->ParseDataRow();
			
			return $this->dataRow;
		}
						
		public function LoadEntities()
		{
			$result = NULL;
			
			$query = "SELECT * FROM <%prefix%>".$this->tableName."";
			$result = $this->databaseClass->SqlQuery($query);
			
			return $result;
		}
	}

/*
CREATE TABLE `ENTITIES` (
  `ENTITIES_ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `GUID` varchar(36) DEFAULT 'UUID()',
  `NAME` varchar(50) DEFAULT NULL,
  `CODE` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`ENTITIES_ID`),
  UNIQUE KEY `ENTITIES_ID_UNIQUE` (`ENTITIES_ID`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COMMENT='Сущності, що використовуються в системі'$$
*/
?>