<?php 
		
  if (@preg_match("/includes/i", $_SERVER['PHP_SELF'])) 
    die ("You can't access this file directly...");

  include_once("includes/DatabaseClasses/Db.CommonClass.php");    
  include_once("includes/DatabaseClasses/Db.CitiesTbl.php");
  include_once("includes/DatabaseClasses/Db.CountriesTbl.php");
  include_once("includes/DatabaseClasses/Db.CompaniesTbl.php");

/**
 * The Package collects the company related classes etc
 *
 * @package Company.pkg
 */

/**
 * class Companies
 *
 * The main class of the Companies. Used for the operating with the companies 
 * 
 * @package Companies.pkg
 */
class Companies extends CommonClass
{
	public $companiesTblObj = NULL;
	public $countriesTblObj = NULL;
	public $cityTblObj = NULL;

	public $updated_login = 0;
	public $companies_id = 0;	
 	public $cities_id = 0;
	public $countries_id = 0;
	public $local_lang_id = 0;
	public $name = "";
	public $unique_name_identifier = "";
	public $city_name = "";
	public $users_id = 0;
	public $country_name = "";
	public $country_name_full = "";
	public $edrpou_taxpnum = "";
	public $mfo = "";
	public $swift = "";
	public $eori_code = "";
	public $iban = 0;
	public $account = 0;
	public $isin = 0;
	public $address = "";
	public $physical_address = "";
	public $comment = "";
	public $zip_code = "";
	public $vat_code = "";
	public $phone_main = "";
	public $phone_mob = "";
	
	public $common_www = "";
	public $common_email = "";
	
	function __construct()
	{
		$this->companiesTblObj = new CompaniesTbl();
		$this->countriesTblObj = new CountriesTbl();
		$this->citiesTblObj = new CitiesTbl();
	}
	
	/**
	 * Перевірка унікального ідентифікатору
	 */
	public function ValidateUniqueIdentifier($unique_identifier = '')
	{
		$result = true; // not unique
			
		if(strlen($this->unique_name_identifier) < 1 && strlen($unique_identifier) > 0)
		{
			$this->unique_name_identifier = $unique_identifier;
		}
		elseif(strlen($this->unique_name_identifier) < 1)
		{
			return false;
		}
		
		$this->ParseFieldsToDataRow();
		$this->companiesTblObj->dataRow = $this->dataRow;
		
		$result = $this->companiesTblObj->ValidateUniqueIdentifier();
		if(!$result)
		{
			$this->isLoaded = false;
			$this->dataRow = NULL;
		}
		else
		{
			$this->isLoaded = true;
			$this->dataRow = $this->companiesTblObj->dataRow;

			$this->ParseDataRow();
		}
		
		return $result; // isLoaded != isUnique
	}
	
	public function RowsCount()
	{
		$count = 0;
		
		$count = $this->companiesTblObj->RowsCount();
		
		return $count;
	}
	
	public function LoadCompanies($limit = 0, $offset = 0, $name = "", $order_by = " `NAME` ")
	{
		$result = NULL;
		
		$this->ParseFieldsToDataRow();
		$this->companiesTblObj->dataRow = $this->dataRow;
		
		$result = $this->companiesTblObj->LoadCompanies($limit, $offset, $name, $order_by);
		
		return $result;
	}
	
	public function LoadCountryName()
	{
		$result = NULL;
		
		if($this->countries_id > 0)
		{
			$this->ParseFieldsToDataRow();
		
			$result = $this->countriesTblObj->LoadCountryById();

			if($result != NULL)
			{
				$this->country_name_full = $this->countriesTblObj->dataRow['NAME'];
			}
		}
		
		return $this->country_name_full;
	}
	
	public function GetCompanyById()
	{
		$result = NULL; 
		
		$this->ParseFieldsToDataRow();
		$this->companiesTblObj->dataRow = $this->dataRow;
		
		$result = $this->companiesTblObj->GetCompanyById();
		if(!$result)
		{
			$this->dataRow = NULL;
			$this->isLoaded = false;
		}
		else
		{
			$this->isLoaded = true;
			$this->dataRow = $this->companiesTblObj->dataRow;
			$this->ParseDataRow();
		}
		
		return $result;
	}
	
	public function DeleteCompany()
	{
		$result = NULL;
		
		$this->ParseFieldsToDataRow();
		$result = $this->companiesTblObj->DeleteCompany();
		
		return $result;
	}
		
	/**
	 * Створення нової компанії
	 */
	public function CreateCompany()
	{
		$newId = 0;
		
		// Validate City and Create new if not exists
		$this->citiesTblObj->dataRow['COUNTRIES_ID'] = $this->countries_id;
		$this->citiesTblObj->dataRow['NAME'] = $this->city_name;

		$this->citiesTblObj->CreateCity(); // завжди вірно!
		$this->citiesTblObj->cities_id = $this->citiesTblObj->dataRow['CITIES_ID'];

		$this->ParseFieldsToDataRow();
		$this->companiesTblObj->dataRow = $this->dataRow;		
		$newId = $this->companiesTblObj->CreateCompany();
		
		if($newId > 0)
		{
			$this->companies_id = $newId;
		}
		else
		{
			trigger_error(Logger::LOG_EVENT_CONST." could not create the new company. ".print_r($this->dataRow, true), E_USER_WARNING);
		}
		
		return $this->companies_id;
	}
	
