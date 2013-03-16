<?php 

  if (@preg_match("/includes/i", $_SERVER['PHP_SELF'])) 
    die ("You can't access this file directly...");

  include_once("Lib.Tag.php");
//  include_once("/includes/DatabaseClasses/Parts.Countries.php");
  include_once("includes/DatabaseClasses/Parts.Companies.php");

/** 
 *	Library is the package with the common library functionality
 *
 *  @package Library.pkg
 */

/**
 * class Calendar 
 *
 * Малюється контрол з відрисовкою календарю
 * 
 * @package Library.pkg
 */

class Calendar extends Tag
{
 	public $tagName = "";
 	
 	public $isBlockType = false;
 	
 	public $tagAttributes = NULL;
 	
 	public $name = "companies_name_field";
 	
 	public $optionsArray = array( // options to draw
 				array("Title" => "", "Value" => "", "Id" => "", "Selected" => false)
 				);
 	
  	public function __construct() 
 	{
 	} 
	
	public function CheckDayOfTheWeek($date)
	{
		
	}
 	 	
 	public function GetCompaniesFullList($selected)
 	{
 	}
	
}

?>