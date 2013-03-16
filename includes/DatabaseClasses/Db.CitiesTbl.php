<?php

  if (@preg_match("/includes/i", $_SERVER['PHP_SELF'])) 
    die ("You can't access this file directly...");

	include_once("includes/DatabaseClasses/Db.CommonTable.php");
	include_once("includes/DatabaseClasses/Db.IDataConnector.php");

	/**
	 * The Package collects the City related classes etc
	 *
	 * @package Cities.pkg
	 */
	
	/**
	 * class Cities
	 *
	 * The main class of the Cities. Used for the operating with the cities
	 * 
	 * @package Cities.pkg
	 */
	 
	class CitiesTbl extends CommonTable implements IDataConnector 
	{
		public $tableName = "`build`.`CITIES`";

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
		 * Перевіряє чи існує в каталозі вказане місто
		 */
		public function ValidateCityNameInCountry()
		{
			$this->isLoaded = false;
			
			$query = "SELECT * FROM <%prefix%>".$this->tableName." WHERE `NAME`='".$this->dataRow['NAME']."' AND `COUNTRIES_ID`='".$this->dataRow['COUNTRIES_ID']."' LIMIT 1";
			$this->dataRow = NULL;
			
			$result = $this->databaseClass->SqlQuery($query);
			
			if(mysql_num_rows($result) == 1)
			{
				$this->isLoaded = true;
				$this->dataRow = mysql_fetch_array($result);
			}
			
			return $this->isLoaded;
		}
		
		public function CreateCity()
		{
			$this->isLoaded = false;

			$cityPage = $this->dataRow['NAME'];
			$countryId = $this->dataRow['COUNTRIES_ID'];
			
			if(!$this->ValidateCityNameInCountry())
			{
				// місто не знайдено. додаємо до бази
				$query = "INSERT INTO <%prefix%>".$this->tableName." ".
						 "(`GUID`, `COUNTRIES_ID`, `ORD`, `NAME`, `LANGS_ID`) VALUES ".
						 "(".$this->databaseClass->GuidColumn().", '".$countryId."', '0', '".$cityPage."', '0')";
				$result = $this->databaseClass->SqlQuery($query);
				
				$this->dataRow['CITIES_ID'] = mysql_insert_id();
				if($this->dataRow['CITIES_ID'] < 1)
				{
					trigger_error(Logger::LOG_EVENT_CONST." Could not insert the city. Query='".$query."'", E_USER_WARNING);
				}
				else 
				{
					$this->dataRow['NAME'] = $cityPage;
					$this->dataRow['COUNTRIES_ID'] = $countryId;
				}
			}
			
			// завжди буде результат
			return true;
		}
	}

/*
CREATE  TABLE IF NOT EXISTS `INNODB_TABLES`.`CITIES` (
  `CITIES_ID` BIGINT UNSIGNED NOT NULL ,
  `GUID` VARCHAR(36) NULL ,
  `COUNTRIES_ID` BIGINT UNSIGNED NULL ,
  `ORD` BIGINT UNSIGNED NULL ,
  `NAME` VARCHAR(50) NULL ,
  `LANGS_ID` BIGINT UNSIGNED NULL ,
  PRIMARY KEY (`CITIES_ID`) ,
  UNIQUE INDEX `CITY_ID_UNIQUE` (`CITIES_ID` ASC) ,
  INDEX `FK_COUNTRIES_ID` (`COUNTRIES_ID` ASC) ,
  CONSTRAINT `FK_COUNTRIES_ID`
    FOREIGN KEY (`COUNTRIES_ID` )
    REFERENCES `MYISAM_TABLES`.`COUNTRIES` (`COUNTRIES_ID` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = 'Таблиця міст'
*/
?>