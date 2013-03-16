<?php

  if (@preg_match("/includes/i", $_SERVER['PHP_SELF'])) 
    die ("You can't access this file directly...");

  include_once("includes/DatabaseClasses/Db.CommonClass.php");    
  include_once("includes/DatabaseClasses/Db.CountriesTbl.php");
  include_once("includes/DatabaseClasses/Db.ContractorsTbl.php");

/**
 * The Package collects the Users related classes etc
 *
 * @package Countries.pkg
 */

/**
 * class Countries
 *
 * The main class of the Countries. Used for the operating with the countries
 * 
 * @package Countries.pkg
 */
class Countries extends CommonClass
{
	public $country = NULL;
	
	public $name = "";
	public $name_eng = "";
	public $countries_id = 0;
	public $country_code = "";
	public $groups_id = 0;
	
	function __construct()
	{
		$this->country = new CountriesTbl();
	}
	
	public function GetCountriesList()
	{
		return $this->country->LoadCountriesList();
	}
	
	public function GetCountryById()
	{
		$this->ParseFieldsToDataRow();
		
		if($this->country->LoadCountryById())
		{
			$this->ParseDataRowToFields();
			return $this->country->countries_id;
		}
		else 
		{
			return 0;
		}	
	}
	
	private function ParseFieldsToDataRow()
	{
		$this->country->dataRow['NAME'] = substr($this->name, 0, 50);
		$this->country->dataRow['NAME_ENG'] = substr($this->name_eng, 0, 50);
		$this->country->dataRow['COUNTRIES_ID'] = (float)$this->countries_id;
		$this->country->dataRow['GROUPS_ID'] = (float)$this->groups_id;
		$this->country->dataRow['COUNTRY_CODE'] = substr($this->country_code, 0, 5);
	}
	
	private function ParseDataRowToFields()
	{
		$this->name = $this->country->dataRow['NAME'];
		$this->name_eng = $this->country->dataRow['NAME_ENG'];
		$this->countries_id = $this->country->dataRow['COUNTRIES_ID'];
		$this->groups_id = $this->country->dataRow['GROUPS_ID'];
		$this->country_code = $this->country->dataRow['COUNTRY_CODE'];
	}
}
/*
CREATE  TABLE IF NOT EXISTS `MYISAM_TABLES`.`COUNTRIES` (
  `COUNTRIES_ID` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `LANG_ID` BIGINT NULL ,
  `ORD` BIGINT NULL ,
  `GROUPS_ID` BIGINT UNSIGNED NULL DEFAULT 0 ,
  `NAME` VARCHAR(50) NULL ,
  `NAME_ENG` VARCHAR(50) NULL ,
  `COUNTRY_CODE` VARCHAR(5) NULL ,
  PRIMARY KEY (`COUNTRIES_ID`) ,
  UNIQUE INDEX `COUNTRIES_ID_UNIQUE` (`COUNTRIES_ID` ASC) ,
  INDEX `FK_COUNTRIES_GROUPS` (`GROUPS_ID` ASC) ,
  CONSTRAINT `FK_COUNTRIES_GROUPS`
    FOREIGN KEY (`GROUPS_ID` )
    REFERENCES `MYISAM_TABLES`.`COUNTRIES_GROUPS` (`COUNTRIES_GROUPS_ID` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = MyISAM
DEFAULT CHARACTER SET = utf8
COMMENT = '&#65533;&#65533;&#65533;&#65533;&#65533;&#65533; &#65533;&#65533;&#65533;&#65533;'
*/

?>