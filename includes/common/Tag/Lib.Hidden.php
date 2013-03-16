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
 * class Hidden 
 *
 * Цей клас реалізує операції та відрисовку тегу hidden: <hidden>
 *
 * The class implements the common renderings and operations with the <hidden> tag
 * 
 * @package Library.pkg
 */

 class Hidden extends Tag
 {
 	public $tagName = "input";
 	
 	public $isBlockType = true;
 	
 	public $tagAttributes = array("type" => "hidden");
 	
  	public function __construct() 
 	{
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