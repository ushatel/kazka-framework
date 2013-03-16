<?php

  if (@preg_match("includes/i", $_SERVER['PHP_SELF'])) 
    die ("You can't access this file directly...");

/**
 * The Package collects the common user classes
 *
 * @package Library.pkg
 */
 
/** 
 * interface IControl
 *
 * The main interface for Controls. Every Block should implement the interface
 *
 * @package Library.pkg
 */

interface IControl
{

	static function GetAllowedSCodes();
	
}

?>