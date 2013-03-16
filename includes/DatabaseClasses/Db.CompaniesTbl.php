<?php


  if (@preg_match("/includes/i", $_SERVER['PHP_SELF'])) 
    die ("You can't access this file directly...");

	include_once("includes/DatabaseClasses/Db.CommonTable.php");
	include_once("includes/DatabaseClasses/Db.IDataConnector.php");

	/**
	 * The Package collects the Users related classes etc
	 *
	 * @package Companies.pkg
	 */
	
	/**
	 * class Companies
	 *
	 * The main class of the Companies. Used for the operating with the users
	 * 
	 * @package Companies.pkg
	 */
	 
	class CompaniesTbl extends CommonTable implements IDataConnector 
	{
		public $tableName = "`COMPANIES`";

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
		 * Завантажити компанії
		 */
		public function LoadCompaniesByIds($companiesIds)
		{
			$result = NULL;
			
			$queryIds = "";
	
			if(is_array($companiesIds))
			{
				$queryIds = " WHERE `COMPANIES_ID` IN (";
				$first = true;
				
				foreach($companiesIds as $value)
				{					
					if(!$first)
						$queryIds .= ",";
					else
						$first = false;

					$queryIds .= $value;
				}

				$queryIds .= ")";				
			}
			elseif(mysql_num_rows($companiesIds) > 0)
			{
				$queryIds = " WHERE `COMPANIES_ID` IN (";
				$first = true;

				while($row = mysql_fetch_array($companiesIds))
				{
					if(!$first)
						$queryIds .= ",";
					else
						$first = false;

					$queryIds .= $row['COMPANIES_ID'];
				}

				$queryIds .= ")";
			}
			elseif(is_numeric($companiesIds))
			{
				$queryIds = " WHERE `COMPANIES_ID`='".$companiesIds."' ";
			}

			if($queryIds != "")
			{
				$query = "SELECT * FROM <%prefix%>".$this->tableName." ".$queryIds;
				$result = $this->databaseClass->SqlQuery($query);
			}
			
			return $result;
		}

		public function RowsCount()
		{
			$result = NULL;
			$count = 0;
			
			$query = " SELECT COUNT(`COMPANIES_ID`) FROM ".$this->tableName." ";
			$result = $this->databaseClass->SqlQuery($query);
			
			if($result != NULL)
			{
				$result = mysql_fetch_array($result);
				$count = $result[0];
			}
			
			return $count;
		}
		
		public function DeleteCompany()
		{
			$result = NULL;
			
			if((float)$this->dataRow['COMPANIES_ID'] > 0)
			{
				$query = "DELETE FROM <%prefix%>".$this->tableName." WHERE `COMPANIES_ID` = '".(float)$this->dataRow['COMPANIES_ID']."' LIMIT 1 ";
				$result = $this->databaseClass->SqlQuery($query);
			}
			
			return $result;
		}
		
		/**
		 * Завантажити перелік компаній
		 */
		public function LoadCompanies($limit = 0, $offset = 0, $name = "", $order_by = ' `NAME`')
		{
			$result = NULL;

			$limit_text = "";
			if($limit > 0 || $offset > 0)
			{
				$limit_text = " LIMIT ".$offset;

				if($limit > 0)
				{
					$limit_text .= ",".(float)$limit;
				}
			}
			
			$name_text = "";
			if(strlen($name) > 0)
			{
				$name_text = "WHERE `NAME` like '%".$name."%'";
			}
			
			$query = "SELECT * FROM <%prefix%>".$this->tableName." ORDER BY ".$order_by." ".$name_text." ".$limit_text;
			$result = $this->databaseClass->SqlQuery($query);

			return $result;
		}
		
		/**
		 * Перевіряє чи вже є вказана компанія у базі даних
		 */
		public function ValidateCompanyName()
		{
			$this->isLoaded = false;

			$query = "SELECT * FROM <%prefix%>".$this->tableName." WHERE `NAME` like '%".$this->dataRow['NAME']."%' LIMIT 1";

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
		 * Перевірка чи вже існує компанія із вказаним унікальним ідентифікатором
		 */
		public function ValidateUniqueIdentifier()
		{
			$this->isLoaded = false;
			
			$query = "SELECT * FROM <%prefix%>".$this->tableName." WHERE `UNIQUE_NAME_IDENTIFIER` like '%".$this->dataRow['UNIQUE_NAME_IDENTIFIER']."%' LIMIT 1";

			$result = $this->databaseClass->SqlQuery($query);
			if($result != NULL && mysql_num_rows($result) == 1)
			{
				$this->dataRow = NULL;
				$this->dataRow = mysql_fetch_array($result);
				$this->isLoaded = true;
			}
			
			return $this->isLoaded;
		}
		
		public function GetCompanyById($needClear = true)
		{
			$this->isLoaded = false;
			
			$query = "SELECT * FROM <%prefix%>".$this->tableName." WHERE `COMPANIES_ID`='".(float)$this->dataRow['COMPANIES_ID']."' LIMIT 1";

			$result = $this->databaseClass->SqlQuery($query);
			if(mysql_num_rows($result) == 1)
			{
				if($needClear)
				{
					$this->dataRow = mysql_fetch_array($result);
				}
				$this->isLoaded = true;
			}
			else 
			{
				if($needClear)
				{
					$this->dataRow = NULL;
				}
			}
			
			return $this->isLoaded;
		}
				
		/**
		 * Перевірка на унікальність
		 */
		public function ValidateCompanyNameAndIdentifier()
		{
			$this->isLoaded = false;
			
			$query = "SELECT * FROM <%prefix%>".$this->tableName." WHERE `UNIQUE_NAME_IDENTIFIER` like '".$this->dataRow['UNIQUE_NAME_IDENTIFIER']."' OR `NAME` like '".$this->dataRow['NAME']."' LIMIT 1";

			$result = $this->databaseClass->SqlQuery($query);
			if(mysql_num_rows($result) == 1)
			{
				/*$this->dataRow = NULL;
				$this->dataRow = mysql_fetch_array($result);
				*/$this->isLoaded = true;
			}
			
			return $this->isLoaded;
		}
		
		/**
		 * Створення нової компанії
		 */
		public function CreateCompany($needValidate = true)
		{
			$insert_id = 0;

			// Check company name is exists
			if(!$needValidate || $needValidate && !$this->ValidateCompanyNameAndIdentifier())
			{
				$cityId = 0;
				$query = "INSERT INTO <%prefix%>".$this->tableName." ".
						 " (`GUID`, `CREATED_TIME`, `UPDATED_TIME`, ".
						 "  `UPDATED_LOGIN`, `NAME`, `UNIQUE_NAME_IDENTIFIER`, `CITY_NAME`, `CITIES_ID`, `COUNTRIES_ID`, `LOCAL_LANG_ID`, `COUNTRY_NAME_LOCAL`, ".
						 "  `EDRPOU_TAXPNUM`, `BANK_MFO`, `BANK_SWIFT_BIC`, `EORI_CODE`, `IBAN`, `ACCOUNT_NUMBER_UA`,".
						 "  `ISIN`, `LAW_ADDRESS`, `PHYSICAL_ADDRESS`, `COMMENT`, `ZIP_CODE`, `VAT_CODE`, `COMMON_WWW`, `COMMON_EMAIL`, `PHONE_MAIN`, `PHONE_MOB`) ".
						 " VALUES ".
						 " ( ".$this->databaseClass->GuidColumn().", ".$this->createdTimeValue.", ".$this->updatedTimeValue.", ".
						 "   '".(float)Session::GetUserId()."', '".$this->dataRow['NAME']."', '".$this->dataRow['UNIQUE_NAME_IDENTIFIER']."', '".$this->dataRow['CITY_NAME']."', '".(int)$cityId."', '".$this->dataRow['COUNTRIES_ID']."', '".$this->dataRow['LOCAL_LANG_ID']."', ".
						 "   '".$this->dataRow['COUNTRY_NAME_LOCAL']."', '".$this->dataRow['EDRPOU_TAXPNUM']."', '".$this->dataRow['BANK_MFO']."', ".
						 "   '".$this->dataRow['BANK_SWIFT_BIC']."', '".$this->dataRow['EORI_CODE']."', '".$this->dataRow['IBAN']."', '".$this->dataRow['ACCOUNT_NUMBER_UA']."', ".
						 "   '".$this->dataRow['ISIN']."', '".$this->dataRow['LAW_ADDRESS']."', '".$this->dataRow['PHYSICAL_ADDRESS']."', '".$this->dataRow['COMMENT']."', ".
						 "	 '".$this->dataRow['ZIP_CODE']."', '".$this->dataRow['VAT_CODE']."', '".$this->dataRow['COMMON_WWW']."', '".$this->dataRow['COMMON_EMAIL']."', '".$this->dataRow['PHONE_MAIN']."', '".$this->dataRow['PHONE_MOB']."' ) ";

				$result = $this->databaseClass->SqlQuery($query);
				$insert_id = mysql_insert_id();
			}
			else 
			{
				trigger_error(Logger::LOG_EVENT_CONST." Could not create the company with the dublicated name='".$this->dataRow['NAME']."'", E_USER_WARNING);
			}
			
			return $insert_id;
		}
		
		public function SaveCompany()
		{
			$result = false;
			
			if($this->GetCompanyById(false) != NULL)
			{
				$query = "  UPDATE <%prefix%>".$this->tableName." SET ".
						 " `UPDATED_TIME`=".$this->updatedTimeValue.", `UPDATED_LOGIN`='".(float)Session::GetUserId()."', `NAME`='".$this->dataRow['NAME']."', `UNIQUE_NAME_IDENTIFIER`='".$this->dataRow['UNIQUE_NAME_IDENTIFIER']."', `CITY_NAME`='".$this->dataRow['CITY_NAME']."', `CITIES_ID`='".(float)$this->dataRow['CITIES_ID']."', ".
						 " `COUNTRIES_ID`='".$this->dataRow['COUNTRIES_ID']."', `LOCAL_LANG_ID`='".$this->dataRow['LOCAL_LANG_ID']."', ".
						 " `COUNTRY_NAME_LOCAL`='".$this->dataRow['COUNTRY_NAME_LOCAL']."', `EDRPOU_TAXPNUM`='".$this->dataRow['EDRPOU_TAXPNUM']."', `BANK_MFO`='".$this->dataRow['BANK_MFO']."', `BANK_SWIFT_BIC`='".$this->dataRow['BANK_SWIFT_BIC']."', `EORI_CODE`='".$this->dataRow['EORI_CODE']."', `IBAN`='".$this->dataRow['IBAN']."', `ACCOUNT_NUMBER_UA`='".$this->dataRow['ACCOUNT_NUMBER_UA']."', `ISIN`='".$this->dataRow['ISIN']."', `LAW_ADDRESS`='".$this->dataRow['LAW_ADDRESS']."', `PHYSICAL_ADDRESS`='".$this->dataRow['PHYSICAL_ADDRESS']."', `COMMENT`='".$this->dataRow['COMMENT']."', `ZIP_CODE`='".$this->dataRow['ZIP_CODE']."', `VAT_CODE`='".$this->dataRow['VAT_CODE']."', `COMMON_WWW`='".$this->dataRow['COMMON_WWW']."', `COMMON_EMAIL`='".$this->dataRow['COMMON_EMAIL']."', ".
						 " `PHONE_MAIN`='".$this->dataRow['PHONE_MAIN']."', `PHONE_MOB`='".$this->dataRow['PHONE_MOB']."' ".
						 "  WHERE `COMPANIES_ID`='".(float)$this->dataRow['COMPANIES_ID']."' LIMIT 1 ";
				$result = $this->databaseClass->SqlQuery($query);
			}
			else
			{
				$result = $this->CreateCompany(false);
			}
			
			return $result;
		}
	}

/*
CREATE  TABLE IF NOT EXISTS `INNODB_TABLES`.`COMPANIES` (
  `COMPANIES_ID` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `GUID` VARCHAR(36) NULL ,
  `CREATED_TIME` TIMESTAMP NULL ,
  `UPDATED_TIME` TIMESTAMP NULL ,
  `UPDATED_LOGIN` BIGINT UNSIGNED NULL ,
  `CITIES_ID` BIGINT UNSIGNED NULL ,
  `COUNTRIES_ID` BIGINT UNSIGNED NULL ,
  `LOCAL_LANG_ID` BIGINT NULL ,
  `NAME` VARCHAR(200) NULL ,
  `CITY_NAME` VARCHAR(200) NULL ,
  `COUNTRY_NAME_LOCAL` VARCHAR(200) NULL ,
  `EDRPOU_TAXPNUM` BIGINT NULL ,
  `BANK_MFO` BIGINT NULL ,
  `BANK_SWIFT_BIC` VARCHAR(50) NULL COMMENT 'ISO 13616-compliant national IBAN formats' ,
  `EORI_CODE` VARCHAR(14) NULL ,
  `IBAN` VARCHAR(30) NULL COMMENT 'ISO 13616 IBAN Standard\nCountry_code' ,
  `ACCOUNT_NUMBER_UA` DECIMAL NULL COMMENT 'номер рахунку\n21 розряд' ,
  `ISIN` VARCHAR(12) NULL ,
  `LAW_ADDRESS` VARCHAR(200) NULL COMMENT 'Юридична адреса' ,
  `PHYSICAL_ADDRESS` VARCHAR(200) NULL COMMENT 'Адреса офісу' ,
  `COMMENT` VARCHAR(1000) NULL ,
  `ZIP_CODE` VARCHAR(10) NULL ,
  `VAT_CODE` VARCHAR(20) NULL ,
  PRIMARY KEY (`COMPANIES_ID`) ,
  UNIQUE INDEX `COMPANIES_ID_UNIQUE` (`COMPANIES_ID` ASC) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = 'Список компаній'
*/
?>