	public function SaveCompany()
	{
		$newId = 0;
		
		// Validate City and Create new if not exists
		$this->citiesTblObj->dataRow['COUNTRIES_ID'] = $this->countries_id;
		$this->citiesTblObj->dataRow['NAME'] = $this->city_name;

		$this->citiesTblObj->CreateCity(); // завжди вірно!
		$this->citiesTblObj->cities_id = $this->citiesTblObj->dataRow['CITIES_ID'];

		$this->ParseFieldsToDataRow();
		$this->companiesTblObj->dataRow = $this->dataRow;
		
		$newId = $this->companiesTblObj->SaveCompany();
		
		if($newId > 0) 
		{
			$this->companies_id = $newId;
		}
		
		return $this->companies_id;
	}
	
	private function ParseDataRow() 
	{
		$this->updated_login = (float)$this->dataRow['UPDATED_LOGIN'];
		$this->companies_id = (float)$this->dataRow['COMPANIES_ID'];
		$this->cities_id = (float)$this->dataRow['CITIES_ID'];
		$this->countries_id = (float)$this->dataRow['COUNTRIES_ID'];
		$this->local_lang_id = (float)$this->dataRow['LOCAL_LANG_ID'];
		$this->name = $this->dataRow['NAME'];
		$this->unique_name_identifier = $this->dataRow['UNIQUE_NAME_IDENTIFIER'];
		$this->city_name = $this->dataRow['CITY_NAME'];
		$this->country_name = $this->dataRow['COUNTRY_NAME_LOCAL'];
		$this->country_name_full = $this->countriesTblObj->dataRow['COUNTRY_NAME'];
		$this->edrpou_taxpnum = $this->dataRow['EDRPOU_TAXPNUM'];
		$this->mfo = $this->dataRow['BANK_MFO'];
		$this->swift = $this->dataRow['BANK_SWIFT_BIC'];
		$this->eori_code = $this->dataRow['EORI_CODE'];
		$this->iban = $this->dataRow['IBAN'];
		$this->account = $this->dataRow['ACCOUNT_NUMBER_UA'];
		$this->isin = $this->dataRow['ISIN'];
		$this->address = $this->dataRow['LAW_ADDRESS'];
		$this->physical_address = $this->dataRow['PHYSICAL_ADDRESS'];
		$this->comment = $this->dataRow['COMMENT'];
		$this->zip_code = $this->dataRow['ZIP_CODE'];
		$this->vat_code = $this->dataRow['VAT_CODE'];
		$this->common_www = $this->dataRow['COMMON_WWW'];
		$this->common_email = $this->dataRow['COMMON_EMAIL'];
		
		$this->phone_main = $this->dataRow['PHONE_MAIN'];
		$this->phone_mob = $this->dataRow['PHONE_MOB'];
	}
	
	private function ParseFieldsToDataRow()
	{
		$this->dataRow['UPDATED_LOGIN'] = (float)$this->updated_login;
		$this->dataRow['COMPANIES_ID'] = (float)$this->companies_id;
		$this->dataRow['CITIES_ID'] = (float)$this->cities_id;
		$this->dataRow['COUNTRIES_ID'] = (float)$this->countries_id;
		$this->countriesTblObj->dataRow['COUNTRIES_ID'] = (float)$this->countries_id;
		//$this->countriesTblObj->dataRow['NAME'] = $this->country_name_full;
		$this->dataRow['LOCAL_LANG_ID'] = (float)$this->local_lang_id;
		$this->dataRow['NAME'] = substr($this->name, 0, 200);
		$this->dataRow['UNIQUE_NAME_IDENTIFIER'] = substr($this->unique_name_identifier, 0, 50);
		$this->dataRow['CITY_NAME'] = substr($this->city_name, 0, 200);
		$this->dataRow['COUNTRY_NAME_LOCAL'] = substr($this->country_name, 0, 200);
		$this->dataRow['EDRPOU_TAXPNUM'] = (float)$this->edrpou_taxpnum;
		$this->dataRow['BANK_MFO'] = (int)$this->mfo;
		$this->dataRow['BANK_SWIFT_BIC'] = substr($this->swift, 0, 50);
		$this->dataRow['EORI_CODE'] = substr($this->eori_code, 0, 14);
		$this->dataRow['IBAN'] = substr($this->iban, 0, 30);
		$this->dataRow['ACCOUNT_NUMBER_UA'] = (float)$this->account;
		$this->dataRow['ISIN'] = substr($this->isin, 0, 12);
		$this->dataRow['LAW_ADDRESS'] = substr($this->address, 0, 200);
		$this->dataRow['PHYSICAL_ADDRESS'] = substr($this->physical_address, 0, 200);
		$this->dataRow['COMMENT'] = substr($this->comment, 0, 10000);
		$this->dataRow['ZIP_CODE'] = substr($this->zip_code, 0, 10);
		$this->dataRow['VAT_CODE'] = substr($this->vat_code, 0, 20);
		$this->dataRow['COMMON_WWW'] = substr($this->common_www, 0, 200);
		$this->dataRow['COMMON_EMAIL'] = substr($this->common_email, 0, 200);

		$this->dataRow['PHONE_MAIN'] = substr($this->phone_main, 0, 100);
		$this->dataRow['PHONE_MOB'] = substr($this->phone_mob, 0, 100);
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