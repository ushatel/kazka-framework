<?php

  if (@preg_match("includes/i", $_SERVER['PHP_SELF'])) 
    die ("You can't access this file directly...");

  include_once("Lib.Tag.php");
  
  
/** 
 *	Library is the package with the common library functionality
 *
 *  @package Library.pkg
 */

/**
 * class Checkbox 
 *
 * Цей клас реалізує операції та відрисовки тегу checkbox: <input type='checkbox'>
 *
 * The class implements the common renderings and operations with the <input type='checkbox'> tag
 * 
 * @package Library.pkg
 */

 class Checkbox extends Tag
 {
 	public $tagName = "input";
 	
 	public $isBlockType = true;
 	
 	public $tagAttributes = array("type" => "checkbox");
 	
  	public function __construct() 
 	{
 	} 	
 	
 	public function SetChecked($val)
 	{
 		if((bool)$val == true)
 		{
 			$this->tagAttributes['checked'] = "checked";
 		} 
 		else
 		{
 			$result = array();
 			
 			foreach($this->tagAttributes as $key => $value)
 			{
 				if($key != 'checked')
 				{
	 				$result[$key] = $value;
	 			}
 			}
 			
 			$this->tagAttributes = $result;
 		}
 	}
 	
 	public function SetValue($val) 
 	{
 		$this->tagAttributes["value"] = $val;
 	}
 	
 	public function SetName($name)
 	{
 		$this->tagAttributes["name"] = $name;
 	}
 }

?>