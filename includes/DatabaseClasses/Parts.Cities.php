<?php

  if (@preg_match("/includes/i", $_SERVER['PHP_SELF'])) 
    die ("You can't access this file directly...");

  include_once("includes/DatabaseClasses/Db.CommonClass.php");    
  include_once("includes/DatabaseClasses/Db.CitiesTbl.php");
  include_once("includes/DatabaseClasses/Db.CountriesTbl.php");

/**
 * The Package collects the cities related classes etc
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
class Cities extends CommonClass
{
	public $country = NULL;
	public $city = NULL;
	
	public $cityName = "";
	public $cityId = "";
	public $countryId = "";
	public $countryCode = "";
	public $countryName = "";
	
	function __construct()
	{
		$this->country = new CountriesTbl();
		$this->city = new CitiesTbl();
	}
	
	/**
	 * Переверіяє чи існує місто
	 */
	public function IsCityExists()
	{
		$isExists = false;
	
		if($this->city->ValidateCityName())
		{
			$this->country->dataRow['COUNTRIES_ID'] = $this->city->dataRow['COUNTRIES_ID'];
			$this->country->LoadCountry();
			
			$isExists = true;
		}
		
		return $isExists;
	}

}

?>