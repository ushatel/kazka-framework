<?php

  if (@preg_match("/includes/i", $_SERVER['PHP_SELF'])) 
    die ("You can't access this file directly...");

  include_once("Lib.Tag.php");
  include_once("includes/DatabaseClasses/Parts.Countries.php");

/** 
 *	Library is the package with the common library functionality
 *
 *  @package Library.pkg
 */

/**
 * class CountriesList 
 *
 * Малюється комбобокс з переліком країн
 * 
 * The class implements the combobox with the list of countries
 *
 * @package Library.pkg
 */

class CountriesList extends Tag
{

 	public $tagName = "select";
 	
 	public $isBlockType = false;
 	
 	public $tagAttributes = NULL;
 	
 	public $name = "country_name_field";
	public $width = "100%";
	public $id = "country_name_field";
 	
 	public $optionsArray = array( // options to draw
 				array("Title" => "", "Value" => "", "Id" => "", "Selected" => false)
 				);
 	
  	public function __construct() 
 	{
 	} 
 	
 	public function GetCountriesList($selected)
	{
		$countries = new Countries();
		$result = $countries->GetCountriesList($selected);

		$select = new Select();
		$select->tagAttributes = $this->tagAttributes;
		$select->tagAttributes['name'] = $this->name;
		$select->tagAttributes['id'] = $this->id;
		$select->tagAttributes['style'] .= "width:".$this->width.";";
		//$select->tagAttributes['onchange'] = "alert(this.children[this.selectedIndex].value + ' _ ' + this.children[this.selectedIndex].id)";

		while($row = mysql_fetch_array($result))
		{			
	 		$opt = array("Title" => $row["Title"], "Value" => $row["COUNTRIES_ID"]."_".$row["Value"], "Id" => "country_".$row["Value"], "Selected" => ((string)$selected == (string)$row["Value"] || (string)$selected == (string)$row['Title'] || (float)$selected == (float)$row['COUNTRIES_ID']) ? true : false);
			array_push($select->optionsArray, $opt);
		}

		return $select->RenderTop().$select->RenderBottom();
	}
	
}

?>