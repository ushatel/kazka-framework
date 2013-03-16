<?php

  if (@preg_match("/includes/i", $_SERVER['PHP_SELF'])) 
    die ("You can't access this file directly...");

  include_once("Lib.Tag.php");

/** 
 *	Library is the package with the common library functionality
 *
 *  @package Library.pkg
 */

/**
 * class Select 
 *
 * Малюється комбобокс
 * 
 * The class implements the common renderings and operations with the <hidden> tag
 * 
 * @package Library.pkg
 */
 
class Select extends Tag
{
 	public $tagName = "select";
 	
 	public $isBlockType = false;
 	
 	public $isCombobox = true;
 	
 	public $tagAttributes = NULL;
 	
 	public $optionsArray = array( // options to draw
 				array("Title" => "", "Value" => "", "Id" => "", "Selected" => false)
 				);
 	
  	public function __construct() 
 	{
 	} 
 	
 	public function RenderTop()
 	{
 		if(!$this->isCombobox)
	 		$this->tagAttributes['multiple'] = "multiple";
 		
 		$result = $this->OpenTag();
 		
 		foreach($this->optionsArray as $option)
 		{
 			$result .= $this->RenderOptionTag($option);
 		}
 		
 		return $result;
 	}
 	
 	public function RenderOptionTag($option) 
 	{
 		$result = "";
 		
 		$optiont = new Tag();

		$id = $option["Id"];

 		$optiont->id = $id;
 		$optiont->name = $optiont->tagAttributes["name"] = $optiont->tagAttributes["id"] = $id;
		$optiont->tagAttributes["value"] = $option["Value"];
 		$optiont->tagName = "option";
 		
 		if($option["Selected"] == true)
 		{
 			$optiont->tagAttributes["Selected"] = "selected";
 		}

 		$result .= $optiont->OpenTag();
		$result .= $option["Title"];
 		$result .= $optiont->CloseTag();
 		
 		return $result;
 	}
 	
 	public function RenderBottom()
 	{
 		return $this->CloseTag();
 	}
}
?>