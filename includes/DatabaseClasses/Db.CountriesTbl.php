<?php

  if (@preg_match("/includes/i", $_SERVER['PHP_SELF'])) 
    die ("You can't access this file directly...");

	include_once("includes/DatabaseClasses/Db.CommonTable.php");
	include_once("includes/DatabaseClasses/Db.IDataConnector.php");

	/**
	 * The Package collects the Countries related classes etc
	 *
	 * @package Countries.pkg
	 */
	
	/**
	 * class CountriesTbl
	 *
	 * The main class of the Countries. Used for the operating with the users
	 * 
	 * @package Countries.pkg
	 */
	class CountriesTbl extends CommonTable implements IDataConnector
	{
		public $tableName = "`build`.`COUNTRIES`";
		
		public function __construct()
		{
			$this->databaseClass = new Database();
				
			$this->databaseClass->SqlConnectCredentials($this->commonCredentials["host"], 
				$this->commonCredentials["username"], $this->commonCredentials["pass"], 
				$this->commonCredentials["dbname"], $this->commonCredentials["prefix"]);
					
			parent::__construct();
		}
		
		/**
		 * Повертає зручний масив задля select-option країн зі списку
		 */
		public function LoadCountriesList()
		{
			$result = $this->databaseClass->SqlQuery("SELECT `COUNTRIES_ID`, `NAME` AS `Title`, `COUNTRY_CODE` AS `Value` FROM <%prefix%>`COUNTRIES` WHERE `GROUPS_ID` = '0' ORDER BY `COUNTRY_CODE` IN ('UA', 'RU', 'PL', 'GB', 'NL') DESC , `ORD`");

			return $result;
		}
		
		/**
		 * Повертає країну за ідентифікатором
		 */
		public function LoadCountryById()
		{
			$this->isLoaded = false;

			$query = "SELECT * FROM <%prefix%>`COUNTRIES` WHERE `COUNTRIES_ID` ='".(float)$this->dataRow["COUNTRIES_ID"]."' LIMIT 1";
			$result = $this->databaseClass->SqlQuery($query);

			if(mysql_num_rows($result))
			{
				$this->dataRow = NULL;
				$this->dataRow = mysql_fetch_array($result);
				$this->isLoaded = true;
			}
			
			return $this->isLoaded;
		}
		
		public function GetDataRow()
		{
			return NULL;
		}
	}

?>
