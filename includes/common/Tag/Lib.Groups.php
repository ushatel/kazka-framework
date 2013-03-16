<?php

if(@preg_match("includes/i", $_SERVER['PHP_SELF']))
	die("You can not access this file directly...");

include_once("Lib.Tag.php");

include_once("/includes/DatabaseClass/Parts.php");

/**
 * Library is the package with the common library functionality
 *
 * @package Library.pkg
 */

/**
 * class Groups
 *
 * ÷ей класс в≥дрисовуЇ дерево груп товар≥в
 *
 * The class implements the common goods groups
 *
 * @package Library.pkg
 *
 */

class Groups extends Tag
{
	public $isBlockType = true;

	public $tagAttributes = array("type" => "button");

	public $id = "";
	public $name = "";
	public $title = "";
	public $value = "";

	public $isAjax = false;

	public function __construct()
	{
	}
	
	public function RenderTree()
	{
		$result = "testing";
		
		return $result;
	}

}

?>