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
 * class DivisibilityList 
 *
 * Малюється комбобокс з переліком подільностей
 * 
 * The class implements the combobox with the list of divisibilities
 *
 * @package Library.pkg
 */

class DivisibilityList extends Tag
{
 	public $tagName = "select";
 	
 	public $isBlockType = false;
 	
 	public $tagAttributes = NULL;
 	
 	public $name = "divisibility_name_field";
 	public $id = "divisibility_id_field";
 	
 	public $optionsArray = array( // options to draw
 				array("Title" => "", "Value" => "", "Id" => "", "Selected" => false)
 				);
 	
  	public function __construct() 
 	{
 	} 
 	
 	public function GetDivisibilitiesList($selected)
 	{
 		$divisibility = new Materials();
 		$result = $divisibility->GetDivisibilitiesList($selected);
 		
 		$select = new Select();
		$select->tagAttributes = $this->tagAttributes;
 		$select->tagAttributes['name'] = $this->name;
 		$select->tagAttributes['id'] = $this->id;

 		while($row = mysql_fetch_array($result))
 		{
 			$opt = array("Title" => $row["NAME"], "Value" => $row["DIVISIBILITY_ID"], "Id" => "divisibility_".$row["DIVISIBILITY_ID"], "Selected" => ( ((string)$selected == (string)$row["NAME"] || (float)$selected == (float)$row["DIVISIBILITY_ID"]) ? true : false ));
 			array_push($select->optionsArray, $opt);
 		}
 		
 		return $select->RenderTop().$select->RenderBottom();
 	}
	
}

?>