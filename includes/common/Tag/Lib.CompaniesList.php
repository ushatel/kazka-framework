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
 * class CompaniesList 
 *
 * Малюється комбобокс або text edit со списком компаний
 * 
 * The class implements the combobox with the list of companies
 *
 * @package Library.pkg
 */

class CompaniesList extends Tag
{
 	public $tagName = "select";
 	
 	public $isBlockType = false;
 	
 	public $tagAttributes = NULL;
 	
 	public $name = "companies_name_field";
 	
 	public $optionsArray = array( // options to draw
 				array("Title" => "", "Value" => "", "Id" => "", "Selected" => false)
 				);
 	
  	public function __construct() 
 	{
 	} 
 	 	
 	public function GetCompaniesFullList($selected)
 	{
 		$companies = new Companies();
 		
 		$result = $companies->LoadCompanies();
 		
 		$select = new Select();
 		$select->tagAttributes = $this->tagAttributes;
 		$select->tagAttributes['name'] = $this->name;
 		
 		while($row = mysql_fetch_array($result))
 		{
 			$opt = array("Title" => $row['NAME'], "Value" => $row['COMPANIES_ID'], "Id" => "companies_".$row['COMPANIES_ID'], "Selected" => ( ((string)$selected == (string)$row['NAME'] || (float)$selected == (float)$row["COMPANIES_ID"]) ? true : false) );
 			array_push($select->optionsArray, $opt);
 		}
 		
 		return $select->RenderTop().$select->RenderBottom();
 	}
	
}

?>